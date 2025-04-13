<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GitHub-based Update Checker for WordPress Plugins
 * 
 * A lightweight implementation that directly communicates with the GitHub API
 * without dependencies on external libraries.
 */
class ESC_GitHub_Updater {
    private $github_username;
    private $github_repo;
    private $plugin_file;
    private $plugin_slug;
    private $plugin_data;
    private $current_version;
    private $transient_key;
    private $cache_time = 43200; // 12 hours in seconds

    /**
     * Initialize the updater
     * 
     * @param string $github_username GitHub username
     * @param string $github_repo GitHub repository name
     * @param string $plugin_file Full path to the main plugin file
     */
    public function __construct($github_username, $github_repo, $plugin_file) {
        $this->github_username = $github_username;
        $this->github_repo = $github_repo;
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->plugin_data = get_plugin_data($plugin_file);
        $this->current_version = $this->plugin_data['Version'];
        $this->transient_key = 'esc_github_update_' . md5($this->plugin_slug);
        
        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 10, 3);
        add_action('upgrader_process_complete', [$this, 'clear_cache'], 10, 0);
        add_action('admin_init', [$this, 'handle_manual_check']);
        
        // Add "Check for Updates" link to plugin row
        add_filter('plugin_action_links_' . $this->plugin_slug, [$this, 'add_check_update_link']);
    }

    /**
     * Check if an update is available
     * 
     * @param object $transient WordPress update transient
     * @return object Modified transient with update info if available
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Get update info from cache or GitHub
        $update_info = $this->get_update_info();
        
        if ($update_info && version_compare($this->current_version, $update_info['version'], '<')) {
            $update = new stdClass();
            $update->slug = dirname($this->plugin_slug);
            $update->plugin = $this->plugin_slug;
            $update->new_version = $update_info['version'];
            $update->url = $update_info['url'];
            $update->package = $update_info['download_url'];
            $update->tested = $update_info['tested'];
            $update->requires_php = $update_info['requires_php'];
            $update->icons = $update_info['icons'];
            
            $transient->response[$this->plugin_slug] = $update;
        }
        
        return $transient;
    }

    /**
     * Get plugin information for the WordPress updates screen
     * 
     * @param false|object|array $result The result object or array
     * @param string $action The API action being performed
     * @param object $args Plugin API arguments
     * @return false|object Plugin information
     */
    public function plugin_info($result, $action, $args) {
        // Only handle requests for our plugin
        if ('plugin_information' !== $action || $args->slug !== dirname($this->plugin_slug)) {
            return $result;
        }
        
        $update_info = $this->get_update_info();
        
        if (!$update_info) {
            return $result;
        }
        
        $info = new stdClass();
        $info->name = $this->plugin_data['Name'];
        $info->slug = dirname($this->plugin_slug);
        $info->version = $update_info['version'];
        $info->author = $this->plugin_data['Author'];
        $info->homepage = $update_info['url'];
        $info->requires = $update_info['requires'];
        $info->tested = $update_info['tested'];
        $info->requires_php = $update_info['requires_php'];
        $info->downloaded = 0;
        $info->last_updated = $update_info['last_updated'];
        $info->sections = [
            'description' => $this->plugin_data['Description'],
            'changelog' => $update_info['changelog'],
        ];
        $info->download_link = $update_info['download_url'];
        
        return $info;
    }

    /**
     * Get update information from GitHub
     * 
     * @param bool $force_check Force a fresh check instead of using cached data
     * @return array|false Update information or false on failure
     */
    private function get_update_info($force_check = false) {
        // Check for cached data
        if (!$force_check) {
            $cached_data = get_transient($this->transient_key);
            if (false !== $cached_data) {
                return $cached_data;
            }
        }
        
        // Try to get the latest release first
        $update_info = $this->get_info_from_release();
        
        // If no release is found, try to get info from the main plugin file
        if (!$update_info) {
            $update_info = $this->get_info_from_file();
        }
        
        // Cache the result if successful
        if ($update_info) {
            set_transient($this->transient_key, $update_info, $this->cache_time);
        }
        
        return $update_info;
    }

    /**
     * Get update information from the latest GitHub release
     * 
     * @return array|false Update information or false on failure
     */
    private function get_info_from_release() {
        $response = wp_remote_get(sprintf(
            'https://api.github.com/repos/%s/%s/releases/latest',
            $this->github_username,
            $this->github_repo
        ));
        
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            return false;
        }
        
        $release_data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (empty($release_data) || !isset($release_data['tag_name'])) {
            return false;
        }
        
        // Format the version (remove 'v' prefix if present)
        $version = ltrim($release_data['tag_name'], 'v');
        
        // Get the changelog from the release body
        $changelog = isset($release_data['body']) ? $release_data['body'] : 'No changelog provided';
        
        return [
            'version' => $version,
            'download_url' => sprintf(
                'https://github.com/%s/%s/archive/%s.zip',
                $this->github_username,
                $this->github_repo,
                $release_data['tag_name']
            ),
            'url' => sprintf('https://github.com/%s/%s', $this->github_username, $this->github_repo),
            'requires' => '6.0',
            'tested' => '6.7.2',
            'requires_php' => '8.2',
            'last_updated' => isset($release_data['published_at']) 
                ? date('Y-m-d', strtotime($release_data['published_at'])) 
                : date('Y-m-d'),
            'changelog' => $this->format_changelog($changelog),
            'icons' => [],
        ];
    }

    /**
     * Get update information from the main plugin file in the repository
     * 
     * @return array|false Update information or false on failure
     */
    private function get_info_from_file() {
        $response = wp_remote_get(sprintf(
            'https://api.github.com/repos/%s/%s/contents/%s?ref=master',
            $this->github_username,
            $this->github_repo,
            basename($this->plugin_file)
        ));
        
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            return false;
        }
        
        $file_data = json_decode(wp_remote_retrieve_body($response), true);
        
        if (empty($file_data) || !isset($file_data['content']) || $file_data['encoding'] !== 'base64') {
            return false;
        }
        
        $file_content = base64_decode($file_data['content']);
        
        // Extract version from the plugin header
        if (!preg_match('/Version:\s*([0-9.]+)/i', $file_content, $matches)) {
            return false;
        }
        
        $version = trim($matches[1]);
        
        return [
            'version' => $version,
            'download_url' => sprintf(
                'https://github.com/%s/%s/archive/master.zip',
                $this->github_username,
                $this->github_repo
            ),
            'url' => sprintf('https://github.com/%s/%s', $this->github_username, $this->github_repo),
            'requires' => '6.0',
            'tested' => '6.7.2',
            'requires_php' => '8.2',
            'last_updated' => date('Y-m-d'),
            'changelog' => 'Update to version ' . $version,
            'icons' => [],
        ];
    }

    /**
     * Format the changelog for display
     * 
     * @param string $changelog Raw changelog text
     * @return string Formatted changelog HTML
     */
    private function format_changelog($changelog) {
        // Convert GitHub markdown to basic HTML
        $changelog = nl2br(esc_html($changelog));
        
        // Make links clickable
        $changelog = preg_replace(
            '/\[(.*?)\]\((.*?)\)/',
            '<a href="$2" target="_blank">$1</a>',
            $changelog
        );
        
        return $changelog;
    }

    /**
     * Clear the update cache
     */
    public function clear_cache() {
        delete_transient($this->transient_key);
    }

    /**
     * Handle manual update checks
     */
    public function handle_manual_check() {
        if (isset($_GET['esc_check_for_updates']) && $_GET['esc_check_for_updates'] == 1) {
            if (check_admin_referer('esc_check_for_updates')) {
                $this->clear_cache();
                $this->get_update_info(true);
                
                // Redirect back to the plugins page
                wp_redirect(admin_url('plugins.php?esc_checked=1'));
                exit;
            }
        }
        
        // Show admin notice after checking for updates
        if (isset($_GET['esc_checked']) && $_GET['esc_checked'] == 1) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-info is-dismissible"><p>' . 
                     esc_html__('Checked for updates. If an update is available, it will appear in the updates list.', 'erins-seed-catalog') . 
                     '</p></div>';
            });
        }
    }
    
    /**
     * Add "Check for Updates" link to plugin actions
     * 
     * @param array $actions Plugin action links
     * @return array Modified action links
     */
    public function add_check_update_link($actions) {
        $check_update_url = wp_nonce_url(
            add_query_arg(
                ['esc_check_for_updates' => 1],
                self_admin_url('plugins.php')
            ),
            'esc_check_for_updates'
        );
        
        $actions['check-for-updates'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url($check_update_url),
            __('Check for Updates', 'erins-seed-catalog')
        );
        
        return $actions;
    }
}

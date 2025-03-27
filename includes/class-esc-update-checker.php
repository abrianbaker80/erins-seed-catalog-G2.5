<?php
/**
 * Update Checker Class
 *
 * @package    Erins_Seed_Catalog
 * @subpackage Includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ESC_Update_Checker
 *
 * Handles checking for plugin updates from GitHub.
 */
class ESC_Update_Checker {
    /**
     * GitHub repository owner.
     *
     * @var string
     */
    private $github_owner = 'abrianbaker80';

    /**
     * GitHub repository name.
     *
     * @var string
     */
    private $github_repo = 'erins-seed-catalog-G2.5';

    /**
     * Transient name for caching update data.
     *
     * @var string
     */
    private $transient_name = 'esc_update_check';

    /**
     * Cache time in seconds (12 hours).
     *
     * @var int
     */
    private $cache_time = 43200;

    /**
     * Initialize the update checker.
     */
    public function init() {
        // Add filters for the update checker
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
        
        // Add action links to the plugins page
        add_filter( 'plugin_action_links_' . plugin_basename( ESC_PLUGIN_FILE ), array( $this, 'add_plugin_links' ) );
        add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2 );
        
        // Handle manual update checks
        add_action( 'admin_init', array( $this, 'handle_manual_check' ) );
    }

    /**
     * Add plugin action links.
     *
     * @param array $links Plugin action links.
     * @return array Modified action links.
     */
    public function add_plugin_links( $links ) {
        // Add check for updates link
        $check_update_url = wp_nonce_url(
            add_query_arg(
                array(
                    'esc_check_for_updates' => 1,
                ),
                self_admin_url( 'plugins.php' )
            ),
            'esc_check_for_updates'
        );
        
        $links['check-for-updates'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url( $check_update_url ),
            __( 'Check for Updates', 'erins-seed-catalog' )
        );
        
        return $links;
    }

    /**
     * Add plugin row meta links.
     *
     * @param array  $plugin_meta Plugin meta links.
     * @param string $plugin_file Plugin file.
     * @return array Modified meta links.
     */
    public function add_plugin_meta_links( $plugin_meta, $plugin_file ) {
        if ( plugin_basename( ESC_PLUGIN_FILE ) !== $plugin_file ) {
            return $plugin_meta;
        }
        
        // Add GitHub repository link
        $plugin_meta[] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url( "https://github.com/{$this->github_owner}/{$this->github_repo}" ),
            __( 'GitHub Repository', 'erins-seed-catalog' )
        );
        
        // Add releases link
        $plugin_meta[] = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url( "https://github.com/{$this->github_owner}/{$this->github_repo}/releases" ),
            __( 'View Releases', 'erins-seed-catalog' )
        );
        
        return $plugin_meta;
    }

    /**
     * Handle manual update check.
     */
    public function handle_manual_check() {
        if ( isset( $_GET['esc_check_for_updates'] ) && check_admin_referer( 'esc_check_for_updates' ) ) {
            // Clear the cached data
            delete_transient( $this->transient_name );
            
            // Redirect back to the plugins page
            wp_redirect( self_admin_url( 'plugins.php' ) );
            exit;
        }
    }

    /**
     * Check for updates.
     *
     * @param object $transient Update transient.
     * @return object Modified update transient.
     */
    public function check_for_updates( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }
        
        // Get the update data
        $update_data = $this->get_update_data();
        
        if ( $update_data && version_compare( ESC_VERSION, $update_data['version'], '<' ) ) {
            $plugin_slug = plugin_basename( ESC_PLUGIN_FILE );
            
            $transient->response[ $plugin_slug ] = (object) array(
                'id'            => 'github.com/' . $this->github_owner . '/' . $this->github_repo,
                'slug'          => 'erins-seed-catalog',
                'plugin'        => $plugin_slug,
                'new_version'   => $update_data['version'],
                'url'           => "https://github.com/{$this->github_owner}/{$this->github_repo}",
                'package'       => $update_data['download_url'],
                'icons'         => array(),
                'banners'       => array(),
                'banners_rtl'   => array(),
                'tested'        => '',
                'requires_php'  => '',
                'compatibility' => new stdClass(),
            );
        }
        
        return $transient;
    }

    /**
     * Get plugin information for the WordPress updates screen.
     *
     * @param false|object|array $result The result object or array.
     * @param string             $action The API action being performed.
     * @param object             $args   Plugin API arguments.
     * @return false|object Plugin information.
     */
    public function plugin_info( $result, $action, $args ) {
        // Check if this is the right plugin
        if ( 'plugin_information' !== $action || 'erins-seed-catalog' !== $args->slug ) {
            return $result;
        }
        
        // Get the update data
        $update_data = $this->get_update_data();
        
        if ( ! $update_data ) {
            return $result;
        }
        
        $plugin_data = get_plugin_data( ESC_PLUGIN_FILE );
        
        $information = (object) array(
            'name'              => $plugin_data['Name'],
            'slug'              => 'erins-seed-catalog',
            'version'           => $update_data['version'],
            'author'            => $plugin_data['Author'],
            'author_profile'    => '',
            'contributors'      => array(),
            'requires'          => '',
            'tested'            => '',
            'requires_php'      => '',
            'compatibility'     => array(),
            'rating'            => 0,
            'num_ratings'       => 0,
            'support_threads'   => 0,
            'support_threads_resolved' => 0,
            'active_installs'   => 0,
            'last_updated'      => $update_data['last_updated'],
            'added'             => '',
            'homepage'          => $plugin_data['PluginURI'],
            'sections'          => array(
                'description'   => $plugin_data['Description'],
                'changelog'     => $update_data['changelog'],
            ),
            'download_link'     => $update_data['download_url'],
            'tags'              => array(),
            'donate_link'       => '',
            'banners'           => array(),
            'banners_rtl'       => array(),
            'icons'             => array(),
        );
        
        return $information;
    }

    /**
     * Get update data from GitHub.
     *
     * @param bool $force_check Force a fresh check instead of using cached data.
     * @return array|false Update data or false on failure.
     */
    private function get_update_data( $force_check = false ) {
        // Check for cached data
        if ( ! $force_check ) {
            $cached_data = get_transient( $this->transient_name );
            if ( false !== $cached_data ) {
                return $cached_data;
            }
        }
        
        // Get the latest release from GitHub
        $response = wp_remote_get( "https://api.github.com/repos/{$this->github_owner}/{$this->github_repo}/releases/latest" );
        
        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return false;
        }
        
        $release_data = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( empty( $release_data ) || ! isset( $release_data['tag_name'] ) ) {
            return false;
        }
        
        // Format the version (remove 'v' prefix if present)
        $version = ltrim( $release_data['tag_name'], 'v' );
        
        // Get the changelog
        $changelog = $this->get_changelog( $release_data );
        
        // Prepare the update data
        $update_data = array(
            'version'      => $version,
            'download_url' => isset( $release_data['zipball_url'] ) ? $release_data['zipball_url'] : '',
            'last_updated' => isset( $release_data['published_at'] ) ? date( 'Y-m-d', strtotime( $release_data['published_at'] ) ) : '',
            'changelog'    => $changelog,
        );
        
        // Cache the data
        set_transient( $this->transient_name, $update_data, $this->cache_time );
        
        return $update_data;
    }

    /**
     * Get the changelog from the release data.
     *
     * @param array $release_data Release data from GitHub.
     * @return string Formatted changelog.
     */
    private function get_changelog( $release_data ) {
        $changelog = '';
        
        if ( isset( $release_data['body'] ) && ! empty( $release_data['body'] ) ) {
            $changelog = $release_data['body'];
        } else {
            $changelog = sprintf(
                __( 'Version %s released on %s', 'erins-seed-catalog' ),
                ltrim( $release_data['tag_name'], 'v' ),
                date( 'F j, Y', strtotime( $release_data['published_at'] ) )
            );
        }
        
        // Format the changelog for WordPress
        $changelog = '<h4>' . sprintf( __( 'Version %s', 'erins-seed-catalog' ), ltrim( $release_data['tag_name'], 'v' ) ) . '</h4>' . "\n" . $changelog;
        
        return $changelog;
    }
}

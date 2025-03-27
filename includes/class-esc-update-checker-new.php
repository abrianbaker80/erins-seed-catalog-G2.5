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
 * Class ESC_Update_Checker_New
 *
 * Handles checking for plugin updates from GitHub using Plugin Update Checker library.
 */
class ESC_Update_Checker_New {
    /**
     * The update checker instance.
     *
     * @var Puc_v4p13_Plugin_UpdateChecker
     */
    private $update_checker;

    /**
     * Initialize the update checker.
     */
    public function init() {
        // Include the library if it's not already included
        if (!class_exists('Puc_v4p13_Plugin_UpdateChecker')) {
            require_once ESC_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';
        }
        
        // Create the update checker
        $this->update_checker = Puc_v4_Factory::buildUpdateChecker(
            'https://github.com/abrianbaker80/erins-seed-catalog-G2.5/',
            ESC_PLUGIN_FILE,
            'erins-seed-catalog'
        );
        
        // Set the branch that contains the stable release
        $this->update_checker->setBranch('master');
        
        // Optional: Set authentication for private repositories
        // $this->update_checker->setAuthentication('your-token-here');
        
        // Set the update checker to use releases instead of tags
        $this->update_checker->getVcsApi()->enableReleaseAssets();
        
        // Add filter to modify the update transient
        add_filter('pre_set_site_transient_update_plugins', array($this, 'modify_update_transient'), 11, 1);
        
        // Add "Check for Updates" link to plugin row
        add_filter('plugin_action_links_' . plugin_basename(ESC_PLUGIN_FILE), array($this, 'add_check_update_link'));
    }
    
    /**
     * Modify the update transient to ensure our plugin is checked correctly.
     *
     * @param object $transient The update transient object.
     * @return object Modified transient.
     */
    public function modify_update_transient($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Log the transient for debugging
        error_log('Update Transient: ' . print_r($transient, true));
        
        return $transient;
    }
    
    /**
     * Add "Check for Updates" link to plugin actions.
     *
     * @param array $links Plugin action links.
     * @return array Modified links.
     */
    public function add_check_update_link($links) {
        $check_update_url = wp_nonce_url(
            add_query_arg(
                array(
                    'puc_check_for_updates' => 1,
                    'puc_slug' => 'erins-seed-catalog',
                ),
                self_admin_url('plugins.php')
            ),
            'puc_check_for_updates'
        );
        
        $links['check-for-updates'] = sprintf(
            '<a href="%s">%s</a>',
            esc_url($check_update_url),
            __('Check for Updates', 'erins-seed-catalog')
        );
        
        return $links;
    }
}

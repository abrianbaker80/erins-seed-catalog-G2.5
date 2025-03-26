<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ESC_Model_Updater
 * Handles checking for and updating available Gemini models.
 */
class ESC_Model_Updater {

    // Transient name for storing model data
    const MODEL_CACHE_TRANSIENT = 'esc_gemini_models_cache';
    
    // How often to check for new models (in seconds)
    const CACHE_EXPIRATION = DAY_IN_SECONDS * 7; // Check weekly
    
    /**
     * Initialize the model updater.
     */
    public static function init() {
        // Add filter to modify the available models
        add_filter('esc_gemini_available_models', [__CLASS__, 'maybe_update_models']);
        
        // Add action to clear the cache when needed
        add_action('esc_clear_model_cache', [__CLASS__, 'clear_cache']);
        
        // Add a button to manually check for new models in the admin
        add_action('admin_init', [__CLASS__, 'register_admin_actions']);
    }
    
    /**
     * Register admin actions for model updating.
     */
    public static function register_admin_actions() {
        // Handle the check for new models action
        if (isset($_GET['esc_action']) && $_GET['esc_action'] === 'check_models' && current_user_can('manage_options')) {
            if (check_admin_referer('esc_check_models_nonce')) {
                self::clear_cache();
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . 
                         esc_html__('Gemini model list has been refreshed.', 'erins-seed-catalog') . 
                         '</p></div>';
                });
            }
        }
    }
    
    /**
     * Clear the model cache.
     */
    public static function clear_cache() {
        delete_transient(self::MODEL_CACHE_TRANSIENT);
    }
    
    /**
     * Check if we need to update the models and do so if needed.
     * 
     * @param array $default_models The default models array.
     * @return array The updated models array.
     */
    public static function maybe_update_models($default_models) {
        // Check if we have cached models
        $cached_models = get_transient(self::MODEL_CACHE_TRANSIENT);
        
        if ($cached_models !== false) {
            // We have cached models, use them
            return $cached_models;
        }
        
        // No cached models, try to fetch the latest models
        $updated_models = self::fetch_latest_models($default_models);
        
        // Cache the models for future use
        set_transient(self::MODEL_CACHE_TRANSIENT, $updated_models, self::CACHE_EXPIRATION);
        
        return $updated_models;
    }
    
    /**
     * Fetch the latest models from Google's documentation or API.
     * 
     * @param array $default_models The default models to fall back to.
     * @return array The updated models array.
     */
    private static function fetch_latest_models($default_models) {
        // In a real implementation, you might fetch this from Google's API
        // For now, we'll just use the default models and add a timestamp
        
        // This is where you would implement the actual API call to get the latest models
        // For example:
        // $response = wp_remote_get('https://api.example.com/gemini-models');
        // if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        //     $body = wp_remote_retrieve_body($response);
        //     $models_data = json_decode($body, true);
        //     // Process the data and return it
        // }
        
        // For demonstration, we'll just add a timestamp to show it was updated
        $updated_models = $default_models;
        $updated_models['_last_updated'] = current_time('mysql');
        
        // You could also add new experimental models here based on the API response
        
        return $updated_models;
    }
    
    /**
     * Get the last update time for the models.
     * 
     * @return string|false The last update time or false if never updated.
     */
    public static function get_last_update_time() {
        $cached_models = get_transient(self::MODEL_CACHE_TRANSIENT);
        if ($cached_models && isset($cached_models['_last_updated'])) {
            return $cached_models['_last_updated'];
        }
        return false;
    }
    
    /**
     * Add a refresh button to the settings page.
     */
    public static function render_refresh_button() {
        $last_update = self::get_last_update_time();
        $refresh_url = wp_nonce_url(
            add_query_arg(
                [
                    'page' => 'erins-seed-catalog',
                    'esc_action' => 'check_models'
                ],
                admin_url('admin.php')
            ),
            'esc_check_models_nonce'
        );
        
        echo '<div class="esc-model-refresh">';
        echo '<a href="' . esc_url($refresh_url) . '" class="button">';
        echo '<span class="dashicons dashicons-update"></span> ';
        echo esc_html__('Check for New Models', 'erins-seed-catalog');
        echo '</a>';
        
        if ($last_update) {
            echo '<p class="description">';
            printf(
                /* translators: %s: date and time of last update */
                esc_html__('Last checked: %s', 'erins-seed-catalog'),
                esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_update)))
            );
            echo '</p>';
        }
        echo '</div>';
    }
}

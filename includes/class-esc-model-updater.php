<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define DAY_IN_SECONDS if not already defined
if ( ! defined( 'DAY_IN_SECONDS' ) ) {
    define( 'DAY_IN_SECONDS', 86400 ); // 60 * 60 * 24
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

        // Register the scheduled event for automatic model updates
        add_action('esc_scheduled_model_check', [__CLASS__, 'scheduled_model_check']);

        // Make sure the scheduled event is registered
        add_action('admin_init', [__CLASS__, 'register_scheduled_event']);

        // Add action to register the scheduled event on plugin activation
        register_activation_hook(ESC_PLUGIN_FILE, [__CLASS__, 'activate_scheduled_event']);

        // Add action to unregister the scheduled event on plugin deactivation
        register_deactivation_hook(ESC_PLUGIN_FILE, [__CLASS__, 'deactivate_scheduled_event']);

        // Add admin notice for new models
        add_action('admin_notices', [__CLASS__, 'show_new_models_notice']);

        // Force a refresh of the model cache on plugin load
        self::clear_cache();
    }

    /**
     * Show admin notice for new models.
     */
    public static function show_new_models_notice() {
        // Only show on our plugin pages
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'erins-seed-catalog') === false) {
            return;
        }

        // Check if we have a notification to show
        $notification = get_transient('esc_new_models_notification');
        if (!$notification || empty($notification['models'])) {
            return;
        }

        // Show the notice
        $new_models = $notification['models'];
        $time = isset($notification['time']) ? $notification['time'] : '';
        $settings_url = admin_url('admin.php?page=erins-seed-catalog');

        echo '<div class="notice notice-info is-dismissible">';
        echo '<h3>' . esc_html__('New Gemini Models Available!', 'erins-seed-catalog') . '</h3>';
        echo '<p>' . sprintf(
            /* translators: %d: number of new models */
            esc_html(_n(
                'The automatic model checker has discovered %d new Gemini model:',
                'The automatic model checker has discovered %d new Gemini models:',
                count($new_models),
                'erins-seed-catalog'
            )),
            count($new_models)
        ) . '</p>';

        echo '<ul class="esc-new-models-list">';
        foreach ($new_models as $model) {
            echo '<li><code>' . esc_html($model) . '</code></li>';
        }
        echo '</ul>';

        echo '<p>';
        echo '<a href="' . esc_url($settings_url) . '" class="button button-primary">';
        echo esc_html__('Go to Settings', 'erins-seed-catalog');
        echo '</a> ';
        echo '<a href="#" class="button esc-dismiss-notice" data-notice="new_models">';
        echo esc_html__('Dismiss', 'erins-seed-catalog');
        echo '</a>';
        echo '</p>';
        echo '</div>';

        // Add some inline CSS
        echo '<style>
            .esc-new-models-list {
                margin-left: 20px;
                list-style-type: disc;
            }
            .esc-new-models-list code {
                background: #f0f0f0;
                padding: 2px 5px;
                border-radius: 3px;
            }
        </style>';

        // Add inline JS to handle dismissal
        echo '<script>
            jQuery(document).ready(function($) {
                $(".esc-dismiss-notice").on("click", function(e) {
                    e.preventDefault();
                    var notice = $(this).data("notice");
                    $.post(ajaxurl, {
                        action: "esc_dismiss_notice",
                        notice: notice,
                        nonce: "' . wp_create_nonce('esc_dismiss_notice') . '"
                    });
                    $(this).closest(".notice").fadeOut();
                });
            });
        </script>';
    }

    /**
     * Register the scheduled event for automatic model updates.
     */
    public static function register_scheduled_event() {
        if (!wp_next_scheduled('esc_scheduled_model_check')) {
            wp_schedule_event(time(), 'weekly', 'esc_scheduled_model_check');
        }
    }

    /**
     * Activate the scheduled event on plugin activation.
     */
    public static function activate_scheduled_event() {
        self::register_scheduled_event();
    }

    /**
     * Deactivate the scheduled event on plugin deactivation.
     */
    public static function deactivate_scheduled_event() {
        $timestamp = wp_next_scheduled('esc_scheduled_model_check');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'esc_scheduled_model_check');
        }
    }

    /**
     * Scheduled task to check for new models.
     */
    public static function scheduled_model_check() {
        // Clear the cache to force a refresh
        self::clear_cache();

        // Trigger a model update
        $default_models = apply_filters('esc_gemini_available_models', []);

        // Log the update
        $cached_models = get_transient(self::MODEL_CACHE_TRANSIENT);
        $update_status = isset($cached_models['_update_status']) ? $cached_models['_update_status'] : '';

        if ($update_status === 'success') {
            // Count new models discovered
            $new_models = [];
            foreach ($cached_models as $model_id => $model_data) {
                if (strpos($model_id, '_') === 0) continue; // Skip metadata
                if (isset($model_data['api_data']) && !empty($model_data['api_data'])) {
                    $new_models[] = $model_id;
                }
            }

            if (!empty($new_models)) {
                error_log(sprintf(
                    'Scheduled Gemini model check: %d new models discovered: %s',
                    count($new_models),
                    implode(', ', $new_models)
                ));

                // Notify admin about new models
                self::notify_admin_about_new_models($new_models);
            } else {
                error_log('Scheduled Gemini model check: No new models discovered');
            }
        } elseif ($update_status === 'error') {
            $error_message = isset($cached_models['_update_error']) ? $cached_models['_update_error'] : '';
            error_log('Scheduled Gemini model check failed: ' . $error_message);
        }
    }

    /**
     * Notify the admin about new models discovered.
     *
     * @param array $new_models Array of new model IDs.
     */
    private static function notify_admin_about_new_models($new_models) {
        if (empty($new_models)) {
            return;
        }

        // Get the admin email
        $admin_email = get_option('admin_email');

        // Set up the email content
        $subject = sprintf(
            /* translators: %d: number of new models */
            _n(
                '[%s] %d New Gemini Model Discovered',
                '[%s] %d New Gemini Models Discovered',
                count($new_models),
                'erins-seed-catalog'
            ),
            get_bloginfo('name'),
            count($new_models)
        );

        $message = sprintf(
            /* translators: %1$s: site name, %2$d: number of models, %3$s: list of models */
            __('Hello,

The automatic model checker for your site %1$s has discovered %2$d new Gemini model(s):

%3$s

You can view and select these models in the Seed Catalog settings.

Regards,
Erin\'s Seed Catalog Plugin', 'erins-seed-catalog'),
            get_bloginfo('name'),
            count($new_models),
            implode("\n", array_map(function($model) { return "- " . $model; }, $new_models))
        );

        // Add a link to the settings page
        $settings_url = admin_url('admin.php?page=erins-seed-catalog');
        $message .= "\n\n" . sprintf(
            __('Settings page: %s', 'erins-seed-catalog'),
            $settings_url
        );

        // Send the email
        wp_mail($admin_email, $subject, $message);

        // Also store this notification in a transient to show in the admin
        set_transient('esc_new_models_notification', [
            'models' => $new_models,
            'time' => current_time('mysql')
        ], DAY_IN_SECONDS * 7); // Keep for a week
    }

    /**
     * Register admin actions for model updating.
     */
    public static function register_admin_actions() {
        // Handle the check for new models action
        if (isset($_GET['esc_action']) && $_GET['esc_action'] === 'check_models' && current_user_can('manage_options')) {
            if (check_admin_referer('esc_check_models_nonce')) {
                // Clear the cache to force a refresh
                self::clear_cache();

                // Trigger an immediate check for new models
                $default_models = apply_filters('esc_gemini_available_models', []);

                // Get the updated models
                $cached_models = get_transient(self::MODEL_CACHE_TRANSIENT);
                $update_status = isset($cached_models['_update_status']) ? $cached_models['_update_status'] : '';

                // Add an admin notice with the result
                add_action('admin_notices', function() use ($update_status, $cached_models) {
                    if ($update_status === 'success') {
                        // Count new models discovered
                        $new_models = [];
                        foreach ($cached_models as $model_id => $model_data) {
                            if (strpos($model_id, '_') === 0) continue; // Skip metadata
                            if (isset($model_data['api_data']) && !empty($model_data['api_data'])) {
                                $new_models[] = $model_id;
                            }
                        }

                        $notice_class = 'notice-success';
                        $message = esc_html__('Gemini model list has been refreshed successfully.', 'erins-seed-catalog');

                        if (!empty($new_models)) {
                            $message .= ' ' . sprintf(
                                /* translators: %d: number of new models */
                                esc_html(_n('%d new model was discovered.', '%d new models were discovered.', count($new_models), 'erins-seed-catalog')),
                                count($new_models)
                            );
                        }
                    } elseif ($update_status === 'error') {
                        $notice_class = 'notice-error';
                        $error_message = isset($cached_models['_update_error']) ? $cached_models['_update_error'] : '';
                        $message = esc_html__('Error refreshing Gemini model list.', 'erins-seed-catalog');
                        if (!empty($error_message)) {
                            $message .= ' ' . esc_html($error_message);
                        }
                    } elseif ($update_status === 'no_api_key') {
                        $notice_class = 'notice-warning';
                        $message = esc_html__('Please add your Gemini API key in the settings to check for new models.', 'erins-seed-catalog');
                    } else {
                        $notice_class = 'notice-info';
                        $message = esc_html__('Gemini model list has been reset to defaults.', 'erins-seed-catalog');
                    }

                    echo '<div class="notice ' . esc_attr($notice_class) . ' is-dismissible"><p>' . $message . '</p></div>';
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
     * Fetch the latest models from Google's AI API.
     *
     * @param array $default_models The default models to fall back to.
     * @return array The updated models array.
     */
    private static function fetch_latest_models($default_models) {
        // Get the API key
        $api_key = get_option(ESC_API_KEY_OPTION);

        // If no API key is set, we can't fetch models
        if (empty($api_key)) {
            // Just add a timestamp to the default models
            $default_models['_last_updated'] = current_time('mysql');
            $default_models['_update_status'] = 'no_api_key';
            return $default_models;
        }

        // Start with the default models
        $updated_models = $default_models;
        $updated_models['_last_updated'] = current_time('mysql');

        // Google AI API endpoint for listing models
        $api_url = add_query_arg('key', $api_key, 'https://generativelanguage.googleapis.com/v1beta/models');

        // Add custom user agent and additional debugging for 403 errors
        $args = [
            'timeout' => 15, // Increase timeout for potentially slow API
            'headers' => [
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; Erins-Seed-Catalog; ' . get_bloginfo('url'),
                'Referer' => site_url(),
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ];
        
        // Log the request attempt for debugging
        error_log('Attempting to fetch Gemini models from: ' . preg_replace('/key=([^&]+)/', 'key=REDACTED', $api_url));

        // Make the API request
        $response = wp_remote_get($api_url, $args);

        // Check if the request was successful
        if (is_wp_error($response)) {
            // Log the error
            error_log('Error fetching Gemini models: ' . $response->get_error_message());
            $updated_models['_update_status'] = 'error';
            $updated_models['_update_error'] = $response->get_error_message();
            return $updated_models;
        }

        // Check the response code
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            // Log the error with more detail
            $body = wp_remote_retrieve_body($response);
            $error_body = json_decode($body, true);
            $error_message = 'API returned status code: ' . $response_code;
            
            if (!empty($error_body) && isset($error_body['error'])) {
                $error_details = $error_body['error'];
                if (isset($error_details['message'])) {
                    $error_message .= ' - ' . $error_details['message'];
                }
                if (isset($error_details['status'])) {
                    $error_message .= ' (Status: ' . $error_details['status'] . ')';
                }
                
                // Check for specific error conditions
                if (strpos($error_details['message'] ?? '', 'API key not valid') !== false) {
                    $error_message = 'Invalid API key. Please verify your Gemini API key.';
                } else if (strpos($error_details['message'] ?? '', 'API key expired') !== false) {
                    $error_message = 'Your Gemini API key has expired. Please generate a new key.';
                } else if ($response_code == 403) {
                    // More detailed 403 error handling
                    $error_message = 'Access denied (403). This could be due to:';
                    $error_message .= "\n- Your API key may have insufficient permissions";
                    $error_message .= "\n- IP restrictions on your API key";
                    $error_message .= "\n- You may need to enable the Gemini API in your Google Cloud project";
                    $error_message .= "\n- Your API key usage quota may have been exceeded";
                    
                    // Check for quota issues in the message
                    if (isset($error_details['message']) && 
                        (strpos($error_details['message'], 'quota') !== false || 
                         strpos($error_details['message'], 'limit') !== false)) {
                        $error_message = 'API quota exceeded. You may have reached your usage limits for the Gemini API.';
                    }
                }
            }
            
            // Log the full response for debugging
            error_log('Error fetching Gemini models: ' . $error_message);
            error_log('Full response headers: ' . print_r(wp_remote_retrieve_headers($response), true));
            error_log('Full response body: ' . substr($body, 0, 500));
            
            $updated_models['_update_status'] = 'error';
            $updated_models['_update_error'] = $error_message;
            return $updated_models;
        }

        // Get the response body
        $body = wp_remote_retrieve_body($response);
        $api_data = json_decode($body, true);

        // Check if we got valid JSON
        if (json_last_error() !== JSON_ERROR_NONE || !isset($api_data['models'])) {
            error_log('Invalid JSON response from Gemini API: ' . substr($body, 0, 100));
            $updated_models['_update_status'] = 'error';
            $updated_models['_update_error'] = 'Invalid API response format';
            return $updated_models;
        }

        // Process the models from the API
        $discovered_models = self::process_api_models($api_data['models'], $default_models);

        // Merge the discovered models with our default models
        // This ensures we keep our custom metadata for known models
        foreach ($discovered_models as $model_id => $model_data) {
            if (isset($updated_models[$model_id])) {
                // Update existing model with API data while preserving our metadata
                $updated_models[$model_id]['available'] = true;
                $updated_models[$model_id]['display_name'] = $model_data['display_name'];
                $updated_models[$model_id]['version'] = $model_data['version'];
                $updated_models[$model_id]['api_data'] = $model_data;
            } else {
                // This is a new model we didn't know about
                $updated_models[$model_id] = [
                    'name' => $model_data['display_name'],
                    'type' => self::determine_model_type($model_id),
                    'description' => __('New model discovered via API', 'erins-seed-catalog'),
                    'recommended' => false,
                    'available' => true,
                    'version' => $model_data['version'],
                    'api_data' => $model_data
                ];
            }
        }

        // Mark models that weren't found in the API as potentially unavailable
        foreach ($updated_models as $model_id => $model_data) {
            // Skip metadata entries (those starting with _)
            if (strpos($model_id, '_') === 0) {
                continue;
            }

            // If this model wasn't in the API response, mark it
            if (!isset($discovered_models[$model_id])) {
                $updated_models[$model_id]['available'] = false;
            }
        }

        $updated_models['_update_status'] = 'success';
        return $updated_models;
    }

    /**
     * Process the models from the API response.
     *
     * @param array $api_models The models from the API response.
     * @param array $default_models Our default models for reference.
     * @return array Processed models.
     */
    private static function process_api_models($api_models, $default_models) {
        $processed_models = [];

        foreach ($api_models as $model) {
            // Skip models that aren't Gemini
            if (!isset($model['name']) || strpos($model['name'], 'gemini') === false) {
                continue;
            }

            // Extract the model ID from the full name (e.g., "models/gemini-pro" -> "gemini-pro")
            $name_parts = explode('/', $model['name']);
            $model_id = end($name_parts);

            // Store the model with its API data
            $processed_models[$model_id] = [
                'display_name' => isset($model['displayName']) ? $model['displayName'] : $model_id,
                'version' => isset($model['version']) ? $model['version'] : 'unknown',
                'supported_generation_methods' => isset($model['supportedGenerationMethods']) ? $model['supportedGenerationMethods'] : [],
                'temperature_range' => isset($model['temperatureRange']) ? $model['temperatureRange'] : null,
                'description' => isset($model['description']) ? $model['description'] : ''
            ];
        }

        return $processed_models;
    }

    /**
     * Determine the type of a model based on its ID.
     *
     * @param string $model_id The model ID.
     * @return string The model type (free, advanced, experimental, legacy).
     */
    private static function determine_model_type($model_id) {
        // Default to experimental for unknown models
        $type = 'experimental';

        // Check for known patterns
        if (strpos($model_id, 'flash-lite') !== false) {
            $type = 'free';
        } elseif (strpos($model_id, 'flash') !== false && strpos($model_id, '1.5') !== false) {
            $type = 'free';
        } elseif (strpos($model_id, 'pro') !== false && strpos($model_id, 'vision') !== false && strpos($model_id, '1.5') !== false) {
            $type = 'advanced';
        } elseif (strpos($model_id, 'pro') !== false && strpos($model_id, '1.5') !== false) {
            $type = 'free';
        } elseif (strpos($model_id, 'pro') !== false && strpos($model_id, 'vision') !== false) {
            $type = 'experimental';
        } elseif (strpos($model_id, 'pro') !== false && strpos($model_id, '1.0') !== false) {
            $type = 'free';
        } elseif (strpos($model_id, 'ultra') !== false) {
            $type = 'experimental';
        } elseif (strpos($model_id, '1.0') !== false) {
            $type = 'legacy';
        }

        return $type;
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
        $cached_models = get_transient(self::MODEL_CACHE_TRANSIENT);
        $last_update = isset($cached_models['_last_updated']) ? $cached_models['_last_updated'] : false;
        $update_status = isset($cached_models['_update_status']) ? $cached_models['_update_status'] : '';
        $update_error = isset($cached_models['_update_error']) ? $cached_models['_update_error'] : '';

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

            // Show status information
            if ($update_status === 'success') {
                echo ' <span class="esc-update-status success"><span class="dashicons dashicons-yes-alt"></span> ' .
                     esc_html__('Successfully updated from API', 'erins-seed-catalog') . '</span>';
            } elseif ($update_status === 'error') {
                echo ' <span class="esc-update-status error"><span class="dashicons dashicons-warning"></span> ' .
                     esc_html__('Error updating from API', 'erins-seed-catalog');
                if (!empty($update_error)) {
                    echo ': ' . esc_html($update_error);
                }
                echo '</span>';
            } elseif ($update_status === 'no_api_key') {
                echo ' <span class="esc-update-status warning"><span class="dashicons dashicons-info"></span> ' .
                     esc_html__('API key required to check for new models', 'erins-seed-catalog') . '</span>';
            }

            echo '</p>';
        }

        // Show model statistics
        if (is_array($cached_models)) {
            $model_count = 0;
            $available_count = 0;
            $new_models = [];

            foreach ($cached_models as $model_id => $model_data) {
                // Skip metadata entries
                if (strpos($model_id, '_') === 0) {
                    continue;
                }

                $model_count++;

                // Count available models
                if (isset($model_data['available']) && $model_data['available']) {
                    $available_count++;
                }

                // Collect new models discovered in the last update
                if (isset($model_data['api_data']) && !empty($model_data['api_data'])) {
                    $new_models[] = $model_id;
                }
            }

            echo '<p class="description">';
            printf(
                /* translators: %1$d: total models, %2$d: available models */
                esc_html__('Models: %1$d total, %2$d available with your API key', 'erins-seed-catalog'),
                $model_count,
                $available_count
            );
            echo '</p>';

            // If we have new models discovered in the last update, show them
            if (!empty($new_models) && count($new_models) <= 5) {
                echo '<p class="description esc-new-models">';
                echo esc_html__('Recently discovered models: ', 'erins-seed-catalog');
                echo '<span class="esc-model-list">' . esc_html(implode(', ', $new_models)) . '</span>';
                echo '</p>';
            }
        }

        echo '</div>';
    }
}

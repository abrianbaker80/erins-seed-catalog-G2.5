<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Make sure WP_Error is available
if ( ! class_exists( 'WP_Error' ) ) {
    require_once ABSPATH . 'wp-includes/class-wp-error.php';
}

/**
 * Class ESC_Ajax
 * Handles AJAX requests for the plugin.
 */
class ESC_Ajax {

	/**
	 * Initialize AJAX hooks.
	 */
	public static function init() {
		// AJAX hook for Gemini search (logged-in users and non-logged-in users)
		add_action( 'wp_ajax_esc_gemini_search', [ __CLASS__, 'handle_gemini_search' ] );
		add_action( 'wp_ajax_nopriv_esc_gemini_search', [ __CLASS__, 'handle_gemini_search' ] ); // Allow if form is truly public

        // AJAX hook for adding a seed
        add_action( 'wp_ajax_esc_add_seed', [ __CLASS__, 'handle_add_seed' ] );
		add_action( 'wp_ajax_nopriv_esc_add_seed', [ __CLASS__, 'handle_add_seed' ] ); // Allow if form is truly public

		// AJAX hook for dismissing notices
		add_action( 'wp_ajax_esc_dismiss_notice', [ __CLASS__, 'handle_dismiss_notice' ] );

		// AJAX hook for getting variety suggestions
		add_action( 'wp_ajax_esc_get_varieties', [ __CLASS__, 'handle_get_varieties' ] );
		add_action( 'wp_ajax_nopriv_esc_get_varieties', [ __CLASS__, 'handle_get_varieties' ] );

		// AJAX hook for testing models
		add_action( 'wp_ajax_esc_test_model', [ __CLASS__, 'handle_test_model' ] );

        // AJAX hook for searching/filtering seeds in the view
        add_action( 'wp_ajax_esc_filter_seeds', [ __CLASS__, 'handle_filter_seeds' ] );
		add_action( 'wp_ajax_nopriv_esc_filter_seeds', [ __CLASS__, 'handle_filter_seeds' ] );

        // AJAX hook for deleting seed (admin only)
        add_action( 'wp_ajax_esc_delete_seed', [ __CLASS__, 'handle_delete_seed' ] );

        // AJAX hook for getting seed details
        add_action( 'wp_ajax_esc_get_seed_details', [ __CLASS__, 'handle_get_seed_details' ] );
		add_action( 'wp_ajax_nopriv_esc_get_seed_details', [ __CLASS__, 'handle_get_seed_details' ] );
	}

	/**
	 * Handle the AJAX request for Gemini API search.
	 */
	public static function handle_gemini_search() {
		// 1. Verify Nonce
		check_ajax_referer( 'esc_ajax_nonce', 'nonce' );

		// 2. Get data from $_POST
		$seed_name = isset( $_POST['seed_name'] ) ? sanitize_text_field( wp_unslash( $_POST['seed_name'] ) ) : '';
		$variety   = isset( $_POST['variety_name'] ) ? sanitize_text_field( wp_unslash( $_POST['variety_name'] ) ) : null;
		$brand     = isset( $_POST['brand_name'] ) ? sanitize_text_field( wp_unslash( $_POST['brand_name'] ) ) : null;
		$sku_upc   = isset( $_POST['sku_upc'] ) ? sanitize_text_field( wp_unslash( $_POST['sku_upc'] ) ) : null;

		// Log the search parameters
		error_log('ESC Gemini Search - Seed Name: ' . $seed_name . ', Variety: ' . $variety);

		if ( empty( $seed_name ) ) {
			wp_send_json_error( [ 'message' => __( 'Seed Name is required for AI search.', 'erins-seed-catalog' ) ] );
			return;
		}

		try {
			// 3. Call the Gemini API Class
			$result = ESC_Gemini_API::fetch_seed_info( $seed_name, $variety, $brand, $sku_upc );

			// Log the API response for debugging
			error_log('Gemini API Response for ' . $seed_name . ': ' . print_r($result, true));

			// 4. Process result and send JSON response
			if ( is_wp_error( $result ) ) {
				wp_send_json_error( [
					'message' => $result->get_error_message(),
					'code' => $result->get_error_code(),
					'data' => $result->get_error_data()
				] );
			} else {
				// Successfully got data from API
				// Add category term_ids based on suggestion
				$suggested_category_names = [];
				if (!empty($result['esc_seed_category_suggestion'])) {
					$suggested_category_names = array_map('trim', explode(',', $result['esc_seed_category_suggestion']));
				}
				$term_ids = [];
				if (!empty($suggested_category_names)) {
					foreach ($suggested_category_names as $name) {
						$term = get_term_by('name', $name, ESC_Taxonomy::TAXONOMY_NAME);
						if ($term && !is_wp_error($term)) {
							$term_ids[] = $term->term_id;
						}
					}
				}
				// Add the term IDs to the response so JS can select them
				$result['suggested_term_ids'] = $term_ids;

				// Make sure we have a valid array to return
				if (!is_array($result)) {
					$result = array(
						'seed_name' => $seed_name,
						'variety_name' => $variety,
						'description' => 'Information could not be properly formatted. Please try again or enter details manually.'
					);
				}

				// Ensure seed_name and variety_name are set in the result
				if (!isset($result['seed_name']) || empty($result['seed_name'])) {
					$result['seed_name'] = $seed_name;
				}

				if (!isset($result['variety_name']) && !empty($variety)) {
					$result['variety_name'] = $variety;
				}

				// Log the final data being sent
				error_log('Sending final data to client: ' . print_r($result, true));

				wp_send_json_success( $result );
			}
		} catch (Exception $e) {
			// Log any exceptions
			error_log('Exception in handle_gemini_search: ' . $e->getMessage());

			// Return a friendly error message
			wp_send_json_error([
				'message' => __('An unexpected error occurred while processing your request.', 'erins-seed-catalog'),
				'error' => $e->getMessage()
			]);
		}

		// Always exit after processing AJAX
		wp_die();
	}


    /**
	 * Handle the AJAX request for adding a new seed.
	 */
    public static function handle_add_seed() {
        // 1. Verify Nonce
        check_ajax_referer('esc_ajax_nonce', 'nonce');

        // Log POST data for debugging (excluding sensitive info)
        $debug_data = $_POST;
        unset($debug_data['nonce']); // Remove sensitive data
        error_log('ESC Add Seed - POST Data: ' . print_r($debug_data, true));

        // 3. Get and Sanitize Data from $_POST
        $allowed_fields = ESC_DB::get_allowed_fields();
        $seed_data = [];
        foreach ($allowed_fields as $field => $type) {
            if (isset($_POST[$field])) {
                $seed_data[$field] = wp_unslash($_POST[$field]);
            }
        }

        // Log processed seed data
        error_log('ESC Add Seed - Processed Data: ' . print_r($seed_data, true));

        // Handle Categories
        $category_term_ids = [];
        if (isset($_POST['esc_seed_category']) && is_array($_POST['esc_seed_category'])) {
            $category_term_ids = array_map('absint', $_POST['esc_seed_category']);
            error_log('ESC Add Seed - Category Term IDs: ' . print_r($category_term_ids, true));
        }

        // Convert term_ids to term_taxonomy_ids for storage
        $category_tt_ids = ESC_Taxonomy::get_term_taxonomy_ids($category_term_ids);
        error_log('ESC Add Seed - Category Taxonomy Term IDs: ' . print_r($category_tt_ids, true));

        // 4. Validate required fields
        if (empty($seed_data['seed_name'])) {
            wp_send_json_error([
                'message' => __('Seed Name is required.', 'erins-seed-catalog'),
                'field' => 'seed_name'
            ]);
            return;
        }

        // If variety is provided, include it in the seed name for display
        if (!empty($seed_data['variety_name'])) {
            // Store the original seed name and variety separately
            $seed_data['original_seed_name'] = $seed_data['seed_name'];
            $seed_data['seed_name'] = sprintf('%s (%s)', $seed_data['seed_name'], $seed_data['variety_name']);
        }

        // 5. Add seed to database
        $result = ESC_DB::add_seed($seed_data, $category_tt_ids);

        // 6. Send JSON response
        if (is_wp_error($result)) {
            error_log('ESC Add Seed - Error: ' . $result->get_error_message());
            $error_data = $result->get_error_data();
            error_log('ESC Add Seed - Error Data: ' . print_r($error_data, true));

            $error_message = $result->get_error_message();
            if (!empty($error_data)) {
                // If it's a database error, include the specific MySQL error
                $error_message .= ' ' . __('Database Error:', 'erins-seed-catalog') . ' ' . $error_data;
            }

            wp_send_json_error([
                'message' => $error_message,
                'code' => $result->get_error_code(),
                'data' => $error_data
            ]);
        } else {
            error_log('ESC Add Seed - Success: Seed ID ' . $result);
            wp_send_json_success([
                'message' => __('Seed added successfully!', 'erins-seed-catalog'),
                'seed_id' => $result
            ]);
        }
        wp_die();
    }

     /**
	 * Handle the AJAX request for filtering/searching the seed catalog view.
	 */
    public static function handle_filter_seeds() {
        // 1. Verify Nonce
        check_ajax_referer('esc_ajax_nonce', 'nonce');

        // 2. Get filter parameters
        $search_term = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $category_id = isset($_POST['category']) ? absint($_POST['category']) : 0;
        $paged = isset($_POST['paged']) ? absint($_POST['paged']) : 1;
        $per_page = 12; // Or get from a setting

         // 3. Prepare arguments for DB query
        $args = [
            'search'   => $search_term,
            'category' => $category_id,
            'limit'    => $per_page,
            'offset'   => ($paged - 1) * $per_page,
            'orderby'  => 'seed_name', // Or allow changing via POST
            'order'    => 'ASC',
        ];

        // 4. Query seeds and count total for pagination
        $seeds = ESC_DB::get_seeds($args);
        $total_seeds = ESC_DB::count_seeds(['search' => $search_term, 'category' => $category_id]);
        $total_pages = ceil($total_seeds / $per_page);

        // 5. Generate HTML for the results
        ob_start();
        if ( ! empty( $seeds ) ) {
             echo '<div class="esc-seed-list">';
             foreach ( $seeds as $seed ) {
                // Use a template part or include the view directly
                // Ensure the view file can handle a single $seed object
                 include( ESC_PLUGIN_DIR . 'public/views/_seed-card.php' ); // Create a reusable card template
             }
             echo '</div>'; // esc-seed-list

             // Pagination
             if ($total_pages > 1) {
                echo '<div class="esc-pagination">';
                echo paginate_links([
                    'base'      => '#%#%', // Use # for AJAX pagination state
                    'format'    => '?paged=%#%',
                    'current'   => $paged,
                    'total'     => $total_pages,
                    'prev_text' => __('&laquo; Previous', 'erins-seed-catalog'),
                    'next_text' => __('Next &raquo;', 'erins-seed-catalog'),
                    'add_args'  => false // Important for AJAX, prevents adding query vars
                ]);
                echo '</div>';
             }

        } else {
            echo '<p class="esc-no-results">' . esc_html__( 'No seeds found matching your criteria.', 'erins-seed-catalog' ) . '</p>';
        }
        $html = ob_get_clean();

        // 6. Send JSON response
        wp_send_json_success([
            'html' => $html,
            'total_found' => $total_seeds,
            'current_page' => $paged,
            'total_pages' => $total_pages
        ]);

        wp_die();
    }


     /**
	 * Handle the AJAX request for deleting a seed (Admin only).
	 */
    public static function handle_delete_seed() {
         // 1. Verify Nonce
        check_ajax_referer('esc_delete_seed_nonce', 'nonce');

        // 2. Check Capabilities
        if ( ! current_user_can( 'manage_options' ) ) { // Use appropriate capability
            wp_send_json_error(['message' => __('Permission denied.', 'erins-seed-catalog')]);
            return;
        }

        // 3. Get Seed ID
        $seed_id = isset($_POST['seed_id']) ? absint($_POST['seed_id']) : 0;

        if ($seed_id <= 0) {
             wp_send_json_error(['message' => __('Invalid Seed ID.', 'erins-seed-catalog')]);
            return;
        }

        // 4. Delete seed from database
        $deleted = ESC_DB::delete_seed($seed_id);

        // 5. Send JSON response
        if ($deleted) {
            wp_send_json_success(['message' => __('Seed deleted successfully.', 'erins-seed-catalog')]);
        } else {
             wp_send_json_error(['message' => __('Error deleting seed.', 'erins-seed-catalog')]);
        }

        wp_die();
    }

    /**
     * Handle the AJAX request for getting seed details
     */
    public static function handle_get_seed_details() {
        check_ajax_referer('esc_ajax_nonce', 'nonce');

        $seed_id = isset($_POST['seed_id']) ? absint($_POST['seed_id']) : 0;
        if (!$seed_id) {
            wp_send_json_error(['message' => __('Invalid seed ID.', 'erins-seed-catalog')]);
            return;
        }

        // Get seed from database
        $seed = ESC_DB::get_seed_by_id($seed_id);
        if (!$seed) {
            wp_send_json_error(['message' => __('Seed not found.', 'erins-seed-catalog')]);
            return;
        }

        // Get the HTML using the detail template
        ob_start();
        include ESC_PLUGIN_DIR . 'public/views/_seed-detail.php';
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html
        ]);

        wp_die();
    }

    /**
     * Handle the AJAX request for dismissing notices.
     */
    public static function handle_dismiss_notice() {
        // Verify nonce
        check_ajax_referer( 'esc_dismiss_notice', 'nonce' );

        // Check if user has permission
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'You do not have permission to dismiss notices.', 'erins-seed-catalog' ) ] );
            return;
        }

        // Get the notice to dismiss
        $notice = isset( $_POST['notice'] ) ? sanitize_key( $_POST['notice'] ) : '';

        // Handle different notices
        if ( $notice === 'new_models' ) {
            // Delete the transient that stores the notification
            delete_transient( 'esc_new_models_notification' );
            wp_send_json_success( [ 'message' => __( 'Notice dismissed.', 'erins-seed-catalog' ) ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'Invalid notice type.', 'erins-seed-catalog' ) ] );
        }
    }

    /**
     * Handle the AJAX request for getting variety suggestions.
     */
    public static function handle_get_varieties() {
        // Verify nonce
        check_ajax_referer( 'esc_ajax_nonce', 'nonce' );

        // Get the seed type
        $seed_type = isset( $_POST['seed_type'] ) ? sanitize_text_field( wp_unslash( $_POST['seed_type'] ) ) : '';

        if ( empty( $seed_type ) ) {
            wp_send_json_error( [ 'message' => __( 'Seed type is required.', 'erins-seed-catalog' ) ] );
            return;
        }

        // Get variety suggestions
        $varieties = ESC_Variety_Suggestions::get_variety_suggestions( $seed_type );

        if ( is_wp_error( $varieties ) ) {
            wp_send_json_error( [
                'message' => $varieties->get_error_message(),
                'seed_type' => $seed_type
            ] );
            return;
        }

        // Send the response
        wp_send_json_success( [
            'varieties' => $varieties,
            'seed_type' => $seed_type
        ] );
    }

    /**
     * Handle the AJAX request for testing a Gemini model.
     */
    public static function handle_test_model() {
        // Verify nonce
        check_ajax_referer( 'esc_test_model_nonce', 'nonce' );

        // Check if user has permission
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => __( 'You do not have permission to test models.', 'erins-seed-catalog' ) ] );
            return;
        }

        // Get the model and API key
        $model = isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( $_POST['model'] ) ) : '';
        $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( wp_unslash( $_POST['api_key'] ) ) : '';

        // Check if model and API key are provided
        if ( empty( $model ) ) {
            wp_send_json_error( [ 'message' => __( 'No model specified.', 'erins-seed-catalog' ) ] );
            return;
        }

        if ( empty( $api_key ) ) {
            wp_send_json_error( [ 'message' => __( 'API key is required.', 'erins-seed-catalog' ) ] );
            return;
        }

        // Start timing for latency measurement
        $start_time = microtime(true);

        // Test the model with a simple prompt
        $test_result = self::test_gemini_model($model, $api_key);

        // Calculate latency
        $latency = round((microtime(true) - $start_time) * 1000); // in milliseconds

        // Check if test was successful
        if ( is_wp_error( $test_result ) ) {
            wp_send_json_error( [
                'message' => $test_result->get_error_message(),
                'code' => $test_result->get_error_code(),
                'data' => $test_result->get_error_data()
            ] );
            return;
        } elseif ( is_array( $test_result ) && isset( $test_result['error'] ) && $test_result['error'] === true ) {
            // Handle the fallback error format
            wp_send_json_error( [
                'message' => $test_result['message'],
                'code' => $test_result['code'],
                'data' => $test_result['data'] ?? null
            ] );
            return;
        }

        // Get model capabilities
        $capabilities = self::get_model_capabilities($model, $api_key);

        // Track usage statistics
        $usage_data = [
            'model' => $model,
            'input_tokens' => isset($test_result['usage']['promptTokenCount']) ? (int)$test_result['usage']['promptTokenCount'] : 0,
            'output_tokens' => isset($test_result['usage']['candidatesTokenCount']) ? (int)$test_result['usage']['candidatesTokenCount'] : 0,
            'latency' => (int)$latency
        ];

        // Calculate total tokens and ensure all values are integers
        $usage_data['total_tokens'] = $usage_data['input_tokens'] + $usage_data['output_tokens'];

        // Update usage statistics in the database
        self::update_model_usage_stats($model, $usage_data);

        // Send success response
        wp_send_json_success( [
            'message' => __( 'Model test successful!', 'erins-seed-catalog' ),
            'model' => $model,
            'response' => $test_result['text'],
            'usage' => $usage_data,
            'capabilities' => $capabilities
        ] );
    }

    /**
     * Test a Gemini model with a simple prompt.
     *
     * @param string $model The model to test.
     * @param string $api_key The API key to use.
     * @return array|WP_Error The test result or an error.
     */
    private static function test_gemini_model($model, $api_key) {
        // Construct the API endpoint URL
        $api_url = add_query_arg(
            'key',
            $api_key,
            'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent'
        );

        // Prepare a simple test prompt
        $request_body = [
            'contents' => [
                [
                    'parts' => [
                        [ 'text' => 'Respond with a short greeting and confirm you are working correctly.' ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 50
            ]
        ];

        // Make the API request
        $response = wp_remote_post(
            $api_url,
            [
                'method' => 'POST',
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode($request_body),
                'timeout' => 15, // Increase timeout for potentially slow API
            ]
        );

        // Check if the request was successful
        if ( is_wp_error( $response ) ) {
            if ( class_exists( 'WP_Error' ) ) {
                return new WP_Error(
                    'api_connection_error',
                    __( 'Error connecting to the Gemini API: ', 'erins-seed-catalog' ) . $response->get_error_message()
                );
            } else {
                // Fallback if WP_Error is not available
                return [
                    'error' => true,
                    'code' => 'api_connection_error',
                    'message' => __( 'Error connecting to the Gemini API: ', 'erins-seed-catalog' ) . $response->get_error_message()
                ];
            }
        }

        // Check the response code
        $response_code = wp_remote_retrieve_response_code( $response );
        if ( $response_code !== 200 ) {
            $body = wp_remote_retrieve_body( $response );
            $result_data = json_decode( $body, true );
            $error_message = isset( $result_data['error']['message'] ) ? $result_data['error']['message'] : $body;

            if ( class_exists( 'WP_Error' ) ) {
                return new WP_Error(
                    'api_error',
                    sprintf( __( 'Gemini API Error (Code: %d): ', 'erins-seed-catalog' ), $response_code ) . $error_message,
                    $result_data
                );
            } else {
                // Fallback if WP_Error is not available
                return [
                    'error' => true,
                    'code' => 'api_error',
                    'message' => sprintf( __( 'Gemini API Error (Code: %d): ', 'erins-seed-catalog' ), $response_code ) . $error_message,
                    'data' => $result_data
                ];
            }
        }

        // Parse the response
        $body = wp_remote_retrieve_body( $response );
        $result_data = json_decode( $body, true );

        // Check if we got a valid response
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            if ( class_exists( 'WP_Error' ) ) {
                return new WP_Error(
                    'api_invalid_response',
                    __( 'Invalid response from the Gemini API.', 'erins-seed-catalog' ),
                    $body
                );
            } else {
                // Fallback if WP_Error is not available
                return [
                    'error' => true,
                    'code' => 'api_invalid_response',
                    'message' => __( 'Invalid response from the Gemini API.', 'erins-seed-catalog' ),
                    'data' => $body
                ];
            }
        }

        // Extract the generated text
        $text = $result_data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if ( empty( $text ) ) {
            if ( class_exists( 'WP_Error' ) ) {
                return new WP_Error(
                    'api_empty_response',
                    __( 'The Gemini API returned an empty response.', 'erins-seed-catalog' ),
                    $result_data
                );
            } else {
                // Fallback if WP_Error is not available
                return [
                    'error' => true,
                    'code' => 'api_empty_response',
                    'message' => __( 'The Gemini API returned an empty response.', 'erins-seed-catalog' ),
                    'data' => $result_data
                ];
            }
        }

        // Return the result
        return [
            'text' => $text,
            'usage' => $result_data['usageMetadata'] ?? []
        ];
    }

    /**
     * Get the capabilities of a Gemini model.
     *
     * @param string $model The model to get capabilities for.
     * @param string $api_key The API key to use.
     * @return array The model capabilities.
     */
    public static function get_model_capabilities($model, $api_key) {
        // Construct the API endpoint URL
        $api_url = add_query_arg(
            'key',
            $api_key,
            'https://generativelanguage.googleapis.com/v1beta/models/' . $model
        );

        // Make the API request
        $response = wp_remote_get(
            $api_url,
            [
                'timeout' => 15, // Increase timeout for potentially slow API
            ]
        );

        // Default capabilities
        $capabilities = [
            'supportedGenerationMethods' => [],
            'temperatureRange' => [
                'min' => 0,
                'max' => 1,
                'default' => 0.7
            ],
            'tokenLimit' => 'Unknown',
            'inputTokenLimit' => 0,
            'outputTokenLimit' => 0,
            'displayName' => $model,
            'description' => '',
            'version' => ''
        ];

        // Check if the request was successful
        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return $capabilities;
        }

        // Parse the response
        $body = wp_remote_retrieve_body( $response );
        $result_data = json_decode( $body, true );

        // Check if we got a valid response
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return $capabilities;
        }

        // Extract capabilities
        if (isset($result_data['supportedGenerationMethods'])) {
            $capabilities['supportedGenerationMethods'] = $result_data['supportedGenerationMethods'];
        }

        if (isset($result_data['temperatureRange'])) {
            $capabilities['temperatureRange'] = $result_data['temperatureRange'];
        }

        if (isset($result_data['inputTokenLimit'])) {
            $capabilities['inputTokenLimit'] = $result_data['inputTokenLimit'];
            $capabilities['tokenLimit'] = $result_data['inputTokenLimit'] . ' input / ' .
                                         ($result_data['outputTokenLimit'] ?? 'Unknown') . ' output';
        }

        if (isset($result_data['outputTokenLimit'])) {
            $capabilities['outputTokenLimit'] = $result_data['outputTokenLimit'];
        }

        // Extract additional model information
        if (isset($result_data['displayName'])) {
            $capabilities['displayName'] = $result_data['displayName'];
        }

        if (isset($result_data['description'])) {
            $capabilities['description'] = $result_data['description'];
        }

        if (isset($result_data['version'])) {
            $capabilities['version'] = $result_data['version'];
        }

        return $capabilities;
    }

    /**
     * Update the usage statistics for a model.
     *
     * @param string $model The model to update statistics for.
     * @param array $usage_data The usage data to update.
     */
    private static function update_model_usage_stats($model, $usage_data) {
        // Get existing usage statistics
        $usage_stats = get_option('esc_model_usage_stats', []);

        // Initialize model stats if not exists
        if (!isset($usage_stats[$model])) {
            $usage_stats[$model] = [
                'total_calls' => 0,
                'total_input_tokens' => 0,
                'total_output_tokens' => 0,
                'total_tokens' => 0,
                'avg_latency' => 0,
                'last_used' => current_time('mysql')
            ];
        }

        // Update statistics
        $usage_stats[$model]['total_calls']++;
        $usage_stats[$model]['total_input_tokens'] += $usage_data['input_tokens'];
        $usage_stats[$model]['total_output_tokens'] += $usage_data['output_tokens'];
        $usage_stats[$model]['total_tokens'] += $usage_data['total_tokens'];

        // Update average latency (avoid division by zero)
        if ($usage_stats[$model]['total_calls'] > 0) {
            $usage_stats[$model]['avg_latency'] = (
                ($usage_stats[$model]['avg_latency'] * ($usage_stats[$model]['total_calls'] - 1)) +
                $usage_data['latency']
            ) / $usage_stats[$model]['total_calls'];
        } else {
            $usage_stats[$model]['avg_latency'] = $usage_data['latency'];
        }

        // Update last used timestamp
        $usage_stats[$model]['last_used'] = current_time('mysql');

        // Save updated statistics
        update_option('esc_model_usage_stats', $usage_stats);
    }

} // End Class
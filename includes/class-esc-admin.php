<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ESC_Admin
 * Handles Admin Menu, Settings Page, and Management Page.
 */
class ESC_Admin {

	/**
	 * Initialize admin hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );
		add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_post_esc_export_seeds', [ __CLASS__, 'handle_export_seeds' ] );

        // Add link to taxonomy page in our menu (optional)
        add_action('admin_menu', [__CLASS__, 'add_taxonomy_to_menu']);

        // Add Settings link on plugin page
        add_filter( 'plugin_action_links_' . plugin_basename( ESC_PLUGIN_FILE ), [ __CLASS__, 'add_plugin_settings_link' ] );
	}

	/**
	 * Add admin menu pages.
	 */
	public static function add_admin_menu() {
		// Add top-level menu page
		add_menu_page(
			__( 'Seed Catalog', 'erins-seed-catalog' ), // Page Title
			__( 'Seed Catalog', 'erins-seed-catalog' ), // Menu Title
			'manage_options',                         // Capability required
			'erins-seed-catalog',                     // Menu Slug
			[ __CLASS__, 'render_settings_page' ],      // Function to display page content
			'dashicons-palmtree',                     // Icon URL or Dashicon class
			76                                        // Position (approx near Tools)
		);

        // Add Settings Submenu Page (as the main page content)
        add_submenu_page(
            'erins-seed-catalog',                     // Parent Slug
            __( 'Settings', 'erins-seed-catalog' ),     // Page Title
            __( 'Settings', 'erins-seed-catalog' ),     // Menu Title
            'manage_options',                         // Capability
            'erins-seed-catalog',                     // Menu Slug (same as parent for default page)
            [ __CLASS__, 'render_settings_page' ]       // Function
        );

		// Add Manage Catalog Submenu Page
		add_submenu_page(
			'erins-seed-catalog',                     // Parent Slug
			__( 'Manage Catalog', 'erins-seed-catalog' ), // Page Title
			__( 'Manage Catalog', 'erins-seed-catalog' ), // Menu Title
			'manage_options',                         // Capability (adjust if needed)
			'esc-manage-catalog',                     // Menu Slug
			[ __CLASS__, 'render_manage_catalog_page' ] // Function
		);

        // Add Test Image URLs Submenu Page
        add_submenu_page(
            'erins-seed-catalog',                     // Parent Slug
            __( 'Test Image URLs', 'erins-seed-catalog' ), // Page Title
            __( 'Test Image URLs', 'erins-seed-catalog' ), // Menu Title
            'manage_options',                         // Capability (adjust if needed)
            'esc-test-image-urls',                    // Menu Slug
            [ __CLASS__, 'render_test_image_urls_page' ] // Function
        );

        // Note: The taxonomy management page is added automatically by WP if show_in_menu=true
	}

    /**
     * Add link to the taxonomy management page under our main menu.
     */
    public static function add_taxonomy_to_menu() {
         global $submenu;
         if (isset($submenu['erins-seed-catalog'])) {
            add_submenu_page(
                'erins-seed-catalog',
                __( 'Seed Categories', 'erins-seed-catalog' ),
                __( 'Categories', 'erins-seed-catalog' ),
                'manage_categories', // Capability to manage terms
                'edit-tags.php?taxonomy=' . ESC_Taxonomy::TAXONOMY_NAME
            );

            // Add Usage Statistics page
            add_submenu_page(
                'erins-seed-catalog',                                // Parent slug
                __( 'API Usage Statistics', 'erins-seed-catalog' ),   // Page title
                __( 'Usage Statistics', 'erins-seed-catalog' ),       // Menu title
                'manage_options',                                    // Capability
                'esc-usage-stats',                                   // Menu slug
                [ __CLASS__, 'render_usage_stats_page' ]             // Callback function
            );

            // UI Test menu item removed
         }
    }


	/**
	 * Register plugin settings using the Settings API.
	 */
	public static function register_settings() {
		// Register the API key setting
		register_setting(
			ESC_SETTINGS_OPTION_GROUP,        // Option group
			ESC_API_KEY_OPTION,               // Option name
			[ __CLASS__, 'sanitize_api_key' ] // Sanitization callback
		);

		// Register the Gemini model setting
		register_setting(
			ESC_SETTINGS_OPTION_GROUP,        // Option group
			ESC_GEMINI_MODEL_OPTION,          // Option name
			[ __CLASS__, 'sanitize_gemini_model' ] // Sanitization callback
		);

		// Add settings section
		add_settings_section(
			'esc_settings_section_api',       // ID
			__( 'API Settings', 'erins-seed-catalog' ), // Title
			[ __CLASS__, 'render_api_section_text' ], // Callback for description
			'erins-seed-catalog'              // Page slug where section appears
		);

		// Add settings field for API Key
		add_settings_field(
			ESC_API_KEY_OPTION,               // ID
			__( 'Gemini API Key', 'erins-seed-catalog' ), // Title
			[ __CLASS__, 'render_api_key_field' ], // Callback to render the field
			'erins-seed-catalog',             // Page slug
			'esc_settings_section_api'      // Section ID
		);

		// Add settings field for Gemini Model
		add_settings_field(
			ESC_GEMINI_MODEL_OPTION,          // ID
			__( 'Gemini Model', 'erins-seed-catalog' ), // Title
			[ __CLASS__, 'render_gemini_model_field' ], // Callback to render the field
			'erins-seed-catalog',             // Page slug
			'esc_settings_section_api'      // Section ID
		);

        // --- Add more sections and fields for other settings as needed ---
        /*
        add_settings_section(
			'esc_settings_section_display',
			__( 'Display Settings', 'erins-seed-catalog' ),
			null, // Optional description callback
			'erins-seed-catalog'
		);
        add_settings_field(
			'esc_items_per_page',
			__( 'Items Per Page (Frontend)', 'erins-seed-catalog' ),
            // ... render callback ...
            'erins-seed-catalog',
            'esc_settings_section_display'
        );
        register_setting( ESC_SETTINGS_OPTION_GROUP, 'esc_items_per_page', 'absint' );
        */
	}

	/**
	 * Sanitize the API key input.
	 *
	 * @param string $input Raw input.
	 * @return string Sanitized input.
	 */
	public static function sanitize_api_key( $input ) {
		// Basic sanitization - remove leading/trailing whitespace
        // More complex validation could be added if the key has a known format
		return sanitize_text_field( trim( $input ) );
	}

	/**
	 * Sanitize the Gemini model input.
	 *
	 * @param string $input Raw input.
	 * @return string Sanitized input.
	 */
	public static function sanitize_gemini_model( $input ) {
		// Get available models to validate against
		$available_models = ESC_Gemini_API::get_available_models();

		// Check if the input is a valid model
		if ( array_key_exists( $input, $available_models ) ) {
			return $input;
		}

		// If not valid, return the default model
		add_settings_error(
			ESC_GEMINI_MODEL_OPTION,
			'invalid_model',
			__( 'Invalid Gemini model selected. Using default model.', 'erins-seed-catalog' )
		);
		return 'gemini-2.0-flash-lite';
	}

	/**
	 * Render descriptive text for the API settings section.
	 */
	public static function render_api_section_text() {
		echo '<p>' . esc_html__( 'Enter your Google Gemini API Key to enable AI-assisted seed information retrieval.', 'erins-seed-catalog' ) . '</p>';
        echo '<p>' . sprintf(
            /* translators: %s: Link to Google AI Studio */
            wp_kses_post( __( 'You can obtain an API key from <a href="%s" target="_blank">Google AI Studio</a>.', 'erins-seed-catalog' ) ),
            'https://aistudio.google.com/app/apikey'
        ) . '</p>';
	}

	/**
	 * Render the input field for the API Key.
	 */
	public static function render_api_key_field() {
		$api_key = get_option( ESC_API_KEY_OPTION );
		printf(
			'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" />',
			esc_attr( ESC_API_KEY_OPTION ),
			esc_attr( $api_key )
		);
        echo '<p class="description">' . esc_html__('Your API key is stored securely in the WordPress database.', 'erins-seed-catalog') . '</p>';
	}

	/**
	 * Render the dropdown field for the Gemini Model.
	 */
	public static function render_gemini_model_field() {
		$current_model = get_option( ESC_GEMINI_MODEL_OPTION, 'gemini-2.0-flash-lite' );
		$available_models = ESC_Gemini_API::get_models_for_dropdown();

		// Add some CSS for the model dropdown
		echo '<style>
			.esc-model-select {
				min-width: 300px;
				max-width: 100%;
				margin-right: 10px;
			}
			.esc-model-select option[disabled] {
				font-weight: bold;
				color: #23282d;
				background-color: #f0f0f0;
				padding: 5px;
			}
		</style>';

		echo '<select id="' . esc_attr( ESC_GEMINI_MODEL_OPTION ) . '" name="' . esc_attr( ESC_GEMINI_MODEL_OPTION ) . '" class="esc-model-select">';

		foreach ( $available_models as $model_id => $model_name ) {
			// Check if this is a header (group separator)
			if ( strpos( $model_id, '_header' ) !== false ) {
				printf(
					'<option value="" disabled>%s</option>',
					esc_html( $model_name )
				);
			} else {
				printf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $model_id ),
					selected( $current_model, $model_id, false ),
					esc_html( $model_name )
				);
			}
		}

		echo '</select>';

		// Add a test button
		echo '<button type="button" id="esc-test-model" class="button button-secondary">';
		echo '<span class="dashicons dashicons-yes"></span> ' . esc_html__('Test Selected Model', 'erins-seed-catalog');
		echo '</button>';

		// Add a container for test results
		echo '<div id="esc-model-test-results" style="display:none;" class="esc-test-results"></div>';

		echo '<p class="description">' . esc_html__('Select which Gemini model to use for AI-assisted information retrieval.', 'erins-seed-catalog') . '</p>';
		echo '<p class="description">' . esc_html__('Flash models are faster and more cost-effective, while Pro models may provide more detailed information.', 'erins-seed-catalog') . '</p>';

		// Add a link to update models documentation
		echo '<p class="description"><a href="https://ai.google.dev/models/gemini" target="_blank">' .
		     esc_html__('Learn more about Gemini models', 'erins-seed-catalog') .
		     ' <span class="dashicons dashicons-external"></span></a></p>';

		// Add containers for model capabilities and usage statistics
		echo '<div id="esc-model-capabilities" style="display:none;" class="esc-model-info-container"></div>';
		echo '<div id="esc-model-usage-stats" style="display:none;" class="esc-model-info-container"></div>';

		// Add the refresh button to check for new models
		ESC_Model_Updater::render_refresh_button();

		// Add some CSS to style the dropdown and status indicators
		echo '<style>
			.esc-model-select option[disabled] {
				font-weight: bold;
				background-color: #f0f0f0;
				color: #23282d;
			}
			.esc-model-refresh {
				margin-top: 10px;
				padding: 10px;
				background: #f9f9f9;
				border: 1px solid #e5e5e5;
				border-radius: 4px;
			}
			.esc-model-refresh .button {
				display: inline-flex;
				align-items: center;
			}
			.esc-model-refresh .dashicons {
				margin-right: 5px;
			}
			.esc-update-status {
				display: inline-block;
				margin-left: 10px;
				padding: 2px 8px;
				border-radius: 3px;
				font-size: 12px;
			}
			.esc-update-status.success {
				background-color: #dff0d8;
				color: #3c763d;
			}
			.esc-update-status.error {
				background-color: #f2dede;
				color: #a94442;
			}
			.esc-update-status.warning {
				background-color: #fcf8e3;
				color: #8a6d3b;
			}
			.esc-new-models {
				margin-top: 5px;
			}
			.esc-model-list {
				font-family: monospace;
				background: #f0f0f0;
				padding: 2px 5px;
				border-radius: 3px;
			}
		</style>';
	}

	/**
	 * Render the Settings page content.
	 */
	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'erins-seed-catalog' ) );
		}

		// Enqueue the model capabilities CSS
		wp_enqueue_style(
			'esc-model-capabilities-styles',
			ESC_PLUGIN_URL . 'admin/css/esc-model-capabilities.css',
			[],
			ESC_VERSION
		);

		include_once ESC_PLUGIN_DIR . 'admin/views/settings-page.php';

		// Include the model capabilities template
		include_once ESC_PLUGIN_DIR . 'admin/views/model-capabilities.php';
	}

	/**
	 * Render the Manage Catalog page content.
     * Placeholder - A WP_List_Table would be ideal here for a robust interface.
     * For simplicity now, we'll show a basic table.
	 */
	public static function render_manage_catalog_page() {
        if ( ! current_user_can( 'manage_options' ) ) { // Adjust capability if needed
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'erins-seed-catalog' ) );
		}

        // Check if an edit action is requested
        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : null;
        $seed_id = isset($_GET['seed_id']) ? absint($_GET['seed_id']) : 0;
        $seed_to_edit = null;

        // Handle Save/Update Action (POST request from edit form)
        if (isset($_POST['esc_submit_edit']) && isset($_POST['esc_edit_seed_nonce'])) {
             if (wp_verify_nonce(sanitize_key($_POST['esc_edit_seed_nonce']), 'esc_edit_seed_action')) {
                $edit_seed_id = isset($_POST['seed_id']) ? absint($_POST['seed_id']) : 0;
                if ($edit_seed_id > 0) {
                    $allowed_fields = ESC_DB::get_allowed_fields();
                    $seed_data = [];

                    // Ensure $allowed_fields is an array before looping
                    if (is_array($allowed_fields)) {
                        foreach ($allowed_fields as $field => $type) {
                            if (isset($_POST[$field])) {
                                $seed_data[$field] = wp_unslash($_POST[$field]);
                            } else if ($type === 'bool') {
                                 // Handle checkboxes that are not sent when unchecked
                                $seed_data[$field] = false;
                            }
                        }
                    } else {
                        // Handle error: allowed fields not retrieved correctly
                        add_action('admin_notices', function() {
                            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Error: Could not retrieve field definitions.', 'erins-seed-catalog') . '</p></div>';
                        });
                        // Optionally prevent further processing
                        // return;
                    }

                     // Handle Categories
                    $category_term_ids = [];
                    if ( isset( $_POST['esc_seed_category'] ) && is_array( $_POST['esc_seed_category'] ) ) {
                        $category_term_ids = array_map( 'absint', $_POST['esc_seed_category'] );
                    }
                    $category_tt_ids = ESC_Taxonomy::get_term_taxonomy_ids($category_term_ids);


                    $result = ESC_DB::update_seed($edit_seed_id, $seed_data, $category_tt_ids);

                    if (is_wp_error($result)) {
                         // Add admin notice for error
                        add_action('admin_notices', function() use ($result) {
                            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Error updating seed:', 'erins-seed-catalog') . ' ' . esc_html($result->get_error_message()) . '</p></div>';
                        });
                    } else {
                        // Add admin notice for success
                        add_action('admin_notices', function() {
                            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Seed updated successfully.', 'erins-seed-catalog') . '</p></div>';
                        });
                        // Redirect to main manage page after successful update to avoid resubmission
                        // wp_redirect(admin_url('admin.php?page=esc-manage-catalog&updated=true'));
                        // exit;
                        // No redirect for now, just show success message above table
                        $action = null; // Go back to list view after update
                    }
                }
            } else {
                 // Nonce verification failed
                  add_action('admin_notices', function() {
                        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Security check failed. Please try again.', 'erins-seed-catalog') . '</p></div>';
                  });
            }
        }
        // Load seed data if edit action is requested (and not just handled by POST)
        elseif ($action === 'edit' && $seed_id > 0) {
            $seed_to_edit = ESC_DB::get_seed_by_id($seed_id);
            if (!$seed_to_edit) {
                // Seed not found, show error and default to list view
                 add_action('admin_notices', function() {
                        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Seed not found for editing.', 'erins-seed-catalog') . '</p></div>';
                 });
                 $action = null; // Reset action
            }
        }


        // Default: Load seed list data for the table
        $seeds = [];
        if ($action !== 'edit') {
             // Simple retrieval of all seeds for now. Add pagination later if needed.
             $seeds = ESC_DB::get_seeds(['limit' => 100, 'orderby' => 'seed_name', 'order' => 'ASC']); // Limit for performance initially
        }


		include_once ESC_PLUGIN_DIR . 'admin/views/manage-catalog-page.php'; // Pass $seeds, $action, $seed_to_edit
	}

    /**
     * Handle the export request.
     */
    public static function handle_export_seeds() {
        // Check nonce and capability
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'esc_export_nonce' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'erins-seed-catalog' ), __( 'Nonce Error', 'erins-seed-catalog' ), [ 'response' => 403 ] );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'erins-seed-catalog' ), __( 'Permissions Error', 'erins-seed-catalog' ), [ 'response' => 403 ] );
        }

        // Get all seed data
        $seeds = ESC_DB::get_seeds( [ 'limit' => -1 ] ); // Get all seeds

        if ( empty( $seeds ) ) {
            wp_die( esc_html__( 'No seeds found to export.', 'erins-seed-catalog' ), __( 'Export Error', 'erins-seed-catalog' ), [ 'response' => 404 ] );
            // Or redirect back with a notice:
            // wp_redirect(add_query_arg('esc_notice', 'no_seeds_to_export', admin_url('admin.php?page=esc-manage-catalog')));
            // exit;
        }

        $filename = 'erins-seed-catalog-export-' . date( 'Y-m-d' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );

        $output = fopen( 'php://output', 'w' );

        // Define headers based on DB columns + categories
        $headers = array_keys( ESC_DB::get_allowed_fields() );
        $headers[] = 'id';
        $headers[] = 'date_added';
        $headers[] = 'last_updated';
        $headers[] = 'categories'; // Add a column for categories

        fputcsv( $output, $headers );

        // Add data rows
        foreach ( $seeds as $seed ) {
            $row = [];
            foreach ( $headers as $header ) {
                 if ($header === 'categories') {
                    // Format categories as comma-separated names
                    $cat_names = [];
                    if (!empty($seed->categories)) {
                        foreach($seed->categories as $term) {
                            $cat_names[] = $term->name;
                        }
                    }
                     $row[] = implode(', ', $cat_names);
                 } elseif (isset($seed->$header)) {
                     $row[] = $seed->$header;
                 } else {
                    $row[] = ''; // Empty string for missing fields
                 }
            }
            fputcsv( $output, $row );
        }

        fclose( $output );
        exit;
    }

    /**
     * Add Settings link to the plugin action links.
     *
     * @param array $links Existing links.
     * @return array Modified links.
     */
    public static function add_plugin_settings_link( $links ) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url( 'admin.php?page=erins-seed-catalog' ), // URL to settings page
            __( 'Settings', 'erins-seed-catalog' )
        );
        array_unshift( $links, $settings_link ); // Add to beginning
        return $links;
    }



    /**
     * Render the usage statistics page.
     */
    public static function render_usage_stats_page() {
        // Enqueue the usage stats CSS
        wp_enqueue_style(
            'esc-usage-stats-styles',
            ESC_PLUGIN_URL . 'admin/css/esc-usage-stats.css',
            [],
            ESC_VERSION
        );

        // Handle reset statistics action
        if (isset($_POST['action']) && $_POST['action'] === 'reset_usage_stats') {
            if (check_admin_referer('esc_reset_usage_stats', 'esc_reset_usage_stats_nonce')) {
                delete_option('esc_model_usage_stats');
                add_settings_error(
                    'esc_usage_stats',
                    'stats_reset',
                    __('Usage statistics have been reset.', 'erins-seed-catalog'),
                    'updated'
                );
            }
        }

        // Include the usage statistics template
        include ESC_PLUGIN_DIR . 'admin/views/usage-stats-page.php';
    }

    /**
     * Render the Test Image URLs page content.
     */
    public static function render_test_image_urls_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'erins-seed-catalog' ) );
        }

        include_once ESC_PLUGIN_DIR . 'admin/views/test-image-urls-page.php';
    }

    /**
     * Handle the AJAX request for testing image URLs.
     */
    public static function handle_test_image_urls() {
        // Verify nonce
        check_ajax_referer( 'esc_ajax_nonce', 'nonce' );

        // Get seeds with images
        global $wpdb;
        $table_name = $wpdb->prefix . 'esc_seeds';

        $seeds = $wpdb->get_results(
            "SELECT id, seed_name, variety_name, image_url FROM {$table_name}
             WHERE image_url IS NOT NULL AND image_url != ''
             ORDER BY id DESC"
        );

        // Format the results
        $formatted_seeds = [];
        foreach ( $seeds as $seed ) {
            $formatted_seeds[] = [
                'id' => $seed->id,
                'name' => $seed->seed_name . ( ! empty( $seed->variety_name ) ? ' - ' . $seed->variety_name : '' ),
                'image_url' => $seed->image_url,
            ];
        }

        // Send the response
        wp_send_json_success( [
            'seeds' => $formatted_seeds,
        ] );
    }

}

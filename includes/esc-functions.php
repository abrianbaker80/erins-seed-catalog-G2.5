<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ESC_Functions
 * Handles general functions like script/style enqueueing.
 */
class ESC_Functions {

    /**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_public_scripts_styles' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_scripts_styles' ] );

		// Add AJAX handlers
		add_action( 'wp_ajax_esc_export_catalog', [ __CLASS__, 'handle_export_catalog_ajax' ] );
		add_action( 'wp_ajax_nopriv_esc_export_catalog', [ __CLASS__, 'handle_export_catalog_ajax' ] );
	}

    /**
	 * Enqueue scripts and styles for the frontend.
	 */
	public static function enqueue_public_scripts_styles() {
        global $post;

        // Only load scripts/styles if a shortcode is present on the page
        if ( is_a( $post, 'WP_Post' ) && (
                has_shortcode( $post->post_content, 'erins_seed_catalog_add_form' ) ||
                has_shortcode( $post->post_content, 'erins_seed_catalog_add_form_modern' ) ||
                has_shortcode( $post->post_content, 'erins_seed_catalog_view' ) ||
                has_shortcode( $post->post_content, 'erins_seed_catalog_enhanced_view' ) ||
                has_shortcode( $post->post_content, 'erins_seed_catalog_search' ) ||
                has_shortcode( $post->post_content, 'erins_seed_catalog_categories' ) ||
                has_shortcode( $post->post_content, 'erins_seed_catalog_export' )
            )) {

            // Enqueue Public CSS (Mobile-First)
            wp_enqueue_style(
                'esc-public-styles',
                ESC_PLUGIN_URL . 'public/css/esc-public-styles.css',
                [],
                ESC_VERSION
            );

            // Enqueue Modern Form CSS with forced cache refresh
            wp_enqueue_style(
                'esc-modern-form',
                ESC_PLUGIN_URL . 'public/css/esc-modern-form.css',
                ['esc-public-styles'],
                ESC_VERSION . '.' . time() // Add timestamp to force cache refresh
            );

            // Enqueue Scroll Enhancements CSS
            wp_enqueue_style(
                'esc-scroll-enhancements',
                ESC_PLUGIN_URL . 'public/css/esc-scroll-enhancements.css',
                ['esc-public-styles', 'esc-modern-form'],
                ESC_VERSION
            );

            // Enqueue Seed Variety Layout CSS
            wp_enqueue_style(
                'esc-seed-variety-layout',
                ESC_PLUGIN_URL . 'public/css/esc-seed-variety-layout.css',
                ['esc-public-styles', 'esc-modern-form'],
                ESC_VERSION
            );

            // Enqueue AI Results Fixes CSS
            wp_enqueue_style(
                'esc-ai-results-fixes',
                ESC_PLUGIN_URL . 'public/css/esc-ai-results-fixes.css',
                ['esc-public-styles', 'esc-modern-form', 'esc-seed-variety-layout'],
                ESC_VERSION . '.' . time() // Add timestamp to force cache refresh
            );

            // Enqueue Initial Form Fixes CSS
            wp_enqueue_style(
                'esc-initial-form-fixes',
                ESC_PLUGIN_URL . 'public/css/esc-initial-form-fixes.css',
                ['esc-public-styles', 'esc-modern-form', 'esc-seed-variety-layout', 'esc-ai-results-fixes'],
                ESC_VERSION . '.' . time() // Add timestamp to force cache refresh
            );

            // Enqueue Input Alignment CSS
            wp_enqueue_style(
                'esc-input-alignment',
                ESC_PLUGIN_URL . 'public/css/esc-input-alignment.css',
                ['esc-public-styles', 'esc-modern-form', 'esc-seed-variety-layout', 'esc-ai-results-fixes', 'esc-initial-form-fixes'],
                ESC_VERSION . '.' . time() // Add timestamp to force cache refresh
            );

            // Enqueue Input Fixes Important CSS (with highest priority)
            wp_enqueue_style(
                'esc-input-fixes-important',
                ESC_PLUGIN_URL . 'public/css/esc-input-fixes-important.css',
                ['esc-public-styles', 'esc-modern-form', 'esc-seed-variety-layout', 'esc-ai-results-fixes', 'esc-initial-form-fixes', 'esc-input-alignment'],
                ESC_VERSION . '.' . time() // Add timestamp to force cache refresh
            );

            // Enqueue Remove Green Lines CSS (with highest priority)
            wp_enqueue_style(
                'esc-remove-green-lines',
                ESC_PLUGIN_URL . 'public/css/esc-remove-green-lines.css',
                ['esc-public-styles', 'esc-modern-form', 'esc-seed-variety-layout', 'esc-ai-results-fixes', 'esc-initial-form-fixes', 'esc-input-alignment', 'esc-input-fixes-important'],
                ESC_VERSION . '.' . time() // Add timestamp to force cache refresh
            );

            // Enqueue Public JS
            wp_enqueue_script(
                'esc-public-scripts',
                ESC_PLUGIN_URL . 'public/js/esc-public-scripts.js',
                [ 'jquery' ], // Dependency on jQuery
                ESC_VERSION,
                true // Load in footer
            );

            // Enqueue Variety Suggestions JS
            wp_enqueue_script(
                'esc-variety-suggestions',
                ESC_PLUGIN_URL . 'public/js/esc-variety-suggestions.js',
                [ 'jquery', 'esc-public-scripts' ],
                ESC_VERSION,
                true // Load in footer
            );

            // Enqueue Modern Form JS
            wp_enqueue_script(
                'esc-modern-form',
                ESC_PLUGIN_URL . 'public/js/esc-modern-form.js',
                [ 'jquery', 'esc-public-scripts', 'esc-variety-suggestions' ],
                ESC_VERSION,
                true // Load in footer
            );

            // Enqueue Scroll Enhancements JS
            wp_enqueue_script(
                'esc-scroll-enhancements',
                ESC_PLUGIN_URL . 'public/js/esc-scroll-enhancements.js',
                [ 'jquery', 'esc-modern-form' ],
                ESC_VERSION,
                true // Load in footer
            );

            // Enqueue Debug JS
            wp_enqueue_script(
                'esc-debug',
                ESC_PLUGIN_URL . 'public/js/esc-debug.js',
                [ 'jquery' ],
                ESC_VERSION . '.' . time(), // Force cache refresh
                true // Load in footer
            );

            // Enqueue AI Results Fixes JS
            wp_enqueue_script(
                'esc-ai-results-fixes',
                ESC_PLUGIN_URL . 'public/js/esc-ai-results-fixes.js',
                [ 'jquery', 'esc-public-scripts', 'esc-modern-form' ],
                ESC_VERSION . '.' . time(), // Force cache refresh
                true // Load in footer
            );

            // Enqueue Input Alignment JS
            wp_enqueue_script(
                'esc-input-alignment',
                ESC_PLUGIN_URL . 'public/js/esc-input-alignment.js',
                [ 'jquery', 'esc-public-scripts', 'esc-modern-form', 'esc-ai-results-fixes' ],
                ESC_VERSION . '.' . time(), // Force cache refresh
                true // Load in footer
            );

            // Enqueue Direct Input Fixes JS
            wp_enqueue_script(
                'esc-direct-input-fixes',
                ESC_PLUGIN_URL . 'public/js/esc-direct-input-fixes.js',
                [ 'jquery', 'esc-public-scripts', 'esc-modern-form', 'esc-ai-results-fixes', 'esc-input-alignment' ],
                ESC_VERSION . '.' . time(), // Force cache refresh
                true // Load in footer
            );

            // Enqueue Remove Green Lines JS
            wp_enqueue_script(
                'esc-remove-green-lines',
                ESC_PLUGIN_URL . 'public/js/esc-remove-green-lines.js',
                [ 'jquery', 'esc-public-scripts', 'esc-modern-form', 'esc-ai-results-fixes', 'esc-input-alignment', 'esc-direct-input-fixes' ],
                ESC_VERSION . '.' . time(), // Force cache refresh
                true // Load in footer
            );

            // Localize script for AJAX calls
            wp_localize_script(
                'esc-public-scripts',
                'esc_ajax_object',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'esc_ajax_nonce' ), // Create a nonce
                    'loading_text' => __('Loading...', 'erins-seed-catalog'),
                    'error_text' => __('An error occurred.', 'erins-seed-catalog'),
                    'gemini_error_text' => __('Error fetching AI info:', 'erins-seed-catalog'),
                    'form_submit_success' => __('Seed added successfully!', 'erins-seed-catalog'),
                    'form_submit_error' => __('Error adding seed.', 'erins-seed-catalog'),
                ]
            );
        }
	}

	/**
	 * Enqueue scripts and styles for the admin area.
	 */
	public static function enqueue_admin_scripts_styles( $hook_suffix ) {
        // Only load on our specific admin pages
        // Find the hook suffix for your pages (e.g., using error_log($hook_suffix); )
        // It will likely be 'toplevel_page_erins-seed-catalog' and 'seed-catalog_page_esc-manage-catalog' or similar
         $plugin_pages = [
             'toplevel_page_erins-seed-catalog', // Settings page (adjust if it's a submenu)
             'seed-catalog_page_esc-manage-catalog', // Manage page (adjust based on actual slug)
             'edit-tags.php', // For the category management screen
         ];

        // Also load on the taxonomy edit screen
         if ( get_current_screen()->taxonomy === ESC_Taxonomy::TAXONOMY_NAME && $hook_suffix === 'edit-tags.php') {
             // Potentially load specific styles/scripts for taxonomy admin if needed
         }


        if ( in_array( $hook_suffix, $plugin_pages ) ) {
            // Enqueue Admin CSS
            wp_enqueue_style(
                'esc-admin-styles',
                ESC_PLUGIN_URL . 'admin/css/esc-admin-styles.css',
                [],
                ESC_VERSION
            );

            // Enqueue Model Test CSS
            wp_enqueue_style(
                'esc-model-test-styles',
                ESC_PLUGIN_URL . 'admin/css/esc-model-test.css',
                [],
                ESC_VERSION
            );

            // Enqueue Update Checker CSS
            wp_enqueue_style(
                'esc-update-checker-styles',
                ESC_PLUGIN_URL . 'admin/css/esc-update-checker.css',
                [],
                ESC_VERSION
            );

            // Enqueue Admin JS (if needed for interactions)
            wp_enqueue_script(
                'esc-admin-scripts',
                ESC_PLUGIN_URL . 'admin/js/esc-admin-scripts.js',
                [ 'jquery' ],
                ESC_VERSION,
                true
            );

            // Enqueue Model Test JS
            wp_enqueue_script(
                'esc-model-test-scripts',
                ESC_PLUGIN_URL . 'admin/js/esc-model-test.js',
                [ 'jquery' ],
                ESC_VERSION,
                true
            );

            // Localize admin script if needed for AJAX, nonces etc.
             wp_localize_script(
                'esc-admin-scripts',
                'esc_admin_ajax_object',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'delete_nonce' => wp_create_nonce( 'esc_delete_seed_nonce' ),
                    'confirm_delete' => __( 'Are you sure you want to delete this seed? This cannot be undone.', 'erins-seed-catalog' ),
                ]
             );

            // Localize model test script
            wp_localize_script(
                'esc-model-test-scripts',
                'esc_model_test',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'esc_test_model_nonce' ),
                    'model_option_name' => ESC_GEMINI_MODEL_OPTION,
                    'api_key_option_name' => ESC_API_KEY_OPTION,
                    'loading_text' => __('Testing model connection...', 'erins-seed-catalog'),
                    'loading_header' => __('Testing Model', 'erins-seed-catalog'),
                    'success_text' => __('Model connection successful!', 'erins-seed-catalog'),
                    'success_header' => __('Test Successful', 'erins-seed-catalog'),
                    'error_text' => __('Model connection failed.', 'erins-seed-catalog'),
                    'error_header' => __('Test Failed', 'erins-seed-catalog'),
                    'error_no_api_key' => __('Please enter your API key first.', 'erins-seed-catalog'),
                    'usage_header' => __('Usage Statistics', 'erins-seed-catalog'),
                    'usage_model' => __('Model', 'erins-seed-catalog'),
                    'usage_tokens_in' => __('Input Tokens', 'erins-seed-catalog'),
                    'usage_tokens_out' => __('Output Tokens', 'erins-seed-catalog'),
                    'usage_total_tokens' => __('Total Tokens', 'erins-seed-catalog'),
                    'usage_latency' => __('Response Time', 'erins-seed-catalog'),
                    'capabilities_header' => __('Model Capabilities', 'erins-seed-catalog'),
                    'capabilities_methods' => __('Supported Generation Methods', 'erins-seed-catalog'),
                    'capabilities_temperature' => __('Temperature Range', 'erins-seed-catalog'),
                    'capabilities_min' => __('Minimum', 'erins-seed-catalog'),
                    'capabilities_max' => __('Maximum', 'erins-seed-catalog'),
                    'capabilities_token_limit' => __('Token Limit', 'erins-seed-catalog'),
                ]
            );
        }
	}

    /**
     * Helper function to display formatted seed information.
     * Used in frontend views. Escapes output.
     *
     * @param object $seed The seed data object.
     * @param string $field_name The name of the field to display.
     * @param string $label The label for the field.
     * @param string $type Hint for display formatting (e.g., 'text', 'url', 'bool', 'image').
     */
    public static function display_seed_field( ?object $seed, string $field_name, string $label, string $type = 'string' ) : void {
        if ( ! $seed || ! isset( $seed->$field_name ) ) return;

        $value = $seed->$field_name;

        // Skip display if value is empty, null, or explicitly false for booleans
        if ( $value === null || $value === '' || ($type === 'bool' && $value == false) ) {
            return;
        }

        echo '<div class="esc-field esc-field-' . esc_attr( $field_name ) . '">';
        echo '  <strong class="esc-field-label">' . esc_html( $label ) . ':</strong> ';
        echo '  <span class="esc-field-value">';

        switch ( $type ) {
            case 'image':
                if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
                    echo '<img src="' . esc_url( $value ) . '" alt="' . esc_attr( $seed->seed_name ?? '' ) . ' ' . esc_attr( $seed->variety_name ?? '' ) . '" loading="lazy">';
                } else {
                     echo esc_html( __('Invalid image URL', 'erins-seed-catalog') );
                }
                break;
            case 'url':
                 if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
                     echo '<a href="' . esc_url( $value ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $value ) . '</a>';
                 } else {
                     echo esc_html( $value ); // Display as text if not a valid URL
                 }
                break;
            case 'text':
                echo nl2br( esc_html( $value ) ); // Convert newlines to <br> and escape
                break;
            case 'bool':
                echo esc_html( __( 'Yes', 'erins-seed-catalog' ) ); // Value is guaranteed to be true here due to the check above
                break;
             case 'date':
                 // Attempt to format date based on WP settings
                 $timestamp = strtotime($value);
                 if ($timestamp) {
                    echo esc_html( date_i18n( get_option('date_format'), $timestamp ) );
                 } else {
                    echo esc_html( $value ); // Show raw value if parsing failed
                 }
                 break;
            case 'category':
                if (isset($seed->categories) && !empty($seed->categories)) {
                    $cat_links = [];
                    foreach ($seed->categories as $term) {
                        $link = get_term_link($term, ESC_Taxonomy::TAXONOMY_NAME);
                        if (!is_wp_error($link)) {
                            $cat_links[] = '<a href="' . esc_url($link) . '">' . esc_html($term->name) . '</a>';
                        } else {
                             $cat_links[] = esc_html($term->name);
                        }
                    }
                    echo implode(', ', $cat_links);
                }
                break;
            case 'string':
            default:
                echo esc_html( $value );
                break;
        }

        echo '  </span>';
        echo '</div>';
    }

    /**
     * Handle the AJAX export catalog request.
     */
    public static function handle_export_catalog_ajax() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'esc_export_catalog_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'erins-seed-catalog')]);
        }

        // Get selected columns
        $columns = isset($_POST['export_columns']) && is_array($_POST['export_columns'])
            ? array_map('sanitize_text_field', $_POST['export_columns'])
            : [];

        if (empty($columns)) {
            wp_send_json_error(['message' => __('No columns selected for export.', 'erins-seed-catalog')]);
        }

        // Get category filter if set
        $category_id = isset($_POST['category_filter']) && !empty($_POST['category_filter'])
            ? intval($_POST['category_filter'])
            : 0;

        // Get export format
        $format = isset($_POST['export_format']) ? sanitize_text_field($_POST['export_format']) : 'csv';

        // Get seeds based on filters
        $args = ['limit' => -1]; // Get all seeds

        if ($category_id > 0) {
            $args['category'] = $category_id;
        }

        $seeds = ESC_DB::get_seeds($args);

        if (empty($seeds)) {
            wp_send_json_error(['message' => __('No seeds found to export.', 'erins-seed-catalog')]);
        }

        // Generate filename
        $filename = 'erins-seed-catalog-export-' . date('Y-m-d') . '.csv';

        // Set headers for download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel compatibility
        fputs($output, "\xEF\xBB\xBF");

        // Write header row with selected columns
        $header_row = [];
        foreach ($columns as $column) {
            // Convert snake_case to Title Case for display
            $header_row[] = ucwords(str_replace('_', ' ', $column));
        }
        fputcsv($output, $header_row);

        // Write data rows
        foreach ($seeds as $seed) {
            $row = [];
            foreach ($columns as $column) {
                if ($column === 'categories') {
                    // Format categories as comma-separated names
                    $cat_names = [];
                    if (!empty($seed->categories)) {
                        foreach ($seed->categories as $term) {
                            $cat_names[] = $term->name;
                        }
                    }
                    $row[] = implode(', ', $cat_names);
                } elseif (isset($seed->$column)) {
                    $row[] = $seed->$column;
                } else {
                    $row[] = ''; // Empty string for missing fields
                }
            }
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

}
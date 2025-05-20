<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ESC_Shortcodes
 * Registers and handles the plugin's shortcodes.
 */
class ESC_Shortcodes {

	/**
	 * Get file version for cache busting.
	 * 
	 * This helper method uses filemtime() for more efficient cache busting
	 * than using time() which creates a new version on every page load.
	 * 
	 * @param string $file_path The path to the file.
	 * @return string The version string.
	 */
	private static function get_file_version($file_path) {
		return ESC_VERSION . '.' . (file_exists($file_path) ? filemtime($file_path) : '1');
	}
	/**
	 * Initialize shortcodes.
	 */
	public static function init() {
		add_shortcode( 'erins_seed_catalog_add_form', [ __CLASS__, 'render_add_form' ] );
		add_shortcode( 'erins_seed_catalog_view', [ __CLASS__, 'render_catalog_view' ] );
		add_shortcode( 'erins_seed_catalog_search', [ __CLASS__, 'render_search_form' ] );
		add_shortcode( 'erins_seed_catalog_categories', [ __CLASS__, 'render_category_list' ] );
		add_shortcode( 'erins_seed_catalog_export', [ __CLASS__, 'render_export_form' ] );
		add_shortcode( 'erins_seed_catalog_enhanced_view', [ __CLASS__, 'render_enhanced_catalog_view' ] );
		add_shortcode( 'erins_seed_catalog_improved_view', [ __CLASS__, 'render_improved_catalog_view' ] );

		// Add a test shortcode to verify modern form is working
		add_shortcode( 'erins_seed_catalog_add_form_modern', [ __CLASS__, 'render_add_form_modern' ] );

		// Add a test shortcode for the enhanced AI results page
		add_shortcode( 'erins_seed_catalog_test_ai_results', [ __CLASS__, 'render_test_ai_results' ] );
		// Add a shortcode for the refactored form
		add_shortcode( 'erins_seed_catalog_add_form_refactored', [ __CLASS__, 'render_add_form_refactored' ] );

		// Add a test shortcode for integration testing
		add_shortcode( 'erins_seed_catalog_test_integration', [ __CLASS__, 'render_test_integration' ] );
		
		// Add a debug shortcode to help troubleshoot issues
		add_shortcode( 'erins_seed_catalog_debug', [ __CLASS__, 'render_debug_info' ] );

		// Add a test shortcode for the enhanced view
		add_shortcode( 'erins_seed_catalog_test_enhanced_view', [ __CLASS__, 'render_test_enhanced_view' ] );

		// Add a fixed shortcode for the enhanced view
		add_shortcode( 'esc_fixed_enhanced_view', [ __CLASS__, 'render_fixed_enhanced_view' ] );

		// Add a test shortcode for the refactored form
		add_shortcode( 'erins_seed_catalog_test_refactored', [ __CLASS__, 'render_test_refactored' ] );

		// Add a shortcode for debugging
		add_shortcode( 'erins_seed_catalog_debug', [ __CLASS__, 'render_debug_info' ] );
	}

	/**
	 * Render the [erins_seed_catalog_add_form] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the form.
	 */
	public static function render_add_form( $atts = [] ) {
		// Attributes might be used later for customization (e.g., redirect URL)
		// $atts = shortcode_atts( array(
		// 	'redirect' => '',
		// ), $atts, 'erins_seed_catalog_add_form' );

		// Use the refactored form as the default
		return self::render_add_form_refactored($atts);
	}

	/**
	 * Render the [erins_seed_catalog_view] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the catalog view.
	 */
	public static function render_catalog_view( $atts = [] ) {
         // Attributes could define initial state e.g. default category, items per page
        $atts = shortcode_atts( [
			'per_page' => 12,
            'category' => '', // Allow filtering by category slug/ID initially
		], $atts, 'erins_seed_catalog_view' );

        // Enqueue necessary styles for the catalog view
        wp_enqueue_style(
            'esc-public-styles',
            ESC_PLUGIN_URL . 'public/css/esc-public-styles.css',
            [],
            ESC_VERSION
        );

        $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
        $per_page = absint($atts['per_page']);
        $initial_category_id = 0;

        if (!empty($atts['category'])) {
            $term = get_term_by('slug', $atts['category'], ESC_Taxonomy::TAXONOMY_NAME) ?: get_term_by('id', $atts['category'], ESC_Taxonomy::TAXONOMY_NAME);
            if ($term && !is_wp_error($term)) {
                $initial_category_id = $term->term_id;
            }
        }

        // Initial query args
         $args = [
            'limit'    => $per_page,
            'offset'   => ($paged - 1) * $per_page,
            'category' => $initial_category_id, // Pass term_id
        ];

		$seeds = ESC_DB::get_seeds($args);
        $total_seeds = ESC_DB::count_seeds(['category' => $initial_category_id]); // Count matching initial filter
        $total_pages = ceil($total_seeds / $per_page);

		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/seed-catalog-display.php'; // Pass $seeds, $paged, $total_pages, $initial_category_id to the view
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_search] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the search form.
	 */
	public static function render_search_form( $atts = [] ) {
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/seed-search-form.php';
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_categories] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the category list.
	 */
	public static function render_category_list( $atts = [] ) {
        $atts = shortcode_atts( [
            'show_count' => false, // Show number of seeds per category? (Requires extra queries)
            'hierarchical' => true,
            'orderby' => 'name',
            'order' => 'ASC',
            'title_li' => '', // No default list item title
		], $atts, 'erins_seed_catalog_categories' );

		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/seed-category-list.php'; // Pass $atts to the view
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_add_form_modern] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the modern form.
	 */
	public static function render_add_form_modern( $atts = [] ) {
		// This is a dedicated shortcode that explicitly uses the modern form
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/add-seed-form-modern.php';
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_export] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the export form.
	 */
	public static function render_export_form( $atts = [] ) {
		// Enqueue export-specific scripts and styles
		wp_enqueue_script(
			'esc-export-scripts',
			ESC_PLUGIN_URL . 'public/js/esc-export.js',
			['jquery'],
			ESC_VERSION,
			true
		);

		wp_enqueue_style(
			'esc-export-styles',
			ESC_PLUGIN_URL . 'public/css/esc-export.css',
			['esc-public-styles'],
			ESC_VERSION
		);

		// Localize script with export data
		wp_localize_script(
			'esc-export-scripts',
			'esc_export_object',
			[
				'export_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('esc_export_catalog_nonce'),
				'loading_text' => __('Preparing export...', 'erins-seed-catalog'),
				'success_text' => __('Export started! Your download should begin shortly.', 'erins-seed-catalog'),
				'error_no_columns' => __('Please select at least one column to export.', 'erins-seed-catalog'),
			]
		);

		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/seed-export-form.php';
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_enhanced_view] shortcode.
	 *
	 * @param array $atts Shortcode attributes.	 * @return string HTML output for the enhanced catalog view.
	 */	public static function render_enhanced_catalog_view( $atts = [] ) {
		// Attributes could define initial state e.g. default category, items per page
		$atts = shortcode_atts( [
			'per_page' => 12,
			'category' => '', // Allow filtering by category slug/ID initially
		], $atts, 'erins_seed_catalog_enhanced_view' );

		// Enqueue public styles first as a dependency
		wp_enqueue_style(
			'esc-public-styles',
			ESC_PLUGIN_URL . 'public/css/esc-public-styles.css',
			[],
			ESC_VERSION
		);

		// Enqueue enhanced card scripts and styles
		wp_enqueue_script(
			'esc-enhanced-cards-scripts',
			ESC_PLUGIN_URL . 'public/js/esc-enhanced-cards.js',
			['jquery'],
			ESC_VERSION,
			true
		);

		// Localize script with AJAX data
		wp_localize_script(
			'esc-enhanced-cards-scripts',
			'esc_ajax_object',
			[
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('esc_ajax_nonce'),
				'loading_text' => __('Loading...', 'erins-seed-catalog'),
				'error_text' => __('An error occurred.', 'erins-seed-catalog'),
			]
		);
		// Enqueue enhanced cards styles with better cache-busting
		$css_path = ESC_PLUGIN_DIR . 'public/css/esc-enhanced-cards.css';
		wp_enqueue_style(
			'esc-enhanced-cards-styles',
			ESC_PLUGIN_URL . 'public/css/esc-enhanced-cards.css',
			['esc-public-styles'],
			self::get_file_version($css_path)
		);
		
		// Enqueue enhanced cards 2024 styles
		$css_2024_path = ESC_PLUGIN_DIR . 'public/css/esc-enhanced-cards-2024.css';
		if (file_exists($css_2024_path)) {
			wp_enqueue_style(
				'esc-enhanced-cards-2024-styles',
				ESC_PLUGIN_URL . 'public/css/esc-enhanced-cards-2024.css',			['esc-enhanced-cards-styles'],
			self::get_file_version($css_2024_path) 
		);
		}

		// Enqueue dashicons if not already loaded
		wp_enqueue_style('dashicons');
		// Enqueue debug scripts in development environments with better cache-busting
		if (defined('WP_DEBUG') && WP_DEBUG) {
			$debug_js_path = ESC_PLUGIN_DIR . 'public/js/esc-debug.js';
			wp_enqueue_script(
				'esc-debug-script',
				ESC_PLUGIN_URL . 'public/js/esc-debug.js',
				['jquery', 'esc-enhanced-cards-scripts'],
				self::get_file_version($debug_js_path),
				true
			);

			// Enqueue image debug script
			$image_debug_js_path = ESC_PLUGIN_DIR . 'public/js/esc-image-debug.js';
			wp_enqueue_script(
				'esc-image-debug-script',
				ESC_PLUGIN_URL . 'public/js/esc-image-debug.js',
				['jquery', 'esc-enhanced-cards-scripts'],
				self::get_file_version($image_debug_js_path),
				true
			);

			// Enqueue image check script
			$image_check_js_path = ESC_PLUGIN_DIR . 'public/js/esc-image-check.js';
			wp_enqueue_script(
				'esc-image-check-script',
				ESC_PLUGIN_URL . 'public/js/esc-image-check.js',
				['jquery', 'esc-enhanced-cards-scripts'],
				self::get_file_version($image_check_js_path),
				true
			);

			// Enqueue image URL test script
			$image_url_test_js_path = ESC_PLUGIN_DIR . 'public/js/esc-image-url-test.js';
			wp_enqueue_script(
				'esc-image-url-test-script',
				ESC_PLUGIN_URL . 'public/js/esc-image-url-test.js',
				['jquery', 'esc-enhanced-cards-scripts'],
				self::get_file_version($image_url_test_js_path),
				true
			);
		}

		$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
		$per_page = absint($atts['per_page']);
		$initial_category_id = 0;

		if (!empty($atts['category'])) {
			$term = get_term_by('slug', $atts['category'], ESC_Taxonomy::TAXONOMY_NAME) ?: get_term_by('id', $atts['category'], ESC_Taxonomy::TAXONOMY_NAME);
			if ($term && !is_wp_error($term)) {
				$initial_category_id = $term->term_id;
			}
		}

		// Initial query args
		$args = [
			'limit'    => $per_page,
			'offset'   => ($paged - 1) * $per_page,
			'category' => $initial_category_id, // Pass term_id
		];

		$seeds = ESC_DB::get_seeds($args);
		$total_seeds = ESC_DB::count_seeds(['category' => $initial_category_id]); // Count matching initial filter
		$total_pages = ceil($total_seeds / $per_page);

		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/enhanced-seed-catalog-display.php'; // Pass $seeds, $paged, $total_pages, $initial_category_id to the view
		return ob_get_clean();
	}

	/**
	 * Render enhanced catalog view with improved styling
	 *
	 * This function is a variation of render_enhanced_catalog_view that uses the improved CSS.
	 * It should be used with the shortcode [erins_seed_catalog_improved_view]
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output of seed catalog
	 */
	public static function render_improved_catalog_view( $atts = [] ) {
		// IMPROVED_VIEW_START - Unique identifier for easier code maintenance
		// Attributes could define initial state e.g. default category, items per page
		$atts = shortcode_atts( [
			'per_page' => 12,
			'category' => '', // Allow filtering by category slug/ID initially
		], $atts, 'erins_seed_catalog_improved_view' );

		// Enqueue public styles first as a dependency
		wp_enqueue_style(
			'esc-public-styles',
			ESC_PLUGIN_URL . 'public/css/esc-public-styles.css',
			[],
			ESC_VERSION
		);

		// Enqueue enhanced card scripts and styles
		wp_enqueue_script(
			'esc-enhanced-cards-scripts',
			ESC_PLUGIN_URL . 'public/js/esc-enhanced-cards.js',
			['jquery'],
			ESC_VERSION,
			true
		);

		// Localize script with AJAX data
		wp_localize_script(
			'esc-enhanced-cards-scripts',
			'esc_ajax_object',
			[
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('esc_ajax_nonce'),
				'loading_text' => __('Loading...', 'erins-seed-catalog'),
				'error_text' => __('An error occurred.', 'erins-seed-catalog'),
			]
		);

		// Enqueue improved enhanced cards styles with better cache-busting
		$css_file_path = ESC_PLUGIN_DIR . 'public/css/esc-enhanced-cards-2024.css';
		wp_enqueue_style(
			'esc-enhanced-cards-styles-2024',
			ESC_PLUGIN_URL . 'public/css/esc-enhanced-cards-2024.css',
			['esc-public-styles'],
			self::get_file_version($css_file_path)
		);

		// Enqueue dashicons if not already loaded
		wp_enqueue_style('dashicons');

		// Enqueue debug scripts in development environments with improved versioning
		if (defined('WP_DEBUG') && WP_DEBUG) {
			$debug_js_path = ESC_PLUGIN_DIR . 'public/js/esc-debug.js';
			wp_enqueue_script(
				'esc-debug-script',
				ESC_PLUGIN_URL . 'public/js/esc-debug.js',
				['jquery', 'esc-enhanced-cards-scripts'],
				self::get_file_version($debug_js_path),
				true
			);

			$image_debug_js_path = ESC_PLUGIN_DIR . 'public/js/esc-image-debug.js';
			wp_enqueue_script(
				'esc-image-debug-script',
				ESC_PLUGIN_URL . 'public/js/esc-image-debug.js',
				['jquery', 'esc-enhanced-cards-scripts'],
				self::get_file_version($image_debug_js_path),
				true
			);

			$image_check_js_path = ESC_PLUGIN_DIR . 'public/js/esc-image-check.js';
			wp_enqueue_script(
				'esc-image-check-script',
				ESC_PLUGIN_URL . 'public/js/esc-image-check.js',
				['jquery', 'esc-enhanced-cards-scripts'],
				self::get_file_version($image_check_js_path),
				true
			);

			$image_url_test_js_path = ESC_PLUGIN_DIR . 'public/js/esc-image-url-test.js';
			wp_enqueue_script(
				'esc-image-url-test-script',
				ESC_PLUGIN_URL . 'public/js/esc-image-url-test.js',
				['jquery', 'esc-enhanced-cards-scripts'],
				self::get_file_version($image_url_test_js_path),
				true
			);
		}

		$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
		$per_page = absint($atts['per_page']);
		$initial_category_id = 0;

		// Handle shortcode attribute category if provided
		if (!empty($atts['category'])) {
			// Support both category slug and ID
			if (is_numeric($atts['category'])) {
				$initial_category_id = intval($atts['category']);
			} else {
				// Using the consistent taxonomy name from the Taxonomy class
				$term = get_term_by('slug', sanitize_text_field($atts['category']), ESC_Taxonomy::TAXONOMY_NAME);
				if ($term && !is_wp_error($term)) {
					$initial_category_id = $term->term_id;
				}
			}
		}

		// Get seed data for initial render with pagination
		// Using the correct class name case: ESC_DB (uppercase DB)
		$args = [
			'limit'    => $per_page,
			'offset'   => ($paged - 1) * $per_page,
			'category' => $initial_category_id, // Pass term_id
		];
		$seeds = ESC_DB::get_seeds($args);
		$total_seeds = ESC_DB::count_seeds(['category' => $initial_category_id]);
		$total_pages = ceil($total_seeds / $per_page);

		// Start output buffer
		ob_start();

		// Include the enhanced catalog display view for the initial render
		include ESC_PLUGIN_DIR . 'public/views/enhanced-seed-catalog-display.php';

		return ob_get_clean();
		// IMPROVED_VIEW_END - Unique identifier for easier code maintenance
	}

	/**
	 * Render the [erins_seed_catalog_test_ai_results] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the test AI results page.
	 */	public static function render_test_ai_results( $atts = [] ) {
		// Enqueue refactored CSS first with proper cache busting
		$refactored_css_path = ESC_PLUGIN_DIR . 'public/css/esc-refactored.css';
		wp_enqueue_style('esc-refactored', ESC_PLUGIN_URL . 'public/css/esc-refactored.css', [], 
			self::get_file_version($refactored_css_path));

		// Then load modern form CSS for backward compatibility
		$modern_form_css_path = ESC_PLUGIN_DIR . 'public/css/esc-modern-form.css';
		wp_enqueue_style('esc-modern-form', ESC_PLUGIN_URL . 'public/css/esc-modern-form.css', ['esc-refactored'], 
			self::get_file_version($modern_form_css_path));

		// Enqueue enhanced AI results CSS and JS
		$ai_results_css_path = ESC_PLUGIN_DIR . 'public/css/esc-ai-results-enhanced.css';
		$ai_results_js_path = ESC_PLUGIN_DIR . 'public/js/esc-ai-results-enhanced.js';
		wp_enqueue_style('esc-ai-results-enhanced', ESC_PLUGIN_URL . 'public/css/esc-ai-results-enhanced.css', 
			['esc-refactored', 'esc-modern-form'], self::get_file_version($ai_results_css_path));
		wp_enqueue_script('esc-ai-results-enhanced', ESC_PLUGIN_URL . 'public/js/esc-ai-results-enhanced.js', 
			['jquery'], self::get_file_version($ai_results_js_path), true);

		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/test-ai-results.php';
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_add_form_refactored] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the refactored form.
	 */
	public static function render_add_form_refactored( $atts = [] ) {
		// Enqueue design system and components CSS directly
		wp_enqueue_style('esc-design-system', ESC_PLUGIN_URL . 'public/css/esc-design-system.css', [], ESC_VERSION);
		wp_enqueue_style('esc-components', ESC_PLUGIN_URL . 'public/css/esc-components.css', ['esc-design-system'], ESC_VERSION);

		// Enqueue refactored CSS after its dependencies
		wp_enqueue_style('esc-refactored', ESC_PLUGIN_URL . 'public/css/esc-refactored.css', ['esc-design-system', 'esc-components'], ESC_VERSION);
		wp_enqueue_style('esc-variety-dropdown', ESC_PLUGIN_URL . 'public/css/esc-variety-dropdown.css', ['esc-refactored'], ESC_VERSION);

		// Enqueue modern form CSS for backward compatibility
		wp_enqueue_style('esc-modern-form', ESC_PLUGIN_URL . 'public/css/esc-modern-form.css', ['esc-refactored'], ESC_VERSION);

		// Enqueue our new modular JavaScript files
		wp_enqueue_script('esc-core', ESC_PLUGIN_URL . 'public/js/esc-core.js', ['jquery'], ESC_VERSION, true);

		// Localize script for AJAX calls before loading dependent scripts
		$ajax_data = [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('esc_ajax_nonce'),
			'loading_text' => __('Loading...', 'erins-seed-catalog'),
			'error_text' => __('An error occurred.', 'erins-seed-catalog'),
			'gemini_error_text' => __('Error fetching AI info:', 'erins-seed-catalog'),
			'form_submit_success' => __('Seed added successfully!', 'erins-seed-catalog'),
			'form_submit_error' => __('Error adding seed.', 'erins-seed-catalog'),
			'catalog_url' => home_url('/seed-catalog/'),
			'add_another_text' => __('Add Another Seed', 'erins-seed-catalog'),
			'view_catalog_text' => __('View Catalog', 'erins-seed-catalog'),
			'site_url' => get_site_url(),
			'debug' => true // Enable debug mode for troubleshooting
		];

		wp_localize_script('esc-core', 'esc_ajax_object', $ajax_data);

		// Now load dependent scripts
		wp_enqueue_script('esc-ui', ESC_PLUGIN_URL . 'public/js/esc-ui.js', ['esc-core'], ESC_VERSION, true);
		wp_enqueue_script('esc-form', ESC_PLUGIN_URL . 'public/js/esc-form.js', ['esc-core', 'esc-ui'], ESC_VERSION, true);
		wp_enqueue_script('esc-ai', ESC_PLUGIN_URL . 'public/js/esc-ai.js', ['esc-core', 'esc-form'], ESC_VERSION, true);
		wp_enqueue_script('esc-variety', ESC_PLUGIN_URL . 'public/js/esc-variety.js', ['esc-core', 'esc-form'], ESC_VERSION, true);

		// AJAX data already localized above

		// Enqueue dashicons
		wp_enqueue_style('dashicons');

		// Enqueue Select2 for category dropdown
		wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0-rc.0');
		wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0-rc.0', true);

		// Enqueue image uploader scripts and styles
		wp_enqueue_style('esc-image-uploader', ESC_PLUGIN_URL . 'public/css/esc-image-uploader.css', [], ESC_VERSION);
		wp_enqueue_script('esc-image-uploader', ESC_PLUGIN_URL . 'public/js/esc-image-uploader.js', ['jquery'], ESC_VERSION, true);

		// Enqueue WordPress media if user can upload
		if (current_user_can('upload_files')) {
			wp_enqueue_media();
		}

		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/add-seed-form-refactored.php';
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_test_integration] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the integration test page.
	 */	public static function render_test_integration( $atts = [] ) {
		// Enqueue all necessary styles and scripts
		wp_enqueue_style('esc-refactored', ESC_PLUGIN_URL . 'public/css/esc-refactored.css', [], ESC_VERSION);
		wp_enqueue_style('esc-modern-form', ESC_PLUGIN_URL . 'public/css/esc-modern-form.css', [], ESC_VERSION);
		wp_enqueue_style('dashicons');

		// Include the test integration template
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/test-integration.php';
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_test_enhanced_view] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the test enhanced view page.
	 */
	public static function render_test_enhanced_view( $atts = [] ) {
		// Enqueue enhanced card scripts and styles
		wp_enqueue_script(
			'esc-enhanced-cards-scripts',
			ESC_PLUGIN_URL . 'public/js/esc-enhanced-cards.js',
			['jquery'],
			ESC_VERSION,
			true
		);

		// Localize script with AJAX data
		wp_localize_script(
			'esc-enhanced-cards-scripts',
			'esc_ajax_object',
			[
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('esc_ajax_nonce'),
				'loading_text' => __('Loading...', 'erins-seed-catalog'),
				'error_text' => __('An error occurred.', 'erins-seed-catalog'),
				'site_url' => get_site_url(),
			]
		);

		wp_enqueue_style(
			'esc-enhanced-cards-styles',
			ESC_PLUGIN_URL . 'public/css/esc-enhanced-cards.css',
			['esc-public-styles'],
			ESC_VERSION
		);

		// Enqueue dashicons if not already loaded
		wp_enqueue_style('dashicons');

		// Include the test enhanced view template
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/test-enhanced-view.php';
		return ob_get_clean();
	}

	/**
	 * Render the [esc_fixed_enhanced_view] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the fixed enhanced view.
	 */
	public static function render_fixed_enhanced_view( $atts = [] ) {
		// Parse shortcode attributes
		$atts = shortcode_atts( [
			'per_page' => 12,
			'category' => '',
		], $atts, 'esc_fixed_enhanced_view' );

		// Enqueue public styles first as a dependency
		wp_enqueue_style(
			'esc-public-styles',
			ESC_PLUGIN_URL . 'public/css/esc-public-styles.css',
			[],
			ESC_VERSION
		);

		// Enqueue enhanced card scripts and styles
		wp_enqueue_script(
			'esc-enhanced-cards-scripts',
			ESC_PLUGIN_URL . 'public/js/esc-enhanced-cards.js',
			['jquery'],
			ESC_VERSION,
			true
		);

		// Localize script with AJAX data
		wp_localize_script(
			'esc-enhanced-cards-scripts',
			'esc_ajax_object',
			[
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('esc_ajax_nonce'),
				'loading_text' => __('Loading...', 'erins-seed-catalog'),
				'error_text' => __('An error occurred.', 'erins-seed-catalog'),
				'site_url' => get_site_url(),
			]
		);
		// Enqueue enhanced cards styles with better cache-busting
		$css_path = ESC_PLUGIN_DIR . 'public/css/esc-enhanced-cards.css';
		wp_enqueue_style(
			'esc-enhanced-cards-styles',
			ESC_PLUGIN_URL . 'public/css/esc-enhanced-cards.css',
			['esc-public-styles'],
			self::get_file_version($css_path)
		);

		// Enqueue dashicons if not already loaded
		wp_enqueue_style('dashicons');

		// Get seeds from the database
		$paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
		$per_page = absint($atts['per_page']);
		$initial_category_id = 0;

		if (!empty($atts['category'])) {
			$term = get_term_by('slug', $atts['category'], ESC_Taxonomy::TAXONOMY_NAME) ?: get_term_by('id', $atts['category'], ESC_Taxonomy::TAXONOMY_NAME);
			if ($term && !is_wp_error($term)) {
				$initial_category_id = $term->term_id;
			}
		}

		// Initial query args
		$args = [
			'limit'    => $per_page,
			'offset'   => ($paged - 1) * $per_page,
			'category' => $initial_category_id, // Pass term_id
		];

		$seeds = ESC_DB::get_seeds($args);
		$total_seeds = ESC_DB::count_seeds(['category' => $initial_category_id]); // Count matching initial filter
		$total_pages = ceil($total_seeds / $per_page);

		// Debug output
		$debug_output = '';
		$debug_output .= '<div class="esc-debug" style="background: #f8f8f8; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd;">';
		$debug_output .= '<h3>Debug Information</h3>';
		$debug_output .= '<p>Total Seeds: ' . $total_seeds . '</p>';
		$debug_output .= '<p>Seeds Retrieved: ' . count($seeds) . '</p>';
		$debug_output .= '<p>Page: ' . $paged . ' of ' . $total_pages . '</p>';
		$debug_output .= '</div>';

		// Start output buffer
		ob_start();

		// Output debug information
		echo $debug_output;

		// Include the enhanced seed catalog display template
		include ESC_PLUGIN_DIR . 'public/views/enhanced-seed-catalog-display.php';

		// Return the output
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_test_refactored] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the test refactored form page.
	 */
	public static function render_test_refactored( $atts = [] ) {
		// Enqueue all necessary styles and scripts
		wp_enqueue_style('esc-design-system', ESC_PLUGIN_URL . 'public/css/esc-design-system.css', [], ESC_VERSION);
		wp_enqueue_style('esc-components', ESC_PLUGIN_URL . 'public/css/esc-components.css', ['esc-design-system'], ESC_VERSION);
		wp_enqueue_style('esc-refactored', ESC_PLUGIN_URL . 'public/css/esc-refactored.css', ['esc-design-system', 'esc-components'], ESC_VERSION);
		wp_enqueue_style('esc-variety-dropdown', ESC_PLUGIN_URL . 'public/css/esc-variety-dropdown.css', ['esc-refactored'], ESC_VERSION);
		wp_enqueue_style('esc-modern-form', ESC_PLUGIN_URL . 'public/css/esc-modern-form.css', ['esc-refactored'], ESC_VERSION);
		wp_enqueue_style('esc-image-uploader', ESC_PLUGIN_URL . 'public/css/esc-image-uploader.css', [], ESC_VERSION);
		wp_enqueue_style('dashicons');

		// Enqueue Select2 for category dropdown
		wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0-rc.0');
		wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0-rc.0', true);

		// Enqueue JavaScript files
		wp_enqueue_script('esc-core', ESC_PLUGIN_URL . 'public/js/esc-core.js', ['jquery'], ESC_VERSION, true);

		// Localize script for AJAX calls
		$ajax_data = [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('esc_ajax_nonce'),
			'loading_text' => __('Loading...', 'erins-seed-catalog'),
			'error_text' => __('An error occurred.', 'erins-seed-catalog'),
			'site_url' => get_site_url(),
			'debug' => true
		];

		wp_localize_script('esc-core', 'esc_ajax_object', $ajax_data);

		// Load dependent scripts
		wp_enqueue_script('esc-ui', ESC_PLUGIN_URL . 'public/js/esc-ui.js', ['esc-core'], ESC_VERSION, true);
		wp_enqueue_script('esc-form', ESC_PLUGIN_URL . 'public/js/esc-form.js', ['esc-core', 'esc-ui'], ESC_VERSION, true);
		wp_enqueue_script('esc-ai', ESC_PLUGIN_URL . 'public/js/esc-ai.js', ['esc-core', 'esc-form'], ESC_VERSION, true);
		wp_enqueue_script('esc-variety', ESC_PLUGIN_URL . 'public/js/esc-variety.js', ['esc-core', 'esc-form'], ESC_VERSION, true);
		wp_enqueue_script('esc-image-uploader', ESC_PLUGIN_URL . 'public/js/esc-image-uploader.js', ['jquery'], ESC_VERSION, true);

		// Enqueue WordPress media if user can upload
		if (current_user_can('upload_files')) {
			wp_enqueue_media();
		}

		// Include the test refactored form template
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/test-refactored-form.php';
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_debug] shortcode - for troubleshooting
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Debug information
	 */
	public static function render_debug_info( $atts = [] ) {
		global $wpdb;
		
		// Output buffer for the debug info
		ob_start();
		
		echo '<div class="esc-debug-info" style="background: #f8f8f8; border: 1px solid #ddd; padding: 20px; margin: 20px 0; font-family: monospace;">';
		echo '<h2>Erin\'s Seed Catalog Debug Info</h2>';
		
		// Check if required files exist
		echo '<h3>Required Files</h3>';
		$files_to_check = [
			'enhanced-seed-catalog-display.php' => ESC_PLUGIN_DIR . 'public/views/enhanced-seed-catalog-display.php',
			'seed-search-form.php' => ESC_PLUGIN_DIR . 'public/views/seed-search-form.php',
			'_enhanced-seed-card.php' => ESC_PLUGIN_DIR . 'public/views/_enhanced-seed-card.php',
			'esc-enhanced-cards.css' => ESC_PLUGIN_DIR . 'public/css/esc-enhanced-cards.css',
			'esc-enhanced-cards-2024.css' => ESC_PLUGIN_DIR . 'public/css/esc-enhanced-cards-2024.css'
		];
		
		echo '<ul>';
		foreach ($files_to_check as $name => $path) {
			$exists = file_exists($path);
			echo '<li>' . esc_html($name) . ': ' . ($exists ? '✅ Exists' : '❌ Missing') . '</li>';
		}
		echo '</ul>';
		
		// Check database
		echo '<h3>Database Check</h3>';
		$table_name = $wpdb->prefix . 'esc_seeds';
		$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
		echo '<p>Seeds Table: ' . ($table_exists ? '✅ Exists' : '❌ Missing') . '</p>';
		
		if ($table_exists) {
			$seed_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
			echo '<p>Total Seeds: ' . intval($seed_count) . '</p>';
			
			// Get a sample seed
			$sample_seed = $wpdb->get_row("SELECT * FROM $table_name LIMIT 1");
			if ($sample_seed) {
				echo '<p>Sample Seed: ID=' . esc_html($sample_seed->id) . ', Name=' . esc_html($sample_seed->seed_name) . '</p>';
			} else {
				echo '<p>No seeds found in database</p>';
			}
		}
		
		// Check if shortcodes are registered properly
		echo '<h3>Shortcode Registration</h3>';
		echo '<p>This function is running, which means the class is loaded correctly.</p>';
		
		// Try getting seeds directly
		echo '<h3>Direct Database Query Test</h3>';
		try {
			$seeds = ESC_DB::get_seeds(['limit' => 3]);
			echo '<p>ESC_DB::get_seeds returned: ' . count($seeds) . ' seeds</p>';
			if (!empty($seeds)) {
				echo '<ul>';
				foreach ($seeds as $seed) {
					echo '<li>ID: ' . esc_html($seed->id) . ', Name: ' . esc_html($seed->seed_name) . '</li>';
				}
				echo '</ul>';
			}
		} catch (Exception $e) {
			echo '<p>Error: ' . esc_html($e->getMessage()) . '</p>';
		}
		
		// Verify CSS Loading
		echo '<h3>CSS Files</h3>';
		global $wp_styles;
		echo '<p>Currently Enqueued Styles:</p>';
		echo '<ul>';
		foreach ($wp_styles->registered as $handle => $style) {
			if (strpos($handle, 'esc') !== false) {
				echo '<li>' . esc_html($handle) . ' - ' . esc_html($style->src) . '</li>';
			}
		}
		echo '</ul>';
		
		echo '</div>';
		
		return ob_get_clean();
	}
}

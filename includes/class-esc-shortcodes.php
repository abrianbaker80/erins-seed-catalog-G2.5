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
	 * Initialize shortcodes.
	 */
	public static function init() {
		add_shortcode( 'erins_seed_catalog_add_form', [ __CLASS__, 'render_add_form' ] );
		add_shortcode( 'erins_seed_catalog_view', [ __CLASS__, 'render_catalog_view' ] );
		add_shortcode( 'erins_seed_catalog_search', [ __CLASS__, 'render_search_form' ] );
		add_shortcode( 'erins_seed_catalog_categories', [ __CLASS__, 'render_category_list' ] );
		add_shortcode( 'erins_seed_catalog_export', [ __CLASS__, 'render_export_form' ] );
		add_shortcode( 'erins_seed_catalog_enhanced_view', [ __CLASS__, 'render_enhanced_catalog_view' ] );

		// Add a test shortcode to verify modern form is working
		add_shortcode( 'erins_seed_catalog_add_form_modern', [ __CLASS__, 'render_add_form_modern' ] );

		// Add a test shortcode for the enhanced AI results page
		add_shortcode( 'erins_seed_catalog_test_ai_results', [ __CLASS__, 'render_test_ai_results' ] );
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

		// Force debug output to help troubleshoot
		echo '<!-- Using modern form template -->';

		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/add-seed-form-modern.php';
		return ob_get_clean();
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
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the enhanced catalog view.
	 */
	public static function render_enhanced_catalog_view( $atts = [] ) {
		// Attributes could define initial state e.g. default category, items per page
		$atts = shortcode_atts( [
			'per_page' => 12,
			'category' => '', // Allow filtering by category slug/ID initially
		], $atts, 'erins_seed_catalog_enhanced_view' );

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

		wp_enqueue_style(
			'esc-enhanced-cards-styles',
			ESC_PLUGIN_URL . 'public/css/esc-enhanced-cards.css',
			['esc-public-styles'],
			ESC_VERSION
		);

		// Enqueue dashicons if not already loaded
		wp_enqueue_style('dashicons');

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
	 * Render the [erins_seed_catalog_test_ai_results] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the test AI results page.
	 */
	public static function render_test_ai_results( $atts = [] ) {
		// Make sure modern form CSS is loaded first
		wp_enqueue_style('esc-modern-form', ESC_PLUGIN_URL . 'public/css/esc-modern-form.css', [], ESC_VERSION . '.' . time());

		// Enqueue enhanced AI results CSS and JS
		wp_enqueue_style('esc-ai-results-enhanced', ESC_PLUGIN_URL . 'public/css/esc-ai-results-enhanced.css', ['esc-modern-form'], ESC_VERSION . '.' . time());
		wp_enqueue_script('esc-ai-results-enhanced', ESC_PLUGIN_URL . 'public/js/esc-ai-results-enhanced.js', ['jquery'], ESC_VERSION . '.' . time(), true);

		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/test-ai-results.php';
		return ob_get_clean();
	}
}
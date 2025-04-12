<?php
/**
 * Shortcodes for Erin's Seed Catalog
 *
 * @package    Erins_Seed_Catalog
 * @subpackage Erins_Seed_Catalog/includes
 */

/**
 * Shortcodes for Erin's Seed Catalog
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

		// Add a shortcode for the refactored form
		add_shortcode( 'erins_seed_catalog_add_form_refactored', [ __CLASS__, 'render_add_form_refactored' ] );
	}

	/**
	 * Render the [erins_seed_catalog_add_form] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the add form.
	 */
	public static function render_add_form( $atts = [] ) {
		// Enqueue styles and scripts
		wp_enqueue_style( 'esc-form-style', ESC_PLUGIN_URL . 'public/css/esc-form.css', [], ESC_VERSION );
		wp_enqueue_script( 'esc-form-script', ESC_PLUGIN_URL . 'public/js/esc-form.js', [ 'jquery' ], ESC_VERSION, true );

		// Localize script for AJAX calls
		wp_localize_script(
			'esc-form-script',
			'esc_ajax_object',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'esc_ajax_nonce' ),
			]
		);

		// Include the form template
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/add-seed-form.php';
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_view] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the catalog view.
	 */
	public static function render_catalog_view( $atts = [] ) {
		// Parse attributes
		$atts = shortcode_atts(
			[
				'category' => '',
				'per_page' => 10,
			],
			$atts,
			'erins_seed_catalog_view'
		);

		// Enqueue styles
		wp_enqueue_style( 'esc-catalog-style', ESC_PLUGIN_URL . 'public/css/esc-catalog.css', [], ESC_VERSION );

		// Get current page
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

		// Get seeds
		$args = [
			'post_type'      => 'esc_seed',
			'posts_per_page' => $atts['per_page'],
			'paged'          => $paged,
		];

		// Add category filter if specified
		if ( ! empty( $atts['category'] ) ) {
			$args['tax_query'] = [
				[
					'taxonomy' => ESC_Taxonomy::TAXONOMY_NAME,
					'field'    => 'slug',
					'terms'    => $atts['category'],
				],
			];
		}

		$seeds_query = new WP_Query( $args );
		$seeds       = $seeds_query->posts;

		// Calculate total pages
		$total_pages = $seeds_query->max_num_pages;

		// Include the catalog template
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/seed-catalog-display.php'; // Pass $seeds, $paged, $total_pages to the view
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_search] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the search form.
	 */
	public static function render_search_form( $atts = [] ) {
		// Enqueue styles
		wp_enqueue_style( 'esc-search-style', ESC_PLUGIN_URL . 'public/css/esc-search.css', [], ESC_VERSION );

		// Include the search template
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/search-form.php';
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_categories] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the category list.
	 */
	public static function render_category_list( $atts = [] ) {
		// Parse attributes
		$atts = shortcode_atts(
			[
				'title'     => __( 'Seed Categories', 'erins-seed-catalog' ),
				'show_count' => true,
			],
			$atts,
			'erins_seed_catalog_categories'
		);

		// Enqueue styles
		wp_enqueue_style( 'esc-categories-style', ESC_PLUGIN_URL . 'public/css/esc-categories.css', [], ESC_VERSION );

		// Get categories
		$categories = get_terms(
			[
				'taxonomy'   => ESC_Taxonomy::TAXONOMY_NAME,
				'hide_empty' => false,
			]
		);

		// Include the categories template
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/category-list.php'; // Pass $categories, $atts to the view
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_export] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the export form.
	 */
	public static function render_export_form( $atts = [] ) {
		// Check if user has permission to export
		if ( ! current_user_can( 'manage_options' ) ) {
			return '<p>' . __( 'You do not have permission to export data.', 'erins-seed-catalog' ) . '</p>';
		}

		// Enqueue styles and scripts
		wp_enqueue_style( 'esc-export-style', ESC_PLUGIN_URL . 'public/css/esc-export.css', [], ESC_VERSION );
		wp_enqueue_script( 'esc-export-script', ESC_PLUGIN_URL . 'public/js/esc-export.js', [ 'jquery' ], ESC_VERSION, true );

		// Localize script for AJAX calls
		wp_localize_script(
			'esc-export-script',
			'esc_export_object',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'esc_export_nonce' ),
			]
		);

		// Include the export template
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/export-form.php';
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_enhanced_view] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the enhanced catalog view.
	 */
	public static function render_enhanced_catalog_view( $atts = [] ) {
		// Parse attributes
		$atts = shortcode_atts(
			[
				'category' => '',
				'per_page' => 10,
			],
			$atts,
			'erins_seed_catalog_enhanced_view'
		);

		// Enqueue styles and scripts
		wp_enqueue_style( 'esc-enhanced-catalog-style', ESC_PLUGIN_URL . 'public/css/esc-enhanced-catalog.css', [], ESC_VERSION );
		wp_enqueue_script( 'esc-enhanced-catalog-script', ESC_PLUGIN_URL . 'public/js/esc-enhanced-catalog.js', [ 'jquery' ], ESC_VERSION, true );

		// Get current page
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

		// Get initial category ID if specified
		$initial_category_id = 0;
		if ( ! empty( $atts['category'] ) ) {
			$category_term = get_term_by( 'slug', $atts['category'], ESC_Taxonomy::TAXONOMY_NAME );
			if ( $category_term ) {
				$initial_category_id = $category_term->term_id;
			}
		}

		// Get seeds
		$args = [
			'post_type'      => 'esc_seed',
			'posts_per_page' => $atts['per_page'],
			'paged'          => $paged,
		];

		// Add category filter if specified
		if ( $initial_category_id > 0 ) {
			$args['tax_query'] = [
				[
					'taxonomy' => ESC_Taxonomy::TAXONOMY_NAME,
					'field'    => 'term_id',
					'terms'    => $initial_category_id,
				],
			];
		}

		$seeds_query = new WP_Query( $args );
		$seeds       = $seeds_query->posts;

		// Calculate total pages
		$total_pages = $seeds_query->max_num_pages;

		// Include the enhanced catalog template
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/enhanced-seed-catalog-display.php'; // Pass $seeds, $paged, $total_pages, $initial_category_id to the view
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_add_form_modern] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the modern add form.
	 */
	public static function render_add_form_modern( $atts = [] ) {
		// Enqueue Modern Form CSS first (base styles)
		wp_enqueue_style(
			'esc-modern-form',
			ESC_PLUGIN_URL . 'public/css/esc-modern-form.css',
			[],
			ESC_VERSION
		);

		// Enqueue Simplified CSS
		wp_enqueue_style(
			'esc-simplified',
			ESC_PLUGIN_URL . 'public/css/esc-simplified.css',
			['esc-modern-form'],
			ESC_VERSION
		);

		// Enqueue Variety Suggestions CSS
		wp_enqueue_style(
			'esc-variety-suggestions',
			ESC_PLUGIN_URL . 'public/css/esc-variety-suggestions.css',
			['esc-modern-form', 'esc-simplified'],
			ESC_VERSION
		);

		// Enqueue AI Results CSS
		wp_enqueue_style(
			'esc-ai-results',
			ESC_PLUGIN_URL . 'public/css/esc-ai-results.css',
			['esc-modern-form', 'esc-simplified'],
			ESC_VERSION
		);

		// Enqueue AI Results Fixes CSS
		wp_enqueue_style(
			'esc-ai-results-fixes',
			ESC_PLUGIN_URL . 'public/css/esc-ai-results-fixes.css',
			['esc-modern-form', 'esc-simplified', 'esc-ai-results'],
			ESC_VERSION
		);

		// Enqueue scripts
		wp_enqueue_script(
			'esc-modern-form',
			ESC_PLUGIN_URL . 'public/js/esc-modern-form.js',
			['jquery'],
			ESC_VERSION,
			true
		);

		// Enqueue Variety Suggestions JS
		wp_enqueue_script(
			'esc-variety-suggestions',
			ESC_PLUGIN_URL . 'public/js/esc-variety-suggestions.js',
			['jquery', 'esc-modern-form'],
			ESC_VERSION,
			true
		);

		// Enqueue AI Results JS
		wp_enqueue_script(
			'esc-ai-results',
			ESC_PLUGIN_URL . 'public/js/esc-ai-results.js',
			['jquery', 'esc-modern-form'],
			ESC_VERSION,
			true
		);

		// Localize script for AJAX calls
		wp_localize_script(
			'esc-modern-form',
			'esc_ajax_object',
			[
				'ajax_url'          => admin_url('admin-ajax.php'),
				'nonce'             => wp_create_nonce('esc_ajax_nonce'),
				'loading_text'      => __('Loading...', 'erins-seed-catalog'),
				'error_text'        => __('An error occurred.', 'erins-seed-catalog'),
				'gemini_error_text' => __('Error fetching AI info:', 'erins-seed-catalog'),
			]
		);

		// Include the modern form template
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/add-seed-form-modern.php';
		return ob_get_clean();
	}

	/**
	 * Render the [erins_seed_catalog_test_ai_results] shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the test AI results page.
	 */
	public static function render_test_ai_results( $atts = [] ) {
		// Enqueue refactored CSS first
		wp_enqueue_style('esc-refactored', ESC_PLUGIN_URL . 'public/css/esc-refactored.css', [], ESC_VERSION . '.' . time());
		
		// Then load modern form CSS for backward compatibility
		wp_enqueue_style('esc-modern-form', ESC_PLUGIN_URL . 'public/css/esc-modern-form.css', ['esc-refactored'], ESC_VERSION . '.' . time());
		
		// Enqueue enhanced AI results CSS and JS
		wp_enqueue_style('esc-ai-results-enhanced', ESC_PLUGIN_URL . 'public/css/esc-ai-results-enhanced.css', ['esc-refactored', 'esc-modern-form'], ESC_VERSION . '.' . time());
		wp_enqueue_script('esc-ai-results-enhanced', ESC_PLUGIN_URL . 'public/js/esc-ai-results-enhanced.js', ['jquery'], ESC_VERSION . '.' . time(), true);

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
		// Enqueue refactored CSS
		wp_enqueue_style('esc-refactored', ESC_PLUGIN_URL . 'public/css/esc-refactored.css', [], ESC_VERSION);
		
		// Enqueue our new modular JavaScript files
		wp_enqueue_script('esc-core', ESC_PLUGIN_URL . 'public/js/esc-core.js', ['jquery'], ESC_VERSION, true);
		wp_enqueue_script('esc-ui', ESC_PLUGIN_URL . 'public/js/esc-ui.js', ['esc-core'], ESC_VERSION, true);
		wp_enqueue_script('esc-form', ESC_PLUGIN_URL . 'public/js/esc-form.js', ['esc-core'], ESC_VERSION, true);
		wp_enqueue_script('esc-ai', ESC_PLUGIN_URL . 'public/js/esc-ai.js', ['esc-core'], ESC_VERSION, true);
		wp_enqueue_script('esc-variety', ESC_PLUGIN_URL . 'public/js/esc-variety.js', ['esc-core'], ESC_VERSION, true);
		
		// Localize script for AJAX calls
		$ajax_data = [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('esc_ajax_nonce'),
			'loading_text' => __('Loading...', 'erins-seed-catalog'),
			'error_text' => __('An error occurred.', 'erins-seed-catalog'),
			'gemini_error_text' => __('Error fetching AI info:', 'erins-seed-catalog'),
			'form_submit_success' => __('Seed added successfully!', 'erins-seed-catalog'),
			'form_submit_error' => __('Error adding seed.', 'erins-seed-catalog'),
			'catalog_url' => get_permalink(get_option('esc_catalog_page_id')) ?: home_url('/seed-catalog/'),
			'add_another_text' => __('Add Another Seed', 'erins-seed-catalog'),
			'view_catalog_text' => __('View Catalog', 'erins-seed-catalog'),
		];
		
		wp_localize_script('esc-core', 'esc_ajax_object', $ajax_data);
		
		// Enqueue dashicons
		wp_enqueue_style('dashicons');
		
		ob_start();
		include ESC_PLUGIN_DIR . 'public/views/add-seed-form-refactored.php';
		return ob_get_clean();
	}
}

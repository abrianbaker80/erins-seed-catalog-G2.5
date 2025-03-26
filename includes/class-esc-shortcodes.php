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
}
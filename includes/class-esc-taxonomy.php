<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ESC_Taxonomy
 * Handles the custom 'seed_category' taxonomy.
 */
class ESC_Taxonomy {

	const TAXONOMY_NAME = 'esc_seed_category';

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'register' ] );
        // Note: We don't add admin columns for this taxonomy to posts, as it's not linked to posts.
        // We'll manage categories via the standard WP Term interface (under a custom menu or Posts/Categories if `show_in_menu` is true)
        // or potentially a custom admin interface if needed.
	}

	/**
	 * Register the custom hierarchical taxonomy.
	 */
	public static function register() {
		$labels = [
			'name'              => _x( 'Seed Categories', 'taxonomy general name', 'erins-seed-catalog' ),
			'singular_name'     => _x( 'Seed Category', 'taxonomy singular name', 'erins-seed-catalog' ),
			'search_items'      => __( 'Search Seed Categories', 'erins-seed-catalog' ),
			'all_items'         => __( 'All Seed Categories', 'erins-seed-catalog' ),
			'parent_item'       => __( 'Parent Seed Category', 'erins-seed-catalog' ),
			'parent_item_colon' => __( 'Parent Seed Category:', 'erins-seed-catalog' ),
			'edit_item'         => __( 'Edit Seed Category', 'erins-seed-catalog' ),
			'update_item'       => __( 'Update Seed Category', 'erins-seed-catalog' ),
			'add_new_item'      => __( 'Add New Seed Category', 'erins-seed-catalog' ),
			'new_item_name'     => __( 'New Seed Category Name', 'erins-seed-catalog' ),
			'menu_name'         => __( 'Seed Categories', 'erins-seed-catalog' ),
		];

		$args = [
			'labels'            => $labels,
			'hierarchical'      => true, // Important for parent/child relationships
			'public'            => true, // Make it visible on frontend if needed (e.g., for filtering URLs)
			'show_ui'           => true, // Show in the admin UI
			'show_admin_column' => false, // No admin column on post types (we don't use post types)
            'show_in_menu'      => true, // Show under the main Seed Catalog admin menu
            'show_in_nav_menus' => true,
            'show_tagcloud'     => false,
			'query_var'         => true, // Allow querying by taxonomy term in URL (?esc_seed_category=...)
			'rewrite'           => [ 'slug' => 'seed-category' ], // Frontend URL slug
            'show_in_rest'      => true, // Enable for REST API access if needed later
            // 'object_type'       => [], // We link manually via our relationship table, not via WP's object type registration
		];

		register_taxonomy( self::TAXONOMY_NAME, null, $args ); // Register with null object_type initially
	}

    /**
	 * Add some default seed categories on activation.
	 */
	public static function add_default_terms() {
		$parent_terms = [
			'Field & Forage Crops',
			'Fruits',
			'Grains & Cereals',
			'Grasses',
			'Herbs',
			'Ornamental Flowers',
			'Specialty Seeds',
			'Trees & Shrubs',
			'Vegetables',
		];

		$child_terms = [
			'Field & Forage Crops' => [ 'Cover Crops', 'Fiber Crops', 'Forage Crops', 'Oilseeds' ],
			'Fruits'              => [ 'Berries', 'Melons' ],
			'Grains & Cereals'     => [],
			'Grasses'             => [ 'Forage Grasses', 'Lawn/Turf Grasses', 'Ornamental Grasses' ],
			'Herbs'               => [ 'Aromatic Herbs', 'Culinary Herbs', 'Medicinal Herbs' ],
			'Ornamental Flowers'   => [ 'Cut Flowers', 'General Garden Flowers', 'Native/Wildflower Seeds' ],
			'Specialty Seeds'      => [ 'Sprouts/Microgreens' ],
			'Trees & Shrubs'       => [ 'Fruit Trees/Shrubs', 'Ornamental Trees', 'Ornamental Shrubs' ],
			'Vegetables'           => [ 'Allium (Onion family)', 'Brassica (Cabbage family)', 'Cucurbit (Gourd family)', 'Leafy Greens', 'Legumes', 'Root Crops', 'Solanaceous' ],
		];

		foreach ( $parent_terms as $term_name ) {
			$term = wp_insert_term( $term_name, self::TAXONOMY_NAME );

            // Add children if parent insertion was successful and children exist
			if ( ! is_wp_error( $term ) && isset( $child_terms[ $term_name ] ) ) {
                $parent_term_id = $term['term_id'];
                foreach ( $child_terms[ $term_name ] as $child_name ) {
                    wp_insert_term( $child_name, self::TAXONOMY_NAME, [ 'parent' => $parent_term_id ] );
                }
			}
		}
	}

    /**
     * Get hierarchical category options suitable for a <select> dropdown.
     *
     * @param int $selected_term_id The term ID that should be pre-selected.
     * @return string HTML options string.
     */
    public static function get_category_dropdown_options( $selected_term_ids = [] ) : string {
        if (!is_array($selected_term_ids)) {
            $selected_term_ids = [$selected_term_ids];
        }
        $selected_term_ids = array_map('absint', $selected_term_ids);

        $terms = get_terms( [
            'taxonomy'   => self::TAXONOMY_NAME,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ] );

        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        // Build a hierarchical array
        $term_tree = [];
        $term_map = [];
        foreach ($terms as $term) {
            $term_map[$term->term_id] = $term;
            if ($term->parent == 0) {
                $term_tree[$term->term_id] = ['term' => $term, 'children' => []];
            }
        }
        foreach ($terms as $term) {
            if ($term->parent != 0 && isset($term_map[$term->parent])) {
                // Find the parent in the tree (could be nested)
                 $parent_ref = self::find_term_in_tree($term_tree, $term->parent);
                 if ($parent_ref) {
                    $parent_ref['children'][$term->term_id] = ['term' => $term, 'children' => []];
                 } else {
                     // Orphaned? Add to top level for now. Should ideally not happen with get_terms.
                     $term_tree[$term->term_id] = ['term' => $term, 'children' => []];
                 }
            }
        }


        // Generate dropdown options recursively
        $options_html = '';
        $options_html .= self::build_option_html($term_tree, $selected_term_ids, 0);

        return $options_html;
    }

    // Recursive helper to find a term node in the tree by ID
    private static function &find_term_in_tree(&$tree, $term_id) {
        foreach ($tree as $id => &$node) {
            if ($id === $term_id) {
                return $node;
            }
            if (!empty($node['children'])) {
                $found = self::find_term_in_tree($node['children'], $term_id);
                if ($found) {
                    return $found;
                }
            }
        }
        $null_ref = null; // Return null reference if not found
        return $null_ref;
    }


    // Recursive helper to build HTML options
    private static function build_option_html($term_nodes, $selected_ids, $level = 0) {
        $html = '';
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $level); // Indentation for hierarchy

        // Sort nodes alphabetically by term name before processing
        uasort($term_nodes, function($a, $b) {
            return strcmp($a['term']->name, $b['term']->name);
        });


        foreach ($term_nodes as $node) {
            $term = $node['term'];
            $is_selected = in_array($term->term_id, $selected_ids);
            $html .= sprintf(
                '<option value="%d" %s>%s%s</option>',
                esc_attr($term->term_id),
                selected($is_selected, true, false), // Use selected() WP helper
                $indent,
                esc_html($term->name)
            );
            if (!empty($node['children'])) {
                $html .= self::build_option_html($node['children'], $selected_ids, $level + 1);
            }
        }
        return $html;
    }

     /**
     * Get Term Taxonomy IDs from Term IDs for saving relationships.
     *
     * @param array $term_ids Array of term IDs.
     * @return array Array of corresponding term_taxonomy_ids.
     */
    public static function get_term_taxonomy_ids( array $term_ids ) : array {
        global $wpdb;
        if ( empty( $term_ids ) ) {
            return [];
        }
        $term_ids = array_map( 'absint', $term_ids );
        $id_placeholders = implode( ',', array_fill( 0, count( $term_ids ), '%d' ) );

        $sql = $wpdb->prepare(
            "SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s AND term_id IN ({$id_placeholders})",
            array_merge( [ self::TAXONOMY_NAME ], $term_ids )
        );

        $results = $wpdb->get_col( $sql );

        return array_map( 'absint', $results );
    }

}
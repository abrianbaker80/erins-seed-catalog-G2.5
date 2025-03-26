<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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

        // AJAX hook for searching/filtering seeds in the view
        add_action( 'wp_ajax_esc_filter_seeds', [ __CLASS__, 'handle_filter_seeds' ] );
		add_action( 'wp_ajax_nopriv_esc_filter_seeds', [ __CLASS__, 'handle_filter_seeds' ] );

        // AJAX hook for deleting seed (admin only)
        add_action( 'wp_ajax_esc_delete_seed', [ __CLASS__, 'handle_delete_seed' ] );
	}

	/**
	 * Handle the AJAX request for Gemini API search.
	 */
	public static function handle_gemini_search() {
		// 1. Verify Nonce
		check_ajax_referer( 'esc_ajax_nonce', 'nonce' );

		// 2. Get data from $_POST
		$seed_name = isset( $_POST['seed_name'] ) ? sanitize_text_field( wp_unslash( $_POST['seed_name'] ) ) : '';
		$variety   = isset( $_POST['variety'] ) ? sanitize_text_field( wp_unslash( $_POST['variety'] ) ) : null;
		$brand     = isset( $_POST['brand'] ) ? sanitize_text_field( wp_unslash( $_POST['brand'] ) ) : null;
		$sku_upc   = isset( $_POST['sku_upc'] ) ? sanitize_text_field( wp_unslash( $_POST['sku_upc'] ) ) : null;

		if ( empty( $seed_name ) ) {
			wp_send_json_error( [ 'message' => __( 'Seed Name is required for AI search.', 'erins-seed-catalog' ) ] );
			return;
		}

		// 3. Call the Gemini API Class
		$result = ESC_Gemini_API::fetch_seed_info( $seed_name, $variety, $brand, $sku_upc );

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

			wp_send_json_success( $result );
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

} // End Class
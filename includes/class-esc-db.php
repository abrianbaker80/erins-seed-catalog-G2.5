<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ESC_DB
 * Handles database interactions for the seed catalog.
 */
class ESC_DB {

	/**
	 * Get the custom table name with the WordPress prefix.
	 *
	 * @return string The full table name.
	 */
	private static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'esc_seeds';
	}

    /**
	 * Get the relationship table name.
	 *
	 * @return string The full relationship table name.
	 */
    private static function get_relationship_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'esc_seed_term_relationships';
    }

	/**
	 * Create the custom database table on plugin activation.
	 */
	public static function create_table() {
		global $wpdb;
		$table_name = self::get_table_name();
        $relationship_table_name = self::get_relationship_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			seed_name VARCHAR(255) NOT NULL,
			variety_name VARCHAR(255) NULL,
			brand VARCHAR(255) NULL,
			sku_upc VARCHAR(100) NULL,
			image_url VARCHAR(2083) NULL,
			description TEXT NULL,
			plant_type VARCHAR(100) NULL,
			growth_habit VARCHAR(100) NULL,
			plant_size VARCHAR(100) NULL,
			fruit_info VARCHAR(255) NULL,
			flavor_profile TEXT NULL,
			scent VARCHAR(255) NULL,
			bloom_time VARCHAR(100) NULL,
			days_to_maturity VARCHAR(50) NULL,
			special_characteristics TEXT NULL,
			sowing_depth VARCHAR(100) NULL,
			sowing_spacing VARCHAR(100) NULL,
			germination_temp VARCHAR(100) NULL,
			sunlight VARCHAR(100) NULL,
			watering TEXT NULL,
			fertilizer TEXT NULL,
			pest_disease_info TEXT NULL,
			harvesting_tips TEXT NULL,
			sowing_method VARCHAR(50) NULL,
			usda_zones VARCHAR(100) NULL,
			pollinator_info TEXT NULL,
			container_suitability TINYINT(1) DEFAULT 0 NULL,
			cut_flower_potential TINYINT(1) DEFAULT 0 NULL,
			storage_recommendations TEXT NULL,
			edible_parts VARCHAR(255) NULL,
			historical_background TEXT NULL,
			companion_plants TEXT NULL,
			regional_tips TEXT NULL,
			seed_saving_info TEXT NULL,
            notes TEXT NULL,
            purchase_date DATE NULL,
            expiration_date DATE NULL,
            quantity_on_hand INT NULL,
			date_added DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
			last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id),
            KEY seed_name (seed_name),
            KEY variety_name (variety_name)
		) $charset_collate;";

        $sql_relationship = "CREATE TABLE $relationship_table_name (
            seed_id BIGINT UNSIGNED NOT NULL,
            term_taxonomy_id BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY  (seed_id, term_taxonomy_id),
            KEY term_taxonomy_id (term_taxonomy_id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
        dbDelta( $sql_relationship );

        /* Foreign key constraints are commented out as they require InnoDB and dbDelta doesn't handle them well
        ALTER TABLE {$relationship_table_name} ADD CONSTRAINT fk_seed_id FOREIGN KEY (seed_id) REFERENCES {$table_name}(id) ON DELETE CASCADE;
        ALTER TABLE {$relationship_table_name} ADD CONSTRAINT fk_term_taxonomy_id FOREIGN KEY (term_taxonomy_id) REFERENCES {$wpdb->term_taxonomy}(term_taxonomy_id) ON DELETE CASCADE;
        */
	}

    /**
     * Drops the custom database table. USE WITH CAUTION.
     */
    public static function drop_table() {
        global $wpdb;
        $table_name = self::get_table_name();
        $relationship_table_name = self::get_relationship_table_name();
        $wpdb->query( "DROP TABLE IF EXISTS {$relationship_table_name}" );
        $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
    }

	/**
	 * Add a new seed entry to the database.
	 *
	 * @param array $data Seed data associative array.
     * @param array $category_ids Array of term_taxonomy_ids for categories.
	 * @return int|WP_Error The ID of the newly inserted seed or WP_Error on failure.
	 */
	public static function add_seed( array $data, array $category_ids = [] ) {
        global $wpdb;
        $table_name = self::get_table_name();

        // Define allowed fields and their formats/types for sanitization
        $allowed_fields = self::get_allowed_fields();
        $insert_data = [];
        $formats = [];

        // Debug log the incoming data
        error_log('ESC DB - Raw Insert Data: ' . print_r($data, true));

        // Specifically check for image_url
        if (isset($data['image_url'])) {
            error_log('ESC DB - Image URL found in data: ' . $data['image_url']);
            // Validate the URL format
            if (filter_var($data['image_url'], FILTER_VALIDATE_URL)) {
                error_log('ESC DB - Image URL is valid');
            } else {
                error_log('ESC DB - Image URL is not a valid URL: ' . $data['image_url']);
                // Try to fix the URL if it's not valid
                if (!empty($data['image_url']) && strpos($data['image_url'], 'http') !== 0) {
                    $data['image_url'] = 'http://' . $data['image_url'];
                    error_log('ESC DB - Fixed image URL: ' . $data['image_url']);
                }
            }
        } else {
            error_log('ESC DB - No image_url found in data');
            // Check if there's any field that might contain the image URL
            foreach ($data as $key => $value) {
                if ((strpos($key, 'image') !== false || strpos($key, 'url') !== false) && !empty($value)) {
                    error_log('ESC DB - Potential image URL field found: ' . $key . ' = ' . $value);
                    // If we find a field that looks like it contains an image URL, use it
                    if (!isset($data['image_url'])) {
                        error_log('ESC DB - Using alternative field for image URL: ' . $key);
                        $data['image_url'] = $value;
                        break;
                    }
                }
            }
        }

        foreach ($allowed_fields as $field => $type) {
            if (isset($data[$field])) {
                // Log before sanitization for image_url
                if ($field === 'image_url') {
                    error_log('ESC DB - Processing image_url before sanitization: ' . $data[$field]);
                }

                $insert_data[$field] = self::sanitize_field($data[$field], $type);
                $formats[] = self::get_format_placeholder($type);

                // Log after sanitization for image_url
                if ($field === 'image_url') {
                    error_log('ESC DB - Processing image_url after sanitization: ' . $insert_data[$field]);
                    // If sanitization removed the URL, try to fix it
                    if (empty($insert_data[$field]) && !empty($data[$field])) {
                        error_log('ESC DB - Sanitization removed image URL, attempting to fix');
                        // Try a different sanitization approach
                        $insert_data[$field] = filter_var($data[$field], FILTER_SANITIZE_URL);
                        error_log('ESC DB - Fixed image URL with alternative sanitization: ' . $insert_data[$field]);
                    }
                }
            }
        }

        // Debug log the sanitized data
        error_log('ESC DB - Sanitized Insert Data: ' . print_r($insert_data, true));

        // Ensure required fields are present
        if (empty($insert_data['seed_name'])) {
            return new WP_Error('missing_field', __('Seed Name is required.', 'erins-seed-catalog'));
        }

        // Add timestamps
        $insert_data['date_added'] = current_time('mysql', 1);
        $insert_data['last_updated'] = current_time('mysql', 1);
        $formats[] = '%s'; // date_added
        $formats[] = '%s'; // last_updated

        // Debug log the SQL before execution
        $query = $wpdb->prepare("INSERT INTO $table_name (" . implode(',', array_keys($insert_data)) . ") VALUES (" . implode(',', $formats) . ")", array_values($insert_data));
        error_log('ESC DB - Insert Query: ' . $query);

        // Debug log the image URL specifically
        if (isset($insert_data['image_url'])) {
            error_log('ESC DB - Image URL in insert data: ' . $insert_data['image_url']);
        } else {
            error_log('ESC DB - No image_url in insert data');
        }

        // Try to insert the data
        $inserted = $wpdb->insert($table_name, $insert_data, $formats);

        if (false === $inserted) {
            // Get detailed MySQL error information
            $db_error = $wpdb->last_error;
            $db_errno = $wpdb->last_errno;
            error_log('ESC DB - Insert Error: ' . $db_error);
            error_log('ESC DB - Error Number: ' . $db_errno);

            // Format a user-friendly error message
            $error_message = __('Could not insert seed data into the database.', 'erins-seed-catalog');

            // Add specific error details based on MySQL error number
            switch ($db_errno) {
                case 1146: // Table doesn't exist
                    $error_message = __('Database table is missing. Please deactivate and reactivate the plugin.', 'erins-seed-catalog');
                    break;
                case 1062: // Duplicate entry
                    $error_message = __('This seed entry appears to be a duplicate.', 'erins-seed-catalog');
                    break;
                case 1054: // Unknown column
                    $error_message = __('Database schema mismatch. Please deactivate and reactivate the plugin.', 'erins-seed-catalog');
                    break;
                default:
                    // Keep the generic message but add the specific error
                    $error_message .= ' ' . $db_error;
            }

            return new WP_Error('db_insert_error', $error_message, $db_error);
        }

        $seed_id = $wpdb->insert_id;
        error_log('ESC DB - Successfully inserted seed with ID: ' . $seed_id);

        // Handle category relationships
        if (!empty($category_ids)) {
            error_log('ESC DB - Adding categories for seed ID ' . $seed_id . ': ' . print_r($category_ids, true));
            self::update_seed_categories($seed_id, $category_ids);
        }

        return $seed_id;
    }

    /**
	 * Update an existing seed entry.
	 *
	 * @param int $seed_id The ID of the seed to update.
	 * @param array $data Associative array of data to update.
     * @param array $category_ids Array of term_taxonomy_ids for categories.
	 * @return bool|WP_Error True on success, false on failure, WP_Error on error.
	 */
    public static function update_seed( int $seed_id, array $data, array $category_ids = [] ) {
        global $wpdb;
        $table_name = self::get_table_name();

        if ( $seed_id <= 0 ) {
            return new WP_Error( 'invalid_id', __( 'Invalid Seed ID provided for update.', 'erins-seed-catalog' ) );
        }

        $allowed_fields = self::get_allowed_fields();
        $update_data = [];
        $formats = [];

        foreach ($allowed_fields as $field => $type) {
            // Only include fields that are actually present in the $data array
            if ( array_key_exists($field, $data) ) {
                 // Allow explicit null/empty values to clear fields
                $update_data[$field] = self::sanitize_field($data[$field], $type);
                $formats[] = self::get_format_placeholder($type);
            }
        }

        // Always update the last_updated timestamp
        $update_data['last_updated'] = current_time('mysql', 1);
        $formats[] = '%s';


        if ( empty($update_data) ) {
             // Update categories even if no other data changed
            self::update_seed_categories($seed_id, $category_ids);
            return true; // Nothing else to update
        }

        $where = [ 'id' => $seed_id ];
        $where_formats = [ '%d' ];

        $updated = $wpdb->update( $table_name, $update_data, $where, $formats, $where_formats );

        if ( false === $updated ) {
            return new WP_Error( 'db_update_error', __( 'Could not update seed data in the database.', 'erins-seed-catalog' ), $wpdb->last_error );
        }

        // Update category relationships
        self::update_seed_categories($seed_id, $category_ids);


        // $updated returns number of rows affected. 0 means no change or no matching row.
        // Return true if no error, even if 0 rows affected (data might be identical).
        return true;
    }

    /**
	 * Delete a seed entry.
	 *
	 * @param int $seed_id The ID of the seed to delete.
	 * @return bool True on success, false on failure.
	 */
	public static function delete_seed( int $seed_id ) {
		global $wpdb;
		$table_name = self::get_table_name();
        $relationship_table_name = self::get_relationship_table_name();

		if ( $seed_id <= 0 ) {
			return false;
		}

        // Delete relationships first
        $wpdb->delete( $relationship_table_name, [ 'seed_id' => $seed_id ], [ '%d' ] );

        // Delete the seed entry
		$deleted = $wpdb->delete( $table_name, [ 'id' => $seed_id ], [ '%d' ] );

		return (bool) $deleted;
	}

    /**
	 * Retrieve a single seed by its ID.
	 *
	 * @param int $seed_id The ID of the seed.
	 * @return object|null Seed data object or null if not found.
	 */
	public static function get_seed_by_id( int $seed_id ) {
		global $wpdb;
		$table_name = self::get_table_name();

		if ( $seed_id <= 0 ) {
			return null;
		}

		$sql = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $seed_id );
		$seed = $wpdb->get_row( $sql );

        if ($seed) {
            // Fetch categories for this seed
            $seed->categories = self::get_seed_categories($seed_id);
        }

		return $seed;
	}

    /**
     * Retrieve seeds based on arguments.
     *
     * @param array $args {
     *     Optional. Arguments to filter seeds.
     *
     *     @type int    $limit      Number of seeds to retrieve. Default -1 (all).
     *     @type int    $offset     Number of seeds to skip. Default 0.
     *     @type string $orderby    Column to order by. Default 'seed_name'.
     *     @type string $order      Order direction (ASC or DESC). Default 'ASC'.
     *     @type string $search     Search term for name or variety.
     *     @type int    $category   Term ID to filter by category.
     *     @type array  $ids        Array of specific seed IDs to fetch.
     * }
     * @return array Array of seed objects.
     */
    public static function get_seeds( array $args = [] ) : array {
        global $wpdb;
        $table_name = self::get_table_name();
        $rel_table = self::get_relationship_table_name();
        $term_taxonomy_table = $wpdb->term_taxonomy;

        // Default arguments
        $defaults = [
            'limit'      => -1,
            'offset'     => 0,
            'orderby'    => 'seed_name',
            'order'      => 'ASC',
            'search'     => '',
            'category'   => 0, // term_id
            'ids'        => [],
        ];
        $args = wp_parse_args( $args, $defaults );

        // Validate orderby - only allow valid column names
        $allowed_orderby = array_keys( self::get_allowed_fields() );
        $allowed_orderby[] = 'id';
        $allowed_orderby[] = 'date_added';
        $allowed_orderby[] = 'last_updated';
        $orderby = in_array( strtolower( $args['orderby'] ), $allowed_orderby, true ) ? $args['orderby'] : 'seed_name';

        // Validate order
        $order = strtoupper( $args['order'] ) === 'DESC' ? 'DESC' : 'ASC';

        $where_clauses = [];
        $params = [];
        $joins = '';

        // Search query
        if ( ! empty( $args['search'] ) ) {
            $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where_clauses[] = "( s.seed_name LIKE %s OR s.variety_name LIKE %s OR s.brand LIKE %s OR s.description LIKE %s )";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }

        // Category filter
        if ( ! empty( $args['category'] ) && is_numeric( $args['category'] ) ) {
            $term_id = absint( $args['category'] );
            // Need term_taxonomy_id
            $term_taxonomy_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT tt.term_taxonomy_id FROM {$term_taxonomy_table} tt WHERE tt.term_id = %d AND tt.taxonomy = %s",
                $term_id,
                ESC_Taxonomy::TAXONOMY_NAME
            ) );

            if ($term_taxonomy_id) {
                $joins .= " INNER JOIN {$rel_table} rel ON s.id = rel.seed_id";
                $where_clauses[] = "rel.term_taxonomy_id = %d";
                $params[] = $term_taxonomy_id;
            }
        }

        // Filter by specific IDs
        if ( ! empty( $args['ids'] ) && is_array( $args['ids'] ) ) {
            $ids = array_map( 'absint', $args['ids'] );
            $ids = array_filter( $ids ); // Remove zeros/invalids
            if ( ! empty( $ids ) ) {
                $id_placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
                $where_clauses[] = "s.id IN ( $id_placeholders )";
                $params = array_merge( $params, $ids );
            }
        }

        $where_sql = '';
        if ( ! empty( $where_clauses ) ) {
            $where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );
        }

        $limit_sql = '';
        if ( $args['limit'] > 0 ) {
            $limit_sql = $wpdb->prepare( 'LIMIT %d OFFSET %d', absint( $args['limit'] ), absint( $args['offset'] ) );
        }

        $sql = "SELECT DISTINCT s.* FROM {$table_name} s {$joins} {$where_sql} ORDER BY s.{$orderby} {$order} {$limit_sql}";

        if ( ! empty( $params ) ) {
            $results = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
        } else {
            $results = $wpdb->get_results( $sql );
        }

         // Fetch categories for each result efficiently
        if ( ! empty( $results ) ) {
            $seed_ids = wp_list_pluck( $results, 'id' );
            $categories_by_seed_id = self::get_categories_for_seeds( $seed_ids );
            foreach ( $results as $seed ) {
                $seed->categories = $categories_by_seed_id[ $seed->id ] ?? [];
            }
        }

        return $results ?: [];
    }

     /**
     * Count total number of seeds matching criteria (useful for pagination).
     *
     * @param array $args Arguments similar to get_seeds (search, category).
     * @return int Total count.
     */
    public static function count_seeds( array $args = [] ) : int {
        global $wpdb;
        $table_name = self::get_table_name();
        $rel_table = self::get_relationship_table_name();
        $term_taxonomy_table = $wpdb->term_taxonomy;

        // Arguments affecting count
        $defaults = [
            'search'   => '',
            'category' => 0, // term_id
        ];
        $args = wp_parse_args( $args, $defaults );

        $where_clauses = [];
        $params = [];
        $joins = '';

        // Search query
        if ( ! empty( $args['search'] ) ) {
            $search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $where_clauses[] = "( s.seed_name LIKE %s OR s.variety_name LIKE %s OR s.brand LIKE %s OR s.description LIKE %s )";
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }

        // Category filter
        if ( ! empty( $args['category'] ) && is_numeric( $args['category'] ) ) {
             $term_id = absint( $args['category'] );
            // Need term_taxonomy_id
            $term_taxonomy_id = $wpdb->get_var( $wpdb->prepare(
                "SELECT tt.term_taxonomy_id FROM {$term_taxonomy_table} tt WHERE tt.term_id = %d AND tt.taxonomy = %s",
                $term_id,
                ESC_Taxonomy::TAXONOMY_NAME
            ) );

            if ($term_taxonomy_id) {
                // Use LEFT JOIN for count to avoid issues if seed has no category
                // Update: INNER JOIN is correct if we only want to count seeds *within* that category
                $joins .= " INNER JOIN {$rel_table} rel ON s.id = rel.seed_id";
                $where_clauses[] = "rel.term_taxonomy_id = %d";
                $params[] = $term_taxonomy_id;
            }
        }

        $where_sql = '';
        if ( ! empty( $where_clauses ) ) {
            $where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );
        }

        // Use COUNT(DISTINCT s.id) if using joins that might create duplicates
        $count_col = !empty($joins) ? 'COUNT(DISTINCT s.id)' : 'COUNT(s.id)';
        $sql = "SELECT {$count_col} FROM {$table_name} s {$joins} {$where_sql}";

        if ( ! empty( $params ) ) {
            $count = $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
        } else {
            $count = $wpdb->get_var( $sql );
        }

        return absint( $count );
    }


    /**
     * Updates the category relationships for a given seed.
     * Deletes existing relationships and adds new ones.
     *
     * @param int $seed_id The seed ID.
     * @param array $term_taxonomy_ids Array of term taxonomy IDs to associate.
     */
    public static function update_seed_categories( int $seed_id, array $term_taxonomy_ids ) {
        global $wpdb;
        $rel_table = self::get_relationship_table_name();

        if ( $seed_id <= 0 ) {
            return;
        }

        // Sanitize input IDs
        $term_taxonomy_ids = array_map( 'absint', $term_taxonomy_ids );
        $term_taxonomy_ids = array_filter( $term_taxonomy_ids ); // Remove zeros

        // Delete existing relationships for this seed
        $wpdb->delete( $rel_table, [ 'seed_id' => $seed_id ], [ '%d' ] );

        // Add new relationships
        if ( ! empty( $term_taxonomy_ids ) ) {
            $values = [];
            $formats = [];
            foreach ( $term_taxonomy_ids as $tt_id ) {
                // Basic validation: Check if term_taxonomy_id exists? Optional for performance.
                // $exists = $wpdb->get_var($wpdb->prepare("SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_taxonomy_id = %d", $tt_id));
                // if ($exists) {
                    $values[] = $wpdb->prepare( '(%d, %d)', $seed_id, $tt_id );
                // }
            }

            if ( ! empty( $values ) ) {
                $query = "INSERT INTO {$rel_table} (seed_id, term_taxonomy_id) VALUES " . implode( ',', $values );
                $wpdb->query( $query );
            }
        }
    }

    /**
     * Get category term objects associated with a single seed.
     *
     * @param int $seed_id The seed ID.
     * @return array Array of WP_Term objects.
     */
    public static function get_seed_categories( int $seed_id ) : array {
        global $wpdb;
        $rel_table = self::get_relationship_table_name();

        if ( $seed_id <= 0 ) {
            return [];
        }

        $term_taxonomy_ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT term_taxonomy_id FROM {$rel_table} WHERE seed_id = %d",
            $seed_id
        ) );

        if ( empty( $term_taxonomy_ids ) ) {
            return [];
        }

        // Get term objects using WordPress functions
        $terms = get_terms( [
            'taxonomy' => ESC_Taxonomy::TAXONOMY_NAME,
            'term_taxonomy_id' => $term_taxonomy_ids,
            'hide_empty' => false, // Show terms even if no posts are associated (relevant for custom table)
        ] );

        return is_wp_error($terms) ? [] : $terms;
    }


    /**
     * Efficiently get categories for multiple seeds.
     *
     * @param array $seed_ids Array of seed IDs.
     * @return array Associative array where keys are seed IDs and values are arrays of WP_Term objects.
     */
    private static function get_categories_for_seeds( array $seed_ids ) : array {
        global $wpdb;
        $rel_table = self::get_relationship_table_name();
        $results = [];

        if ( empty( $seed_ids ) ) {
            return $results;
        }

        $seed_ids = array_map( 'absint', $seed_ids );
        $id_placeholders = implode( ',', array_fill( 0, count( $seed_ids ), '%d' ) );

        $relationships = $wpdb->get_results( $wpdb->prepare(
            "SELECT seed_id, term_taxonomy_id FROM {$rel_table} WHERE seed_id IN ({$id_placeholders})",
            $seed_ids
        ) );

        if ( empty( $relationships ) ) {
            // Initialize results with empty arrays for all requested seed IDs
            foreach ( $seed_ids as $id ) {
                $results[$id] = [];
            }
            return $results;
        }

        // Group term_taxonomy_ids by seed_id
        $tt_ids_by_seed = [];
        $all_tt_ids = [];
        foreach ( $relationships as $rel ) {
            if ( ! isset( $tt_ids_by_seed[ $rel->seed_id ] ) ) {
                $tt_ids_by_seed[ $rel->seed_id ] = [];
            }
            $tt_ids_by_seed[ $rel->seed_id ][] = $rel->term_taxonomy_id;
            $all_tt_ids[] = $rel->term_taxonomy_id;
        }
        $all_tt_ids = array_unique( $all_tt_ids );

        // Fetch all relevant term objects in one go
        $terms = get_terms( [
            'taxonomy' => ESC_Taxonomy::TAXONOMY_NAME,
            'term_taxonomy_id' => $all_tt_ids,
            'hide_empty' => false,
        ] );

        // Map terms back to their term_taxonomy_id for easy lookup
        $terms_by_tt_id = [];
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $terms_by_tt_id[ $term->term_taxonomy_id ] = $term;
            }
        }

        // Build the final result array
        foreach ( $seed_ids as $id ) {
            $results[$id] = [];
            if ( isset( $tt_ids_by_seed[$id] ) ) {
                foreach ( $tt_ids_by_seed[$id] as $tt_id ) {
                    if ( isset( $terms_by_tt_id[$tt_id] ) ) {
                        $results[$id][] = $terms_by_tt_id[$tt_id];
                    }
                }
            }
        }

        return $results;
    }


    /**
     * Define allowed fields and their types for sanitization/formatting.
     *
     * @return array Field names as keys, types as values (string, text, int, bool, url, date).
     */
    public static function get_allowed_fields() : array {
        return [
            'seed_name'               => 'string',
			'variety_name'            => 'string',
			'brand'                   => 'string',
			'sku_upc'                 => 'string',
			'image_url'               => 'url',
			'description'             => 'text',
			'plant_type'              => 'string',
			'growth_habit'            => 'string',
			'plant_size'              => 'string',
			'fruit_info'              => 'string',
			'flavor_profile'          => 'text',
			'scent'                   => 'string',
			'bloom_time'              => 'string',
			'days_to_maturity'        => 'string', // Can contain ranges like "60-70"
			'special_characteristics' => 'text',
			'sowing_depth'            => 'string',
			'sowing_spacing'          => 'string',
			'germination_temp'        => 'string',
			'sunlight'                => 'string',
			'watering'                => 'text',
			'fertilizer'              => 'text',
			'pest_disease_info'       => 'text',
			'harvesting_tips'         => 'text',
			'sowing_method'           => 'string',
			'usda_zones'              => 'string',
			'pollinator_info'         => 'text',
			'container_suitability'   => 'bool',
			'cut_flower_potential'    => 'bool',
			'storage_recommendations' => 'text',
			'edible_parts'            => 'string',
			'historical_background'   => 'text',
			'companion_plants'        => 'text',
			'regional_tips'           => 'text',
			'seed_saving_info'        => 'text',
            'notes'                   => 'text',
            'purchase_date'           => 'date',
            'expiration_date'         => 'date',
            'quantity_on_hand'        => 'int',
            // Timestamps are handled separately
        ];
    }

    /**
     * Sanitize field data based on type.
     *
     * @param mixed $value The value to sanitize.
     * @param string $type The type of field (string, text, int, bool, url, date).
     * @return mixed Sanitized value.
     */
    private static function sanitize_field( $value, string $type ) {
        switch ( $type ) {
            case 'string':
                return sanitize_text_field( $value );
            case 'text':
                 // Allow basic HTML potentially? Use wp_kses_post for more flexibility
                return sanitize_textarea_field( $value );
            case 'int':
                return absint( $value );
            case 'bool':
                return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
            case 'url':
                // Don't validate URLs with filter_var as it's too strict
                // Just make sure it's properly sanitized
                if ($value) {
                    error_log('ESC DB - Sanitizing URL field: ' . $value);

                    // First try standard sanitization
                    $sanitized = esc_url_raw( $value );
                    error_log('ESC DB - Sanitized URL result: ' . $sanitized);

                    // If sanitization removed the URL, use a more lenient approach
                    if (empty($sanitized) || $sanitized !== $value) {
                        error_log('ESC DB - Standard sanitization changed or removed the URL, using more lenient approach');
                        // Just do basic sanitization to remove potentially harmful characters
                        $sanitized = filter_var($value, FILTER_SANITIZE_URL);
                        error_log('ESC DB - Lenient sanitization result: ' . $sanitized);
                    }

                    // If the URL doesn't start with http:// or https://, add http://
                    if (!empty($sanitized) && strpos($sanitized, 'http') !== 0) {
                        $sanitized = 'http://' . $sanitized;
                        error_log('ESC DB - Added http:// prefix: ' . $sanitized);
                    }

                    return $sanitized;
                }
                return '';
            case 'date':
                 // Basic validation - check if it looks like a date YYYY-MM-DD
                if ( preg_match( "/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $value ) ) {
                    return sanitize_text_field( $value );
                }
                return null; // Return null if invalid date format
            default:
                return sanitize_text_field( $value );
        }
    }

    /**
     * Get the $wpdb->prepare placeholder format based on type.
     *
     * @param string $type Field type.
     * @return string Placeholder ('%s', '%d', '%f').
     */
    private static function get_format_placeholder( string $type ) : string {
        switch ( $type ) {
            case 'int':
            case 'bool': // Booleans are often stored as 0/1 (int)
                return '%d';
            case 'float': // Not used currently, but for future
                return '%f';
            case 'string':
            case 'text':
            case 'url':
            case 'date':
            default:
                return '%s';
        }
    }
}

<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ESC_Image_URL_Test_Handler
 * Handles AJAX requests for testing image URLs.
 */
class ESC_Image_URL_Test_Handler {

    /**
     * Initialize AJAX handlers.
     */
    public static function init() {
        // Register AJAX handlers
        add_action( 'wp_ajax_esc_test_image_urls', [ __CLASS__, 'handle_test_image_urls' ] );
        add_action( 'wp_ajax_nopriv_esc_test_image_urls', [ __CLASS__, 'handle_test_image_urls' ] );
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

// Initialize the class
ESC_Image_URL_Test_Handler::init();

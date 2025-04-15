<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Make sure we don't have any syntax errors

/**
 * Class ESC_Image_Fixer
 * Handles fixing image URLs in the database.
 */
class ESC_Image_Fixer {

    /**
     * Initialize the image fixer.
     */
    public static function init() {
        // Register AJAX handlers
        add_action( 'wp_ajax_esc_fix_image_urls', [ __CLASS__, 'handle_fix_image_urls' ] );
        add_action( 'wp_ajax_nopriv_esc_fix_image_urls', [ __CLASS__, 'handle_unauthorized' ] );

        // Add admin menu item
        add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ], 25 );
    }

    /**
     * Add admin menu item.
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'erins-seed-catalog',
            __( 'Fix Image URLs', 'erins-seed-catalog' ),
            __( 'Fix Image URLs', 'erins-seed-catalog' ),
            'manage_options',
            'esc-fix-image-urls',
            [ __CLASS__, 'render_admin_page' ]
        );
    }

    /**
     * Render admin page.
     */
    public static function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Fix Seed Image URLs', 'erins-seed-catalog' ); ?></h1>

            <p><?php esc_html_e( 'This tool fixes image URLs in the database to ensure they work correctly with your WordPress installation.', 'erins-seed-catalog' ); ?></p>

            <div class="esc-image-fixer-controls">
                <button id="esc-fix-all-images" class="button button-primary"><?php esc_html_e( 'Fix All Image URLs', 'erins-seed-catalog' ); ?></button>
            </div>

            <div id="esc-image-fixer-results" style="margin-top: 20px;">
                <div class="esc-image-fixer-progress" style="display: none; margin-bottom: 20px;">
                    <div class="esc-progress-bar" style="height: 20px; background-color: #f0f0f0; border-radius: 3px; overflow: hidden;">
                        <div class="esc-progress-bar-inner" style="height: 100%; width: 0%; background-color: #0073aa; transition: width 0.3s;"></div>
                    </div>
                    <p class="esc-progress-text"><?php esc_html_e( 'Fixing image URLs...', 'erins-seed-catalog' ); ?> <span class="esc-progress-percent">0%</span></p>
                </div>

                <div class="esc-image-fixer-stats" style="display: none; margin-bottom: 20px; padding: 15px; background: #f8f8f8; border: 1px solid #ddd;">
                    <h3><?php esc_html_e( 'Results', 'erins-seed-catalog' ); ?></h3>
                    <p><strong><?php esc_html_e( 'Total Seeds:', 'erins-seed-catalog' ); ?></strong> <span id="esc-total-seeds">0</span></p>
                    <p><strong><?php esc_html_e( 'Seeds with Images:', 'erins-seed-catalog' ); ?></strong> <span id="esc-seeds-with-images">0</span></p>
                    <p><strong><?php esc_html_e( 'URLs Fixed:', 'erins-seed-catalog' ); ?></strong> <span id="esc-urls-fixed">0</span></p>
                </div>

                <table class="widefat esc-image-fixer-table" style="display: none;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'ID', 'erins-seed-catalog' ); ?></th>
                            <th><?php esc_html_e( 'Seed Name', 'erins-seed-catalog' ); ?></th>
                            <th><?php esc_html_e( 'Original URL', 'erins-seed-catalog' ); ?></th>
                            <th><?php esc_html_e( 'Fixed URL', 'erins-seed-catalog' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="esc-image-fixer-table-body">
                        <!-- Results will be added here -->
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            const $fixButton = $('#esc-fix-all-images');
            const $progress = $('.esc-image-fixer-progress');
            const $progressBar = $('.esc-progress-bar-inner');
            const $progressText = $('.esc-progress-text');
            const $progressPercent = $('.esc-progress-percent');
            const $stats = $('.esc-image-fixer-stats');
            const $table = $('.esc-image-fixer-table');
            const $tableBody = $('#esc-image-fixer-table-body');

            let totalSeeds = 0;
            let seedsWithImages = 0;
            let urlsFixed = 0;

            $fixButton.on('click', function() {
                // Reset stats
                totalSeeds = 0;
                seedsWithImages = 0;
                urlsFixed = 0;

                // Clear table
                $tableBody.empty();

                // Show progress
                $progress.show();
                $progressBar.css('width', '0%');
                $progressPercent.text('0%');

                // Hide stats and table
                $stats.hide();
                $table.hide();

                // Disable button
                $fixButton.prop('disabled', true).text('Fixing...');

                // Start fixing
                fixImageUrls();
            });

            function fixImageUrls() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'esc_fix_image_urls',
                        nonce: '<?php echo wp_create_nonce('esc_fix_image_urls_nonce'); ?>'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Update stats
                            totalSeeds = response.data.total_seeds;
                            seedsWithImages = response.data.seeds_with_images;
                            urlsFixed = response.data.urls_fixed;

                            // Update progress
                            $progressBar.css('width', '100%');
                            $progressPercent.text('100%');

                            // Update stats display
                            $('#esc-total-seeds').text(totalSeeds);
                            $('#esc-seeds-with-images').text(seedsWithImages);
                            $('#esc-urls-fixed').text(urlsFixed);

                            // Show stats
                            $stats.show();

                            // Add results to table
                            if (response.data.results.length > 0) {
                                response.data.results.forEach(function(result) {
                                    const $row = $('<tr></tr>');
                                    $row.append('<td>' + result.id + '</td>');
                                    $row.append('<td>' + result.name + '</td>');
                                    $row.append('<td>' + result.original_url + '</td>');
                                    $row.append('<td>' + result.fixed_url + '</td>');
                                    $tableBody.append($row);
                                });

                                // Show table
                                $table.show();
                            }

                            // Enable button
                            $fixButton.prop('disabled', false).text('Fix All Image URLs');

                            // Hide progress
                            $progress.hide();
                        } else {
                            alert('Error: ' + response.data.message);
                            $fixButton.prop('disabled', false).text('Fix All Image URLs');
                            $progress.hide();
                        }
                    },
                    error: function() {
                        alert('Error communicating with the server.');
                        $fixButton.prop('disabled', false).text('Fix All Image URLs');
                        $progress.hide();
                    }
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Handle unauthorized access.
     */
    public static function handle_unauthorized() {
        wp_send_json_error( [
            'message' => __( 'You must be logged in to fix image URLs.', 'erins-seed-catalog' ),
        ] );
    }

    /**
     * Handle AJAX request to fix image URLs.
     */
    public static function handle_fix_image_urls() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'esc_fix_image_urls_nonce' ) ) {
            wp_send_json_error( [
                'message' => __( 'Security check failed.', 'erins-seed-catalog' ),
            ] );
        }

        // Check if user can manage options
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to fix image URLs.', 'erins-seed-catalog' ),
            ] );
        }

        // Get all seeds with images
        global $wpdb;
        $table_name = $wpdb->prefix . 'esc_seeds';

        $seeds = $wpdb->get_results(
            "SELECT id, seed_name, variety_name, image_url FROM {$table_name}
             WHERE image_url IS NOT NULL AND image_url != ''"
        );

        $total_seeds = count( $wpdb->get_results( "SELECT id FROM {$table_name}" ) );
        $seeds_with_images = count( $seeds );
        $urls_fixed = 0;
        $results = [];

        foreach ( $seeds as $seed ) {
            $original_url = $seed->image_url;
            $fixed_url = self::fix_image_url( $original_url );

            if ( $fixed_url !== $original_url ) {
                // Update the URL in the database
                $wpdb->update(
                    $table_name,
                    [ 'image_url' => $fixed_url ],
                    [ 'id' => $seed->id ],
                    [ '%s' ],
                    [ '%d' ]
                );

                $urls_fixed++;

                // Add to results
                $results[] = [
                    'id' => $seed->id,
                    'name' => $seed->seed_name . ( ! empty( $seed->variety_name ) ? ' - ' . $seed->variety_name : '' ),
                    'original_url' => $original_url,
                    'fixed_url' => $fixed_url,
                ];
            }
        }

        wp_send_json_success( [
            'total_seeds' => $total_seeds,
            'seeds_with_images' => $seeds_with_images,
            'urls_fixed' => $urls_fixed,
            'results' => $results,
        ] );
    }

    /**
     * Fix an image URL.
     *
     * @param string $url The image URL to fix.
     * @return string The fixed URL.
     */
    public static function fix_image_url( $url ) {
        // If it's a WordPress media library URL but missing the site URL, add the local network URL
        if ( strpos( $url, '/wp-content/uploads/' ) === 0 ) {
            return 'http://192.168.1.128' . $url;
        }

        // If it's a WordPress media library URL with a different domain, replace it with the local network URL
        if ( strpos( $url, '/wp-content/uploads/' ) !== false && strpos( $url, '192.168.1.128' ) === false ) {
            $path = substr( $url, strpos( $url, '/wp-content/uploads/' ) );
            return 'http://192.168.1.128' . $path;
        }

        // If it's a protocol-relative URL, add http:
        if ( strpos( $url, '//' ) === 0 ) {
            return 'http:' . $url;
        }

        // If it doesn't have a protocol, add http://
        if ( strpos( $url, 'http' ) !== 0 ) {
            return 'http://' . ltrim( $url, '/' );
        }

        return $url;
    }
}

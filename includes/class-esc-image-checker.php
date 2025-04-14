<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ESC_Image_Checker
 * Handles image validation and checking for the seed catalog.
 */
class ESC_Image_Checker {

    /**
     * Initialize the image checker.
     */
    public static function init() {
        // Register AJAX handlers
        add_action( 'wp_ajax_esc_check_images', [ __CLASS__, 'handle_check_images' ] );
        add_action( 'wp_ajax_nopriv_esc_check_images', [ __CLASS__, 'handle_unauthorized' ] );
        
        // Add admin menu item
        add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ], 20 );
    }
    
    /**
     * Add admin menu item.
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'erins-seed-catalog',
            __( 'Image Checker', 'erins-seed-catalog' ),
            __( 'Image Checker', 'erins-seed-catalog' ),
            'manage_options',
            'esc-image-checker',
            [ __CLASS__, 'render_admin_page' ]
        );
    }
    
    /**
     * Render admin page.
     */
    public static function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Seed Catalog Image Checker', 'erins-seed-catalog' ); ?></h1>
            
            <p><?php esc_html_e( 'This tool checks all seed images to ensure they are valid and accessible.', 'erins-seed-catalog' ); ?></p>
            
            <div class="esc-image-checker-controls">
                <button id="esc-check-images" class="button button-primary"><?php esc_html_e( 'Check All Images', 'erins-seed-catalog' ); ?></button>
                <button id="esc-fix-images" class="button" style="display: none;"><?php esc_html_e( 'Fix Image URLs', 'erins-seed-catalog' ); ?></button>
            </div>
            
            <div id="esc-image-checker-results" style="margin-top: 20px;">
                <div class="esc-image-checker-stats" style="display: none; margin-bottom: 20px; padding: 15px; background: #f8f8f8; border: 1px solid #ddd;">
                    <h3><?php esc_html_e( 'Results', 'erins-seed-catalog' ); ?></h3>
                    <p><strong><?php esc_html_e( 'Total Seeds:', 'erins-seed-catalog' ); ?></strong> <span id="esc-total-seeds">0</span></p>
                    <p><strong><?php esc_html_e( 'Seeds with Images:', 'erins-seed-catalog' ); ?></strong> <span id="esc-seeds-with-images">0</span></p>
                    <p><strong><?php esc_html_e( 'Valid Images:', 'erins-seed-catalog' ); ?></strong> <span id="esc-valid-images">0</span></p>
                    <p><strong><?php esc_html_e( 'Invalid Images:', 'erins-seed-catalog' ); ?></strong> <span id="esc-invalid-images">0</span></p>
                    <p><strong><?php esc_html_e( 'WordPress Media Images:', 'erins-seed-catalog' ); ?></strong> <span id="esc-wp-media-images">0</span></p>
                    <p><strong><?php esc_html_e( 'External Images:', 'erins-seed-catalog' ); ?></strong> <span id="esc-external-images">0</span></p>
                </div>
                
                <div class="esc-image-checker-progress" style="display: none; margin-bottom: 20px;">
                    <div class="esc-progress-bar" style="height: 20px; background-color: #f0f0f0; border-radius: 3px; overflow: hidden;">
                        <div class="esc-progress-bar-inner" style="height: 100%; width: 0%; background-color: #0073aa; transition: width 0.3s;"></div>
                    </div>
                    <p class="esc-progress-text"><?php esc_html_e( 'Checking images...', 'erins-seed-catalog' ); ?> <span class="esc-progress-percent">0%</span></p>
                </div>
                
                <table class="widefat esc-image-checker-table" style="display: none;">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'ID', 'erins-seed-catalog' ); ?></th>
                            <th><?php esc_html_e( 'Seed Name', 'erins-seed-catalog' ); ?></th>
                            <th><?php esc_html_e( 'Image URL', 'erins-seed-catalog' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'erins-seed-catalog' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'erins-seed-catalog' ); ?></th>
                        </tr>
                    </thead>
                    <tbody id="esc-image-checker-table-body">
                        <!-- Results will be added here -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            const $checkButton = $('#esc-check-images');
            const $fixButton = $('#esc-fix-images');
            const $progress = $('.esc-image-checker-progress');
            const $progressBar = $('.esc-progress-bar-inner');
            const $progressText = $('.esc-progress-text');
            const $progressPercent = $('.esc-progress-percent');
            const $stats = $('.esc-image-checker-stats');
            const $table = $('.esc-image-checker-table');
            const $tableBody = $('#esc-image-checker-table-body');
            
            let totalSeeds = 0;
            let seedsWithImages = 0;
            let validImages = 0;
            let invalidImages = 0;
            let wpMediaImages = 0;
            let externalImages = 0;
            let seedsChecked = 0;
            let seedsToCheck = [];
            let invalidSeeds = [];
            
            $checkButton.on('click', function() {
                // Reset stats
                totalSeeds = 0;
                seedsWithImages = 0;
                validImages = 0;
                invalidImages = 0;
                wpMediaImages = 0;
                externalImages = 0;
                seedsChecked = 0;
                seedsToCheck = [];
                invalidSeeds = [];
                
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
                $checkButton.prop('disabled', true).text('Checking...');
                
                // Get all seeds
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'esc_check_images',
                        nonce: '<?php echo wp_create_nonce('esc_check_images_nonce'); ?>',
                        command: 'get_seeds'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            totalSeeds = response.data.total;
                            seedsToCheck = response.data.seeds;
                            
                            // Update stats
                            $('#esc-total-seeds').text(totalSeeds);
                            
                            // Start checking images
                            checkNextBatch();
                        } else {
                            alert('Error: ' + response.data.message);
                            $checkButton.prop('disabled', false).text('Check All Images');
                            $progress.hide();
                        }
                    },
                    error: function() {
                        alert('Error communicating with the server.');
                        $checkButton.prop('disabled', false).text('Check All Images');
                        $progress.hide();
                    }
                });
            });
            
            function checkNextBatch() {
                // Get next 10 seeds
                const batch = seedsToCheck.splice(0, 10);
                
                if (batch.length === 0) {
                    // All seeds checked
                    finishCheck();
                    return;
                }
                
                // Check this batch
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'esc_check_images',
                        nonce: '<?php echo wp_create_nonce('esc_check_images_nonce'); ?>',
                        command: 'check_images',
                        seeds: batch
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Process results
                            const results = response.data.results;
                            
                            results.forEach(function(result) {
                                seedsChecked++;
                                
                                if (result.has_image) {
                                    seedsWithImages++;
                                    
                                    if (result.is_valid) {
                                        validImages++;
                                    } else {
                                        invalidImages++;
                                        invalidSeeds.push(result);
                                    }
                                    
                                    if (result.is_wp_media) {
                                        wpMediaImages++;
                                    } else {
                                        externalImages++;
                                    }
                                }
                                
                                // Add to table
                                if (result.has_image) {
                                    const $row = $('<tr></tr>');
                                    $row.append('<td>' + result.id + '</td>');
                                    $row.append('<td>' + result.name + '</td>');
                                    $row.append('<td><a href="' + result.image_url + '" target="_blank">' + result.image_url + '</a></td>');
                                    
                                    if (result.is_valid) {
                                        $row.append('<td><span style="color: green;">Valid</span></td>');
                                        $row.append('<td>-</td>');
                                    } else {
                                        $row.append('<td><span style="color: red;">Invalid</span></td>');
                                        $row.append('<td><button class="button button-small esc-fix-image" data-id="' + result.id + '">Fix</button></td>');
                                    }
                                    
                                    $tableBody.append($row);
                                }
                            });
                            
                            // Update progress
                            const progress = Math.round((seedsChecked / totalSeeds) * 100);
                            $progressBar.css('width', progress + '%');
                            $progressPercent.text(progress + '%');
                            
                            // Update stats
                            $('#esc-seeds-with-images').text(seedsWithImages);
                            $('#esc-valid-images').text(validImages);
                            $('#esc-invalid-images').text(invalidImages);
                            $('#esc-wp-media-images').text(wpMediaImages);
                            $('#esc-external-images').text(externalImages);
                            
                            // Check next batch
                            checkNextBatch();
                        } else {
                            alert('Error: ' + response.data.message);
                            $checkButton.prop('disabled', false).text('Check All Images');
                            $progress.hide();
                        }
                    },
                    error: function() {
                        alert('Error communicating with the server.');
                        $checkButton.prop('disabled', false).text('Check All Images');
                        $progress.hide();
                    }
                });
            }
            
            function finishCheck() {
                // Show stats and table
                $stats.show();
                $table.show();
                
                // Hide progress
                $progress.hide();
                
                // Enable button
                $checkButton.prop('disabled', false).text('Check All Images');
                
                // Show fix button if there are invalid images
                if (invalidImages > 0) {
                    $fixButton.show();
                } else {
                    $fixButton.hide();
                }
            }
            
            // Handle fix button
            $fixButton.on('click', function() {
                if (invalidSeeds.length === 0) {
                    alert('No invalid images to fix.');
                    return;
                }
                
                if (!confirm('This will attempt to fix ' + invalidSeeds.length + ' invalid image URLs. Continue?')) {
                    return;
                }
                
                // Disable buttons
                $checkButton.prop('disabled', true);
                $fixButton.prop('disabled', true).text('Fixing...');
                
                // Fix invalid images
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'esc_check_images',
                        nonce: '<?php echo wp_create_nonce('esc_check_images_nonce'); ?>',
                        command: 'fix_images',
                        seeds: invalidSeeds.map(function(seed) { return seed.id; })
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Fixed ' + response.data.fixed + ' image URLs.');
                            
                            // Reload page
                            window.location.reload();
                        } else {
                            alert('Error: ' + response.data.message);
                            $checkButton.prop('disabled', false);
                            $fixButton.prop('disabled', false).text('Fix Image URLs');
                        }
                    },
                    error: function() {
                        alert('Error communicating with the server.');
                        $checkButton.prop('disabled', false);
                        $fixButton.prop('disabled', false).text('Fix Image URLs');
                    }
                });
            });
            
            // Handle individual fix buttons
            $(document).on('click', '.esc-fix-image', function() {
                const seedId = $(this).data('id');
                const $button = $(this);
                
                $button.prop('disabled', true).text('Fixing...');
                
                // Fix this image
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'esc_check_images',
                        nonce: '<?php echo wp_create_nonce('esc_check_images_nonce'); ?>',
                        command: 'fix_images',
                        seeds: [seedId]
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $button.closest('tr').find('td:eq(3)').html('<span style="color: green;">Fixed</span>');
                            $button.remove();
                        } else {
                            alert('Error: ' + response.data.message);
                            $button.prop('disabled', false).text('Fix');
                        }
                    },
                    error: function() {
                        alert('Error communicating with the server.');
                        $button.prop('disabled', false).text('Fix');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Handle unauthorized access.
     */
    public static function handle_unauthorized() {
        wp_send_json_error( [
            'message' => __( 'You must be logged in to check images.', 'erins-seed-catalog' ),
        ] );
    }
    
    /**
     * Handle AJAX request to check images.
     */
    public static function handle_check_images() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'esc_check_images_nonce' ) ) {
            wp_send_json_error( [
                'message' => __( 'Security check failed.', 'erins-seed-catalog' ),
            ] );
        }
        
        // Check if user can manage options
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to check images.', 'erins-seed-catalog' ),
            ] );
        }
        
        // Check command
        if ( ! isset( $_POST['command'] ) ) {
            wp_send_json_error( [
                'message' => __( 'No command specified.', 'erins-seed-catalog' ),
            ] );
        }
        
        $command = sanitize_text_field( $_POST['command'] );
        
        switch ( $command ) {
            case 'get_seeds':
                self::handle_get_seeds();
                break;
                
            case 'check_images':
                self::handle_check_batch();
                break;
                
            case 'fix_images':
                self::handle_fix_images();
                break;
                
            default:
                wp_send_json_error( [
                    'message' => __( 'Invalid command.', 'erins-seed-catalog' ),
                ] );
        }
    }
    
    /**
     * Handle getting all seeds.
     */
    private static function handle_get_seeds() {
        // Get all seeds
        $seeds = ESC_DB::get_seeds( [
            'limit' => -1,
            'orderby' => 'id',
            'order' => 'ASC',
        ] );
        
        // Extract IDs
        $seed_ids = [];
        foreach ( $seeds as $seed ) {
            $seed_ids[] = $seed->id;
        }
        
        wp_send_json_success( [
            'total' => count( $seed_ids ),
            'seeds' => $seed_ids,
        ] );
    }
    
    /**
     * Handle checking a batch of seeds.
     */
    private static function handle_check_batch() {
        // Check if seeds were provided
        if ( ! isset( $_POST['seeds'] ) || ! is_array( $_POST['seeds'] ) ) {
            wp_send_json_error( [
                'message' => __( 'No seeds provided.', 'erins-seed-catalog' ),
            ] );
        }
        
        $seed_ids = array_map( 'intval', $_POST['seeds'] );
        $results = [];
        
        foreach ( $seed_ids as $seed_id ) {
            // Get seed
            $seed = ESC_DB::get_seed_by_id( $seed_id );
            
            if ( ! $seed ) {
                continue;
            }
            
            $result = [
                'id' => $seed->id,
                'name' => $seed->seed_name . ( ! empty( $seed->variety_name ) ? ' - ' . $seed->variety_name : '' ),
                'has_image' => ! empty( $seed->image_url ),
                'image_url' => $seed->image_url ?? '',
                'is_valid' => false,
                'is_wp_media' => false,
            ];
            
            if ( $result['has_image'] ) {
                // Check if it's a WordPress media library URL
                $result['is_wp_media'] = strpos( $seed->image_url, '/wp-content/uploads/' ) !== false;
                
                // Check if image exists
                $result['is_valid'] = self::check_image_exists( $seed->image_url );
            }
            
            $results[] = $result;
        }
        
        wp_send_json_success( [
            'results' => $results,
        ] );
    }
    
    /**
     * Handle fixing images.
     */
    private static function handle_fix_images() {
        // Check if seeds were provided
        if ( ! isset( $_POST['seeds'] ) || ! is_array( $_POST['seeds'] ) ) {
            wp_send_json_error( [
                'message' => __( 'No seeds provided.', 'erins-seed-catalog' ),
            ] );
        }
        
        $seed_ids = array_map( 'intval', $_POST['seeds'] );
        $fixed = 0;
        
        foreach ( $seed_ids as $seed_id ) {
            // Get seed
            $seed = ESC_DB::get_seed_by_id( $seed_id );
            
            if ( ! $seed || empty( $seed->image_url ) ) {
                continue;
            }
            
            // Fix image URL
            $fixed_url = self::fix_image_url( $seed->image_url );
            
            if ( $fixed_url !== $seed->image_url ) {
                // Update seed
                $result = ESC_DB::update_seed( $seed_id, [
                    'image_url' => $fixed_url,
                ] );
                
                if ( ! is_wp_error( $result ) ) {
                    $fixed++;
                }
            }
        }
        
        wp_send_json_success( [
            'fixed' => $fixed,
        ] );
    }
    
    /**
     * Check if an image exists.
     *
     * @param string $url The image URL to check.
     * @return bool Whether the image exists.
     */
    private static function check_image_exists( $url ) {
        // If it's a WordPress media library URL, check if the file exists
        if ( strpos( $url, '/wp-content/uploads/' ) !== false ) {
            // Convert URL to file path
            $upload_dir = wp_upload_dir();
            $file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
            
            return file_exists( $file_path );
        }
        
        // For external URLs, use wp_remote_head
        $response = wp_remote_head( $url, [
            'timeout' => 5,
            'sslverify' => false,
        ] );
        
        if ( is_wp_error( $response ) ) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code( $response );
        
        return $response_code >= 200 && $response_code < 300;
    }
    
    /**
     * Fix an image URL.
     *
     * @param string $url The image URL to fix.
     * @return string The fixed URL.
     */
    private static function fix_image_url( $url ) {
        // If it's a WordPress media library URL but missing the site URL, add it
        if ( strpos( $url, '/wp-content/uploads/' ) === 0 ) {
            return site_url( $url );
        }
        
        // If it's a protocol-relative URL, add https:
        if ( strpos( $url, '//' ) === 0 ) {
            return 'https:' . $url;
        }
        
        // If it doesn't have a protocol, add https://
        if ( strpos( $url, 'http' ) !== 0 ) {
            return 'https://' . ltrim( $url, '/' );
        }
        
        return $url;
    }
}

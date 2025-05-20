<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ESC_Image_Uploader
 * Handles image upload functionality for the seed catalog.
 */
class ESC_Image_Uploader {

    /**
     * Initialize the image uploader.
     */
    public static function init() {
        // Register AJAX handlers
        add_action( 'wp_ajax_esc_upload_image', [ __CLASS__, 'handle_image_upload' ] );
        add_action( 'wp_ajax_nopriv_esc_upload_image', [ __CLASS__, 'handle_unauthorized' ] );
        add_action( 'wp_ajax_esc_download_image', [ __CLASS__, 'handle_image_download' ] );
        add_action( 'wp_ajax_nopriv_esc_download_image', [ __CLASS__, 'handle_unauthorized' ] );

        // Enqueue scripts and styles
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
    }

    /**
     * Enqueue frontend assets.
     */
    public static function enqueue_assets() {
        // Only enqueue on pages with our shortcodes
        global $post;
        if ( is_a( $post, 'WP_Post' ) ) {
            $has_shortcode = false;
            $shortcodes = [
                'erins_seed_catalog_add_form',
                'erins_seed_catalog_add_form_modern',
            ];

            foreach ( $shortcodes as $shortcode ) {
                if ( has_shortcode( $post->post_content, $shortcode ) ) {
                    $has_shortcode = true;
                    break;
                }
            }

            if ( $has_shortcode ) {
                // Enqueue the CSS
                wp_enqueue_style(
                    'esc-image-uploader',
                    ESC_PLUGIN_URL . 'public/css/esc-image-uploader.css',
                    [],
                    ESC_VERSION
                );

                // Enqueue the JS
                wp_enqueue_script(
                    'esc-image-uploader',
                    ESC_PLUGIN_URL . 'public/js/esc-image-uploader.js',
                    [ 'jquery' ],
                    ESC_VERSION,
                    true
                );

                // Enqueue WordPress media if user can upload
                if ( current_user_can( 'upload_files' ) ) {
                    wp_enqueue_media();
                }
            }
        }
    }    /**
     * Enqueue admin assets.
     */
    public static function enqueue_admin_assets( $hook ) {
        // Only on our plugin's admin pages
        if ( strpos( $hook, 'esc-seed-catalog' ) !== false || strpos( $hook, 'esc-manage-catalog' ) !== false ) {
            // Enqueue the CSS
            wp_enqueue_style(
                'esc-image-uploader',
                ESC_PLUGIN_URL . 'public/css/esc-image-uploader.css',
                [],
                ESC_VERSION
            );

            // Enqueue the JS
            wp_enqueue_script(
                'esc-image-uploader',
                ESC_PLUGIN_URL . 'public/js/esc-image-uploader.js',
                [ 'jquery' ],
                ESC_VERSION,
                true
            );
            
            // Add AJAX object with URL and nonce
            wp_localize_script(
                'esc-image-uploader',
                'esc_ajax_object',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( 'esc_ajax_nonce' )
                ]
            );

            // Enqueue WordPress media
            wp_enqueue_media();
        }
    }

    /**
     * Handle unauthorized access.
     */
    public static function handle_unauthorized() {
        wp_send_json_error( [
            'message' => __( 'You must be logged in to upload images.', 'erins-seed-catalog' ),
        ] );
    }

    /**
     * Handle image upload via AJAX.
     */
    public static function handle_image_upload() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'esc_ajax_nonce' ) ) {
            wp_send_json_error( [
                'message' => __( 'Security check failed.', 'erins-seed-catalog' ),
            ] );
        }

        // Check if user can upload files
        if ( ! current_user_can( 'upload_files' ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to upload files.', 'erins-seed-catalog' ),
            ] );
        }

        // Check if file was uploaded
        if ( empty( $_FILES['image'] ) ) {
            wp_send_json_error( [
                'message' => __( 'No image was provided.', 'erins-seed-catalog' ),
            ] );
        }

        // Get WordPress upload directory
        $upload_dir = wp_upload_dir();

        // Check if upload directory is writable
        if ( $upload_dir['error'] ) {
            wp_send_json_error( [
                'message' => $upload_dir['error'],
            ] );
        }

        // Prepare file data
        $file = $_FILES['image'];

        // Check for upload errors
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            $error_message = self::get_upload_error_message( $file['error'] );
            wp_send_json_error( [
                'message' => $error_message,
            ] );
        }

        // Validate file type
        $file_type = wp_check_filetype( $file['name'], [ 'jpg|jpeg|jpe' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png' ] );
        if ( ! $file_type['type'] ) {
            wp_send_json_error( [
                'message' => __( 'Invalid file type. Please upload a JPEG, PNG, or GIF image.', 'erins-seed-catalog' ),
            ] );
        }

        // Use WordPress media handling to upload the file
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        // Upload the file and get attachment ID
        $attachment_id = media_handle_upload( 'image', 0 );

        if ( is_wp_error( $attachment_id ) ) {
            wp_send_json_error( [
                'message' => $attachment_id->get_error_message(),
            ] );
        }

        // Get the attachment URL
        $attachment_url = wp_get_attachment_url( $attachment_id );

        // Log the successful upload
        error_log('ESC Image Uploader - Image uploaded successfully: ' . $attachment_url);

        // Return success response
        wp_send_json_success( [
            'url' => $attachment_url,
            'id' => $attachment_id,
        ] );
    }

    /**
     * Handle image download from URL via AJAX.
     */
    public static function handle_image_download() {
        // Check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'esc_ajax_nonce' ) ) {
            wp_send_json_error( [
                'message' => __( 'Security check failed.', 'erins-seed-catalog' ),
            ] );
        }

        // Check if user can upload files
        if ( ! current_user_can( 'upload_files' ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to upload files.', 'erins-seed-catalog' ),
            ] );
        }

        // Check if URL was provided
        if ( empty( $_POST['image_url'] ) ) {
            wp_send_json_error( [
                'message' => __( 'No image URL was provided.', 'erins-seed-catalog' ),
            ] );
        }

        $image_url = esc_url_raw( wp_unslash( $_POST['image_url'] ) );

        // Validate URL
        if ( ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
            wp_send_json_error( [
                'message' => __( 'Invalid image URL.', 'erins-seed-catalog' ),
            ] );
        }

        // Fix image URLs to get direct image files
        $image_url = self::fix_wikimedia_url($image_url);

        // Check if the URL was successfully processed
        if (empty($image_url)) {
            // If we couldn't process the URL, return an error with instructions
            wp_send_json_error([
                'message' => __('Could not process the image URL. The URL might point to a page rather than a direct image file.', 'erins-seed-catalog'),
                'source_url' => isset($_POST['source_url']) ? esc_url_raw(wp_unslash($_POST['source_url'])) : '',
                'needs_manual_download' => true
            ]);
        }

        // Get WordPress upload directory
        $upload_dir = wp_upload_dir();

        // Check if upload directory is writable
        if ( $upload_dir['error'] ) {
            wp_send_json_error( [
                'message' => $upload_dir['error'],
            ] );
        }

        // Download the image
        $temp_file = download_url( $image_url );

        // Check for download errors
        if ( is_wp_error( $temp_file ) ) {
            wp_send_json_error( [
                'message' => __( 'Error downloading image: ', 'erins-seed-catalog' ) . $temp_file->get_error_message(),
            ] );
        }

        // Get file info
        $file_info = wp_check_filetype( basename( $image_url ) );

        // If file type couldn't be determined from URL, try to detect from file content
        if ( empty( $file_info['ext'] ) || empty( $file_info['type'] ) ) {
            $file_info = wp_check_filetype( $temp_file );

            // If still empty, default to jpg
            if ( empty( $file_info['ext'] ) || empty( $file_info['type'] ) ) {
                $file_info = [
                    'ext' => 'jpg',
                    'type' => 'image/jpeg',
                ];
            }
        }

        // Prepare file array for media_handle_sideload
        $file = [
            'name' => sanitize_file_name( basename( $image_url ) ),
            'tmp_name' => $temp_file,
            'error' => 0,
            'size' => filesize( $temp_file ),
        ];

        // If filename doesn't have extension, add it
        if ( ! preg_match( '/\.' . $file_info['ext'] . '$/i', $file['name'] ) ) {
            $file['name'] .= '.' . $file_info['ext'];
        }

        // Load required files for media handling
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        // Add image to media library
        $attachment_id = media_handle_sideload( $file, 0 );

        // Remove temporary file
        @unlink( $temp_file );

        // Check for errors
        if ( is_wp_error( $attachment_id ) ) {
            wp_send_json_error( [
                'message' => __( 'Error adding image to media library: ', 'erins-seed-catalog' ) . $attachment_id->get_error_message(),
            ] );
        }

        // Get the attachment URL
        $attachment_url = wp_get_attachment_url( $attachment_id );

        // Return success response
        wp_send_json_success( [
            'url' => $attachment_url,
            'id' => $attachment_id,
        ] );
    }

    /**
     * Fix image URLs to ensure they point to direct image files.
     *
     * @param string $url The image URL to fix.
     * @return string The fixed URL or empty string if the URL can't be fixed.
     */
    private static function fix_wikimedia_url( $url ) {
        // Check if this is a Wikimedia Commons URL
        if ( strpos( $url, 'upload.wikimedia.org' ) !== false ) {
            // Check if it's a thumbnail URL (contains /thumb/ in the path)
            if ( strpos( $url, '/thumb/' ) !== false ) {
                // Extract the original file path by removing /thumb/ and the dimension part
                $url_parts = explode( '/thumb/', $url );
                if ( count( $url_parts ) === 2 ) {
                    $base_url = $url_parts[0];
                    $file_path = $url_parts[1];

                    // Remove the dimension part (e.g., /1280px-filename.jpg)
                    $file_path_parts = explode( '/', $file_path );
                    array_pop( $file_path_parts ); // Remove the last part (the resized filename)
                    $original_file_path = implode( '/', $file_path_parts );

                    // Construct the direct file URL
                    return $base_url . '/' . $original_file_path;
                }
            }
        }

        // Handle Wikimedia Commons File pages
        if ( strpos( $url, 'commons.wikimedia.org/wiki/File:' ) !== false ) {
            // We need to fetch the page and extract the actual image URL
            $response = wp_remote_get( $url, [
                'timeout' => 15, // Increase timeout for potentially slow responses
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', // Use a standard user agent
            ]);

            if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
                $body = wp_remote_retrieve_body( $response );

                // Try to find the full-resolution image link
                if ( preg_match( '/<div class="fullImageLink"[^>]*>\s*<a\s+href="([^"]+)"[^>]*>/i', $body, $matches ) ) {
                    $image_url = $matches[1];
                    if ( strpos( $image_url, '//' ) === 0 ) {
                        $image_url = 'https:' . $image_url;
                    }
                    return $image_url;
                }

                // Fallback to the og:image meta tag
                if ( preg_match( '/<meta[^>]*property="og:image"[^>]*content="([^"]+)"[^>]*>/i', $body, $matches ) ) {
                    return $matches[1];
                }
            }

            // Log the failure for debugging
            error_log( 'Failed to extract image from Wikimedia Commons URL: ' . $url );

            // If we can't extract the image, return empty to indicate failure
            return '';
        }

        // Handle Pixabay URLs
        if ( strpos( $url, 'pixabay.com/photos/' ) !== false ) {
            // We need to fetch the page and extract the actual image URL
            $response = wp_remote_get( $url );
            if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
                $body = wp_remote_retrieve_body( $response );
                // Look for the high-resolution image URL
                if ( preg_match( '/<img[^>]*data-fullsize="([^"]+)"[^>]*>/i', $body, $matches ) ) {
                    return $matches[1];
                }
                // Fallback to other image sources
                if ( preg_match( '/<img[^>]*src="([^"]+)"[^>]*class="[^"]*detail_image[^"]*"/i', $body, $matches ) ) {
                    return $matches[1];
                }
            }
            // If we can't extract the image, return empty to indicate failure
            return '';
        }

        // Handle Unsplash URLs
        if ( strpos( $url, 'unsplash.com/photos/' ) !== false ) {
            // We need to fetch the page and extract the actual image URL
            $response = wp_remote_get( $url );
            if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
                $body = wp_remote_retrieve_body( $response );
                // Look for the high-resolution image URL
                if ( preg_match( '/<img[^>]*src="([^"]+)"[^>]*data-test="photo-grid-single-col"/i', $body, $matches ) ) {
                    return $matches[1];
                }
                // Fallback to meta property
                if ( preg_match( '/<meta[^>]*property="og:image"[^>]*content="([^"]+)"[^>]*>/i', $body, $matches ) ) {
                    return $matches[1];
                }
            }
            // If we can't extract the image, return empty to indicate failure
            return '';
        }

        // Handle Pexels URLs
        if ( strpos( $url, 'pexels.com/photo/' ) !== false ) {
            // We need to fetch the page and extract the actual image URL
            $response = wp_remote_get( $url, [
                'timeout' => 15, // Increase timeout for potentially slow responses
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', // Use a standard user agent
            ]);
            if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
                $body = wp_remote_retrieve_body( $response );

                // First try to find the high-resolution download URL
                if ( preg_match( '/"download":"([^"]+)"/i', $body, $matches ) ) {
                    $download_url = str_replace('\\', '', $matches[1]);
                    return $download_url;
                }

                // Then try the og:image meta tag
                if ( preg_match( '/<meta[^>]*property="og:image"[^>]*content="([^"]+)"[^>]*>/i', $body, $matches ) ) {
                    return $matches[1];
                }

                // Try to find the main photo element
                if ( preg_match( '/<img[^>]*data-big-src="([^"]+)"[^>]*>/i', $body, $matches ) ) {
                    return $matches[1];
                }

                // Fallback to other image sources
                if ( preg_match( '/<img[^>]*src="([^"]+)"[^>]*class="[^"]*photo-item__img[^"]*"/i', $body, $matches ) ) {
                    return $matches[1];
                }

                // Last resort - try to find any large image
                if ( preg_match_all( '/<img[^>]*src="([^"]+)"[^>]*>/i', $body, $matches ) ) {
                    foreach ( $matches[1] as $img_url ) {
                        if ( strpos( $img_url, 'pexels.com' ) !== false &&
                             (strpos( $img_url, 'w=1200' ) !== false || strpos( $img_url, 'h=1200' ) !== false) ) {
                            return $img_url;
                        }
                    }
                }
            }

            // Log the failure for debugging
            error_log( 'Failed to extract image from Pexels URL: ' . $url );

            // If we can't extract the image, return empty to indicate failure
            return '';
        }

        // Return the original URL if it's not a URL we can fix
        return $url;
    }



    /**
     * Get error message for upload error code.
     *
     * @param int $error_code PHP upload error code.
     * @return string Error message.
     */
    private static function get_upload_error_message( $error_code ) {
        switch ( $error_code ) {
            case UPLOAD_ERR_INI_SIZE:
                return __( 'The uploaded file exceeds the upload_max_filesize directive in php.ini.', 'erins-seed-catalog' );
            case UPLOAD_ERR_FORM_SIZE:
                return __( 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.', 'erins-seed-catalog' );
            case UPLOAD_ERR_PARTIAL:
                return __( 'The uploaded file was only partially uploaded.', 'erins-seed-catalog' );
            case UPLOAD_ERR_NO_FILE:
                return __( 'No file was uploaded.', 'erins-seed-catalog' );
            case UPLOAD_ERR_NO_TMP_DIR:
                return __( 'Missing a temporary folder.', 'erins-seed-catalog' );
            case UPLOAD_ERR_CANT_WRITE:
                return __( 'Failed to write file to disk.', 'erins-seed-catalog' );
            case UPLOAD_ERR_EXTENSION:
                return __( 'A PHP extension stopped the file upload.', 'erins-seed-catalog' );
            default:
                return __( 'Unknown upload error.', 'erins-seed-catalog' );
        }
    }

    /**
     * Render the image uploader HTML.
     *
     * @param string $input_name The name of the input field.
     * @param string $input_id The ID of the input field.
     * @param string $current_url The current image URL (if any).
     * @param string $label The label for the uploader.
     */
    public static function render( $input_name = 'image_url', $input_id = 'esc_image_url', $current_url = '', $label = '' ) {
        if ( empty( $label ) ) {
            $label = __( 'Image', 'erins-seed-catalog' );
        }

        ?>
        <div class="esc-image-uploader">
            <label for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $label ); ?></label>

            <div class="esc-dropzone <?php echo ! empty( $current_url ) ? 'has-image' : ''; ?>">
                <input type="file" class="esc-file-input" accept="image/*" style="display: none;">
                <input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" name="<?php echo esc_attr( $input_name ); ?>" class="esc-url-input" value="<?php echo esc_attr( $current_url ); ?>">
                <?php
                // Log the input field for debugging
                error_log('ESC Image Uploader - Rendering input with name: ' . $input_name . ' and id: ' . $input_id);

                // Add a second hidden input with the same value to ensure it's included in form data
                if ( ! empty( $current_url ) ) :
                ?>
                <input type="hidden" name="<?php echo esc_attr( $input_name ); ?>_backup" value="<?php echo esc_attr( $current_url ); ?>">
                <?php endif; ?>

                <div class="esc-dropzone-content">
                    <div class="esc-dropzone-icon dashicons dashicons-upload"></div>
                    <div class="esc-dropzone-text"><?php esc_html_e( 'Drag & drop an image here', 'erins-seed-catalog' ); ?></div>
                    <div class="esc-dropzone-subtext"><?php esc_html_e( 'or click to select a file', 'erins-seed-catalog' ); ?></div>

                    <?php if ( current_user_can( 'upload_files' ) ) : ?>
                        <div class="esc-button-group">
                            <button type="button" class="esc-button esc-button-secondary esc-wp-media-btn">
                                <span class="dashicons dashicons-admin-media"></span>
                                <?php esc_html_e( 'Media Library', 'erins-seed-catalog' ); ?>
                            </button>
                            <button type="button" class="esc-button esc-button-secondary esc-download-image-btn">
                                <span class="dashicons dashicons-download"></span>
                                <?php esc_html_e( 'Download Image', 'erins-seed-catalog' ); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="esc-image-preview" <?php echo ! empty( $current_url ) ? 'style="display: block;"' : ''; ?>>
                <img src="<?php echo esc_url( $current_url ); ?>" class="esc-preview-image" alt="<?php esc_attr_e( 'Image preview', 'erins-seed-catalog' ); ?>">
                <div class="esc-preview-actions">
                    <a href="#" class="esc-remove-image">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e( 'Remove image', 'erins-seed-catalog' ); ?>
                    </a>
                </div>
            </div>

            <div class="esc-upload-progress">
                <div class="esc-progress-bar"></div>
            </div>

            <div class="esc-upload-error"></div>

            <p class="description"><?php esc_html_e( 'Upload an image of the plant, fruit, or seeds. Recommended size: 800x600 pixels.', 'erins-seed-catalog' ); ?></p>
        </div>
        <?php
    }
}

// Initialize the class
ESC_Image_Uploader::init();

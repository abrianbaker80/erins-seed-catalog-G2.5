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
    }

    /**
     * Enqueue admin assets.
     */
    public static function enqueue_admin_assets( $hook ) {
        // Only on our plugin's admin pages
        if ( strpos( $hook, 'esc-seed-catalog' ) !== false ) {
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
        
        // Return success response
        wp_send_json_success( [
            'url' => $attachment_url,
            'id' => $attachment_id,
        ] );
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
                
                <div class="esc-dropzone-content">
                    <div class="esc-dropzone-icon dashicons dashicons-upload"></div>
                    <div class="esc-dropzone-text"><?php esc_html_e( 'Drag & drop an image here', 'erins-seed-catalog' ); ?></div>
                    <div class="esc-dropzone-subtext"><?php esc_html_e( 'or click to select a file', 'erins-seed-catalog' ); ?></div>
                    
                    <?php if ( current_user_can( 'upload_files' ) ) : ?>
                        <button type="button" class="esc-button esc-button-secondary esc-wp-media-btn">
                            <span class="dashicons dashicons-admin-media"></span>
                            <?php esc_html_e( 'Media Library', 'erins-seed-catalog' ); ?>
                        </button>
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

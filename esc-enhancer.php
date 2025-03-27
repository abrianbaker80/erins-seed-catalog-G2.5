<?php
/**
 * Plugin Name: Erin's Seed Catalog Enhancer
 * Description: Enhances the Erin's Seed Catalog plugin with improved UI/UX
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: esc-enhancer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class ESC_Enhancer {
    /**
     * Plugin instance
     */
    private static $instance = null;

    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Define constants
        $this->define_constants();

        // Initialize hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'), 20);
        add_filter('esc_seed_card_html', array($this, 'enhance_seed_card'), 10, 2);
        add_action('wp_footer', array($this, 'add_seed_detail_modal'));
    }

    /**
     * Define plugin constants
     */
    private function define_constants() {
        define('ESC_ENHANCER_VERSION', '1.0.0');
        define('ESC_ENHANCER_PATH', plugin_dir_path(__FILE__));
        define('ESC_ENHANCER_URL', plugin_dir_url(__FILE__));
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        // Only load if Erin's Seed Catalog is active
        if (!function_exists('ESC_Functions::is_plugin_page')) {
            return;
        }

        // Check if we're on a page with the plugin's shortcodes
        global $post;
        if (!is_a($post, 'WP_Post')) {
            return;
        }

        $has_shortcode = false;
        $shortcodes = array(
            'erins_seed_catalog_add_form',
            'erins_seed_catalog_view',
            'erins_seed_catalog_search',
            'erins_seed_catalog_categories',
            'erins_seed_catalog_add_form_modern'
        );

        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                $has_shortcode = true;
                break;
            }
        }

        if (!$has_shortcode) {
            return;
        }

        // Enqueue CSS
        wp_enqueue_style(
            'esc-enhanced-styles',
            ESC_ENHANCER_URL . 'assets/css/esc-enhanced-styles.css',
            array('esc-modern-form', 'esc-public-styles'),
            ESC_ENHANCER_VERSION
        );

        // Enqueue floating label fix CSS
        wp_enqueue_style(
            'esc-label-fix',
            ESC_ENHANCER_URL . 'assets/css/esc-label-fix.css',
            array('esc-modern-form', 'esc-enhanced-styles'),
            ESC_ENHANCER_VERSION
        );

        // Enqueue JS
        wp_enqueue_script(
            'esc-enhanced-scripts',
            ESC_ENHANCER_URL . 'assets/js/esc-enhanced-scripts.js',
            array('jquery', 'esc-public-scripts'),
            ESC_ENHANCER_VERSION,
            true
        );

        // Enqueue floating label fix JS
        wp_enqueue_script(
            'esc-label-fix',
            ESC_ENHANCER_URL . 'assets/js/esc-label-fix.js',
            array('jquery'),
            ESC_ENHANCER_VERSION,
            true
        );
    }

    /**
     * Enhance seed card HTML
     */
    public function enhance_seed_card($html, $seed) {
        // If the filter isn't being used correctly, return original
        if (empty($seed) || !is_object($seed)) {
            return $html;
        }

        // Build enhanced card HTML
        ob_start();
        ?>
        <div class="esc-seed-card" data-seed-id="<?php echo esc_attr($seed->id); ?>">
            <?php if (!empty($seed->image_url) && filter_var($seed->image_url, FILTER_VALIDATE_URL)): ?>
                <img src="<?php echo esc_url($seed->image_url); ?>"
                     alt="<?php echo esc_attr($seed->seed_name); ?><?php echo $seed->variety_name ? ' - ' . esc_attr($seed->variety_name) : ''; ?>"
                     class="esc-seed-image" loading="lazy">
            <?php else: ?>
                <div class="esc-seed-image esc-no-image">
                    <span class="dashicons dashicons-seedling"></span>
                </div>
            <?php endif; ?>

            <div class="esc-seed-card-content">
                <h3>
                    <?php echo esc_html($seed->seed_name); ?>
                    <?php if (!empty($seed->variety_name)): ?>
                        <span class="esc-variety-name">- <?php echo esc_html($seed->variety_name); ?></span>
                    <?php endif; ?>
                </h3>

                <div class="esc-seed-details">
                    <?php
                    // Display key information
                    if (function_exists('ESC_Functions::display_seed_field')) {
                        ESC_Functions::display_seed_field($seed, 'plant_type', __('Type', 'erins-seed-catalog'));
                        ESC_Functions::display_seed_field($seed, 'days_to_maturity', __('Matures In', 'erins-seed-catalog'));
                        ESC_Functions::display_seed_field($seed, 'sunlight', __('Sunlight', 'erins-seed-catalog'));
                        ESC_Functions::display_seed_field($seed, 'sowing_method', __('Sowing', 'erins-seed-catalog'));
                    }
                    ?>
                </div>

                <?php if (!empty($seed->categories)): ?>
                    <div class="esc-categories">
                        <?php foreach ($seed->categories as $category): ?>
                            <span class="esc-category"><?php echo esc_html($category->name); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="esc-card-action">
                    <span class="esc-view-details"><?php _e('View Details', 'esc-enhancer'); ?></span>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Add seed detail modal to footer
     */
    public function add_seed_detail_modal() {
        // Only add if we're on a page with the catalog view
        global $post;
        if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'erins_seed_catalog_view')) {
            return;
        }

        ?>
        <div id="esc-seed-modal" class="esc-modal">
            <div class="esc-modal-content">
                <span class="esc-modal-close">&times;</span>
                <div id="esc-modal-content"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Function to show seed details in modal
            window.showSeedDetails = function(seedId) {
                const $modal = $('#esc-seed-modal');
                const $content = $('#esc-modal-content');

                // Show loading state
                $content.html('<div class="esc-loading">Loading...</div>');
                $modal.fadeIn(300);

                // Fetch seed details via AJAX
                $.ajax({
                    url: esc_ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'esc_get_seed_details',
                        seed_id: seedId,
                        nonce: esc_ajax_object.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $content.html(response.data.html);
                        } else {
                            $content.html('<p class="esc-error">Error loading seed details.</p>');
                        }
                    },
                    error: function() {
                        $content.html('<p class="esc-error">Error loading seed details.</p>');
                    }
                });
            };

            // Close modal when clicking the close button or outside the modal
            $('.esc-modal-close, .esc-modal').on('click', function(e) {
                if (e.target === this) {
                    $('#esc-seed-modal').fadeOut(300);
                }
            });

            // Prevent clicks inside modal from closing it
            $('.esc-modal-content').on('click', function(e) {
                e.stopPropagation();
            });
        });
        </script>
        <?php
    }
}

// Initialize the plugin
function esc_enhancer_init() {
    ESC_Enhancer::get_instance();
}
add_action('plugins_loaded', 'esc_enhancer_init');

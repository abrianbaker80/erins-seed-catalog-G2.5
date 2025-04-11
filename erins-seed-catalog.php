<?php
/**
 * Plugin Name:       Erin's Seed Catalog
 * Plugin URI:        https://github.com/abrianbaker80/erins-seed-catalog-G2.5.git
 * Description:       Catalog and track your vegetable garden seeds with AI-assisted information retrieval via the Gemini API. Mobile-first design.
 * Version:           1.1.71
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            Allen Baker
 * Author URI:        https://github.com/abrianbaker80/erins-seed-catalog-G2.5.git
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       erins-seed-catalog
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define('ESC_VERSION', '1.1.71');
define( 'ESC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ESC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ESC_PLUGIN_FILE', __FILE__ );
define( 'ESC_TEXT_DOMAIN', 'erins-seed-catalog' );
define( 'ESC_DB_VERSION_OPTION', 'esc_db_version' );
define( 'ESC_DB_CURRENT_VERSION', '1.0' ); // Increment this when changing DB schema
define( 'ESC_SETTINGS_OPTION_GROUP', 'esc_settings_group' );
define( 'ESC_API_KEY_OPTION', 'esc_gemini_api_key' );
define( 'ESC_GEMINI_MODEL_OPTION', 'esc_gemini_model' );

// Check PHP Version
if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
    add_action( 'admin_notices', function() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php esc_html_e( "Erin's Seed Catalog requires PHP version 8.2 or higher. Your current version is ", 'erins-seed-catalog' ); echo esc_html( PHP_VERSION ); ?>.</p>
        </div>
        <?php
    });
    // Optionally deactivate the plugin
    // add_action('admin_init', function() { deactivate_plugins(plugin_basename(__FILE__)); });
    return; // Stop loading plugin
}


/**
 * Load plugin textdomain for localization.
 */
function esc_load_textdomain() {
	load_plugin_textdomain( ESC_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'esc_load_textdomain' );

/**
 * Include necessary files.
 */
require_once ESC_PLUGIN_DIR . 'includes/class-esc-db.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-taxonomy.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-gemini-api.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-model-updater.php';
require_once ESC_PLUGIN_DIR . 'includes/esc-functions.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-ajax.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-shortcodes.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-admin.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-update-checker.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-variety-suggestions.php';
require_once ESC_PLUGIN_DIR . 'includes/class-esc-image-uploader.php';

/**
 * Activation Hook: Create database table, register taxonomy, add default terms, flush rewrite rules.
 */
function esc_activate_plugin() {
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// Check/Create Database Table
	ESC_DB::create_table();
    update_option( ESC_DB_VERSION_OPTION, ESC_DB_CURRENT_VERSION );

    // Register Custom Taxonomy
    ESC_Taxonomy::register();
    ESC_Taxonomy::add_default_terms();

	// Flush rewrite rules to ensure taxonomy endpoints work correctly.
	flush_rewrite_rules();

    // Set default options if needed
    if ( false === get_option( ESC_API_KEY_OPTION ) ) {
        update_option( ESC_API_KEY_OPTION, '' );
    }

    // Set default Gemini model if not set
    if ( false === get_option( ESC_GEMINI_MODEL_OPTION ) ) {
        update_option( ESC_GEMINI_MODEL_OPTION, 'gemini-2.0-flash-lite' );
    }
}
register_activation_hook( ESC_PLUGIN_FILE, 'esc_activate_plugin' );

/**
 * Deactivation Hook: Flush rewrite rules.
 */
function esc_deactivate_plugin() {
	// Unregister taxonomy? Generally not needed on deactivation.
	flush_rewrite_rules();
}
register_deactivation_hook( ESC_PLUGIN_FILE, 'esc_deactivate_plugin' );

/**
 * Initialize Plugin Classes
 */
function esc_init_plugin() {
    // Check if DB needs update
    $current_db_version = get_option( ESC_DB_VERSION_OPTION );
    if ( version_compare( $current_db_version, ESC_DB_CURRENT_VERSION, '<' ) ) {
        ESC_DB::create_table(); // Or run an update function
        update_option( ESC_DB_VERSION_OPTION, ESC_DB_CURRENT_VERSION );
    }

	// Initialize classes that add hooks
	ESC_Taxonomy::init();
	ESC_Admin::init();
	ESC_Shortcodes::init();
    ESC_Ajax::init();
    ESC_Functions::init(); // For enqueueing
    ESC_Model_Updater::init(); // For model updates

    // Initialize the update checker
    if (file_exists(ESC_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php')) {
        require_once ESC_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';
        $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
            'https://github.com/abrianbaker80/erins-seed-catalog-G2.5/',
            __FILE__,
            'erins-seed-catalog'
        );

        // Set the branch that contains the stable release
        $myUpdateChecker->setBranch('master');

        // Optional: Enable release assets
        $myUpdateChecker->getVcsApi()->enableReleaseAssets();
    } else {
        // Fallback to the old update checker if the library is not available
        $update_checker = new ESC_Update_Checker();
        $update_checker->init();
    }
}
add_action( 'plugins_loaded', 'esc_init_plugin' );

/**
 * Uninstall Hook: Remove database table, options, and taxonomy terms.
 * Use carefully! This permanently deletes data.
 */
function esc_uninstall_plugin() {
    // Confirm user really wants to uninstall (optional, good practice)

    // Remove database table
    // ESC_DB::drop_table(); // Uncomment if you want full cleanup

    // Remove options
    delete_option( ESC_API_KEY_OPTION );
    delete_option( ESC_GEMINI_MODEL_OPTION );
    delete_option( ESC_DB_VERSION_OPTION );

    // Remove terms and taxonomy? More complex, involves WP Term API.
    // Consider leaving terms unless explicitly requested.

    // Flush rewrite rules
    flush_rewrite_rules();
}
// register_uninstall_hook( ESC_PLUGIN_FILE, 'esc_uninstall_plugin' ); // Uncomment to enable uninstall cleanup







































































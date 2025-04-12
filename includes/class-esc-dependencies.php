<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class ESC_Dependencies
 * Handles loading of third-party dependencies.
 */
class ESC_Dependencies {

    /**
     * Initialize dependencies.
     */
    public static function init() {
        self::load_parsedown();
    }

    /**
     * Load the Parsedown library for Markdown parsing.
     */
    public static function load_parsedown() {
        // Check if Parsedown is already loaded
        if ( ! class_exists( 'Parsedown' ) ) {
            // Include the Parsedown library
            require_once ESC_PLUGIN_DIR . 'vendor/parsedown/Parsedown.php';
        }
    }
}

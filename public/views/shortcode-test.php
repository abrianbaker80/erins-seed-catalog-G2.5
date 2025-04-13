<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// This is a test page to verify shortcode functionality
?>

<div class="wrap">
    <h1>Shortcode Test Page</h1>
    
    <h2>Enhanced View Shortcode Test</h2>
    <div class="shortcode-test">
        <?php 
        // Test if the shortcode function works directly
        if (function_exists('ESC_Shortcodes::render_enhanced_catalog_view')) {
            echo ESC_Shortcodes::render_enhanced_catalog_view([]);
        } else {
            echo '<p>Error: Function ESC_Shortcodes::render_enhanced_catalog_view does not exist.</p>';
        }
        ?>
    </div>
    
    <h2>Basic View Shortcode Test</h2>
    <div class="shortcode-test">
        <?php 
        // Test if the basic view shortcode function works directly
        if (function_exists('ESC_Shortcodes::render_catalog_view')) {
            echo ESC_Shortcodes::render_catalog_view([]);
        } else {
            echo '<p>Error: Function ESC_Shortcodes::render_catalog_view does not exist.</p>';
        }
        ?>
    </div>
    
    <h2>Test Enhanced View Shortcode Test</h2>
    <div class="shortcode-test">
        <?php 
        // Test if the test enhanced view shortcode function works directly
        if (function_exists('ESC_Shortcodes::render_test_enhanced_view')) {
            echo ESC_Shortcodes::render_test_enhanced_view([]);
        } else {
            echo '<p>Error: Function ESC_Shortcodes::render_test_enhanced_view does not exist.</p>';
        }
        ?>
    </div>
</div>

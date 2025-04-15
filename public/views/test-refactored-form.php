<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// This is a test page to debug the refactored form
?>

<div class="wrap">
    <h1>Test Refactored Form</h1>
    
    <p>This page is used to test the refactored form shortcode.</p>
    
    <div class="esc-debug-info">
        <h2>Debug Information</h2>
        
        <h3>CSS Files</h3>
        <ul>
            <?php
            $css_files = [
                'esc-design-system.css',
                'esc-components.css',
                'esc-refactored.css',
                'esc-variety-dropdown.css',
                'esc-modern-form.css',
                'esc-image-uploader.css'
            ];
            
            foreach ($css_files as $file) {
                $file_path = ESC_PLUGIN_DIR . 'public/css/' . $file;
                $exists = file_exists($file_path);
                $size = $exists ? filesize($file_path) : 'N/A';
                echo '<li>' . esc_html($file) . ': ' . ($exists ? 'Exists' : 'Missing') . ' (Size: ' . esc_html($size) . ' bytes)</li>';
            }
            ?>
        </ul>
        
        <h3>JavaScript Files</h3>
        <ul>
            <?php
            $js_files = [
                'esc-core.js',
                'esc-ui.js',
                'esc-form.js',
                'esc-ai.js',
                'esc-variety.js',
                'esc-image-uploader.js'
            ];
            
            foreach ($js_files as $file) {
                $file_path = ESC_PLUGIN_DIR . 'public/js/' . $file;
                $exists = file_exists($file_path);
                $size = $exists ? filesize($file_path) : 'N/A';
                echo '<li>' . esc_html($file) . ': ' . ($exists ? 'Exists' : 'Missing') . ' (Size: ' . esc_html($size) . ' bytes)</li>';
            }
            ?>
        </ul>
        
        <h3>Template Files</h3>
        <ul>
            <?php
            $template_files = [
                'add-seed-form-refactored.php',
                'add-seed-form-fields.php'
            ];
            
            foreach ($template_files as $file) {
                $file_path = ESC_PLUGIN_DIR . 'public/views/' . $file;
                $exists = file_exists($file_path);
                $size = $exists ? filesize($file_path) : 'N/A';
                echo '<li>' . esc_html($file) . ': ' . ($exists ? 'Exists' : 'Missing') . ' (Size: ' . esc_html($size) . ' bytes)</li>';
            }
            ?>
        </ul>
    </div>
    
    <div class="esc-test-form">
        <h2>Form Test</h2>
        <?php echo do_shortcode('[erins_seed_catalog_add_form_refactored]'); ?>
    </div>
</div>

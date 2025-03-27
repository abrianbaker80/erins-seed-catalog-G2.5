<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="esc-shortcode-test">
    <h2>Shortcode Test</h2>
    <p>This is a test to verify the shortcode is working correctly.</p>
    
    <?php 
    // Call the shortcode function directly
    echo ESC_Shortcodes::render_add_form();
    ?>
</div>

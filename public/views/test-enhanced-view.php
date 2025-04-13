<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// This is a test page to verify the enhanced view is working correctly
?>

<div class="wrap">
    <h1>Enhanced Seed Catalog Test</h1>
    
    <p>This page tests the enhanced seed catalog view to ensure proper styling is applied.</p>
    
    <?php 
    // Output the enhanced view shortcode
    echo do_shortcode('[erins_seed_catalog_enhanced_view]'); 
    ?>
</div>

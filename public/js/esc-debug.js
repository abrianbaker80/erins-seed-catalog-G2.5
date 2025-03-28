/**
 * Debug script for Enhanced Seed Catalog
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        console.log('Debug script loaded');
        
        // Check if enhanced catalog exists
        if ($('.esc-enhanced-catalog').length) {
            console.log('Enhanced catalog found on page');
        } else {
            console.log('Enhanced catalog NOT found on page');
        }
        
        // Check if seed cards exist
        if ($('.esc-seed-card').length) {
            console.log('Seed cards found: ' + $('.esc-seed-card').length);
            
            // Log seed IDs
            $('.esc-seed-card').each(function() {
                console.log('Seed card ID: ' + $(this).data('seed-id'));
            });
        } else {
            console.log('No seed cards found');
        }
        
        // Check if modal exists
        if ($('#esc-seed-detail-modal').length) {
            console.log('Modal found');
        } else {
            console.log('Modal NOT found');
        }
        
        // Add test click handler
        $(document).on('click', '.esc-seed-card', function() {
            console.log('Card clicked: ' + $(this).data('seed-id'));
        });
    });
    
})(jQuery);

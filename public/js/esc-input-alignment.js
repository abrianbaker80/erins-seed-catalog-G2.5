/**
 * Specific fixes for input field alignment
 */
jQuery(document).ready(function($) {
    'use strict';

    // Function to fix text alignment in input fields
    function fixInputAlignment() {
        // Add a class to the body to help with CSS targeting
        $('body').addClass('esc-input-alignment-fixed');
        
        // Target all input fields in the seed catalog forms
        $('.esc-modern-form input[type="text"], .esc-modern-form input[type="search"], .esc-form input[type="text"], .esc-form input[type="search"], #esc_seed_name, #esc_variety_name, input[id^="esc_seed_name"], input[id^="esc_variety_name"]').each(function() {
            const $input = $(this);
            
            // Apply inline styles for immediate effect
            $input.css({
                'display': 'flex',
                'align-items': 'center',
                'height': '40px',
                'line-height': '1.5',
                'padding': '0 12px',
                'font-size': '15px',
                'box-sizing': 'border-box',
                'margin': '0'
            });
            
            // Add a class for CSS targeting
            $input.addClass('esc-input-aligned');
        });
        
        // Target the seed type and variety fields specifically
        $('.esc-seed-variety-row .esc-seed-field input, .esc-seed-variety-row .esc-variety-field input').each(function() {
            const $input = $(this);
            
            // Apply inline styles for immediate effect
            $input.css({
                'height': '40px',
                'line-height': '1.5',
                'padding': '0 12px',
                'font-size': '15px'
            });
        });
        
        // Fix for the field labels
        $('.esc-seed-field label, .esc-variety-field label').each(function() {
            const $label = $(this);
            
            // Apply inline styles for immediate effect
            $label.css({
                'display': 'block',
                'margin-bottom': '5px',
                'font-weight': '600'
            });
        });
        
        // Fix for the description text
        $('.esc-seed-field p.description, .esc-variety-field p.description').each(function() {
            const $description = $(this);
            
            // Apply inline styles for immediate effect
            $description.css({
                'margin-top': '5px',
                'font-size': '13px',
                'color': '#666'
            });
        });
    }
    
    // Run the fix when the page loads
    fixInputAlignment();
    
    // Also run the fix after a short delay to ensure all elements are loaded
    setTimeout(fixInputAlignment, 500);
    
    // Run the fix again when the window is resized
    $(window).on('resize', fixInputAlignment);
    
    // Run the fix when any AJAX request completes
    $(document).ajaxComplete(function() {
        fixInputAlignment();
    });
});

/**
 * JavaScript to specifically remove green vertical lines
 */
jQuery(document).ready(function($) {
    'use strict';

    // Function to remove green vertical lines
    function removeGreenLines() {
        console.log('Removing green vertical lines');
        
        // Add a style element to the head with !important rules
        if ($('#remove-green-lines-style').length === 0) {
            const css = `
                /* Remove all ::after pseudo-elements */
                .esc-ai-processed::after,
                .esc-ai-populated::after,
                .esc-form-field::after,
                .esc-seed-field::after,
                .esc-variety-field::after,
                .esc-description::after,
                .esc-image::after,
                [class*="esc-"]::after {
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                    background-color: transparent !important;
                    border: none !important;
                    content: none !important;
                }
                
                /* Remove left borders from inputs */
                .esc-ai-processed input,
                .esc-ai-processed textarea,
                .esc-ai-processed select,
                .esc-ai-populated input,
                .esc-ai-populated textarea,
                .esc-ai-populated select,
                .esc-form-field input,
                .esc-form-field textarea,
                .esc-form-field select,
                .esc-seed-field input,
                .esc-seed-field textarea,
                .esc-seed-field select,
                .esc-variety-field input,
                .esc-variety-field textarea,
                .esc-variety-field select,
                .esc-description input,
                .esc-description textarea,
                .esc-description select,
                .esc-image input,
                .esc-image textarea,
                .esc-image select,
                [class*="esc-"] input,
                [class*="esc-"] textarea,
                [class*="esc-"] select {
                    border-left: none !important;
                    background-image: none !important;
                }
                
                /* Remove all green borders and backgrounds */
                .esc-ai-processed,
                .esc-ai-populated,
                .esc-form-field,
                .esc-seed-field,
                .esc-variety-field,
                .esc-description,
                .esc-image,
                [class*="esc-"] {
                    border-left: none !important;
                    background-image: none !important;
                }
            `;
            
            $('head').append(`<style id="remove-green-lines-style">${css}</style>`);
        }
        
        // Apply inline styles to elements
        $('.esc-ai-processed, .esc-ai-populated, .esc-form-field, .esc-seed-field, .esc-variety-field, .esc-description, .esc-image').each(function() {
            const $element = $(this);
            
            // Apply inline styles
            $element.attr('style', ($element.attr('style') || '') + 
                'border-left: none !important; ' +
                'background-image: none !important; ' +
                'position: relative !important;'
            );
            
            // Find all input, textarea, and select elements
            $element.find('input, textarea, select').each(function() {
                const $input = $(this);
                
                // Apply inline styles
                $input.attr('style', ($input.attr('style') || '') + 
                    'border-left: none !important; ' +
                    'background-image: none !important;'
                );
            });
        });
    }
    
    // Run immediately
    removeGreenLines();
    
    // Run after a short delay
    setTimeout(removeGreenLines, 500);
    
    // Run when any AJAX request completes
    $(document).ajaxComplete(function() {
        setTimeout(removeGreenLines, 100);
    });
    
    // Create a MutationObserver to watch for DOM changes
    const observer = new MutationObserver(function() {
        removeGreenLines();
    });
    
    // Start observing the document
    observer.observe(document.body, { childList: true, subtree: true });
});

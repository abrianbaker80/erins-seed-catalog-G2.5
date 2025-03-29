/**
 * Direct input fixes using inline styles
 */
jQuery(document).ready(function($) {
    'use strict';

    // Function to apply direct fixes to input fields
    function applyDirectFixes() {
        console.log('Applying direct input fixes');

        // Target all seed type and variety input fields
        $('#esc_seed_name, #esc_variety_name, input[id^="esc_seed_name"], input[id^="esc_variety_name"]').each(function() {
            const $input = $(this);

            // Apply inline styles directly
            $input.attr('style',
                'height: 40px !important; ' +
                'line-height: 40px !important; ' +
                'padding: 0 12px !important; ' +
                'font-size: 15px !important; ' +
                'display: flex !important; ' +
                'align-items: center !important; ' +
                'box-sizing: border-box !important; ' +
                'vertical-align: middle !important;'
            );

            // Add a class for additional CSS targeting
            $input.addClass('esc-direct-fixed');
        });

        // Target the seed type and variety fields in the initial form
        $('.esc-seed-variety-row .esc-seed-field input, .esc-seed-variety-row .esc-variety-field input').each(function() {
            const $input = $(this);

            // Apply inline styles directly
            $input.attr('style',
                'height: 40px !important; ' +
                'line-height: 40px !important; ' +
                'padding: 0 12px !important; ' +
                'font-size: 15px !important; ' +
                'display: flex !important; ' +
                'align-items: center !important; ' +
                'box-sizing: border-box !important; ' +
                'vertical-align: middle !important;'
            );

            // Add a class for additional CSS targeting
            $input.addClass('esc-direct-fixed');
        });

        // Remove green vertical lines
        removeGreenLines();
    }

    // Function to specifically remove green vertical lines
    function removeGreenLines() {
        // Target all elements that might have green vertical lines
        $('.esc-ai-processed, .esc-ai-populated, .esc-form-field, .esc-seed-field, .esc-variety-field, .esc-description, .esc-image').each(function() {
            const $element = $(this);

            // Remove the ::after pseudo-element by setting its content to none via a class
            $element.addClass('no-after-element');

            // Apply inline styles to override any ::after styling
            $element.attr('style', ($element.attr('style') || '') +
                'border-left: none !important; ' +
                'background-image: none !important; ' +
                'position: relative !important;'
            );

            // Find all input, textarea, and select elements within this element
            $element.find('input, textarea, select').each(function() {
                const $input = $(this);

                // Remove any left border
                $input.attr('style', ($input.attr('style') || '') +
                    'border-left: none !important; ' +
                    'background-image: none !important;'
                );
            });
        });

        // Add a style element to the head to override the ::after pseudo-elements
        if ($('#remove-green-lines-style').length === 0) {
            $('head').append(
                '<style id="remove-green-lines-style">' +
                '.no-after-element::after { ' +
                '    display: none !important; ' +
                '    width: 0 !important; ' +
                '    height: 0 !important; ' +
                '    background-color: transparent !important; ' +
                '    border: none !important; ' +
                '    content: none !important; ' +
                '}' +
                '</style>'
            );
        }
    }

    // Run the fixes immediately
    applyDirectFixes();

    // Run the fixes after a short delay to ensure all elements are loaded
    setTimeout(applyDirectFixes, 500);

    // Run the fixes again when the window is resized
    $(window).on('resize', applyDirectFixes);

    // Run the fixes when any AJAX request completes
    $(document).ajaxComplete(function() {
        setTimeout(applyDirectFixes, 100);
    });

    // Create a MutationObserver to watch for DOM changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                // Check if any of the added nodes are input fields or contain input fields
                for (let i = 0; i < mutation.addedNodes.length; i++) {
                    const node = mutation.addedNodes[i];
                    if (node.nodeType === 1) { // Element node
                        if (node.tagName === 'INPUT') {
                            // If the added node is an input field
                            applyDirectFixes();
                            break;
                        } else if (node.querySelector('input')) {
                            // If the added node contains input fields
                            applyDirectFixes();
                            break;
                        }
                    }
                }
            }
        });
    });

    // Start observing the document with the configured parameters
    observer.observe(document.body, { childList: true, subtree: true });
});

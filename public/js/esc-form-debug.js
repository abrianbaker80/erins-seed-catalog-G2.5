/**
 * Debug script for the refactored form
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        console.log('ESC Form Debug Script loaded');
        
        // Check if the form exists
        const $form = $('#esc-add-seed-form');
        if ($form.length) {
            console.log('Form found:', $form);
            
            // Check if required scripts are loaded
            const requiredScripts = [
                'esc-core',
                'esc-ui',
                'esc-form',
                'esc-ai',
                'esc-variety',
                'esc-image-uploader'
            ];
            
            console.log('Checking for required scripts:');
            requiredScripts.forEach(script => {
                const isLoaded = typeof window[script.replace(/-/g, '_')] !== 'undefined' || 
                                 $(`script[src*="${script}.js"]`).length > 0;
                console.log(`- ${script}: ${isLoaded ? 'Loaded' : 'Not loaded'}`);
            });
            
            // Check if required styles are loaded
            const requiredStyles = [
                'esc-design-system',
                'esc-components',
                'esc-refactored',
                'esc-variety-dropdown',
                'esc-modern-form',
                'esc-image-uploader'
            ];
            
            console.log('Checking for required styles:');
            requiredStyles.forEach(style => {
                const isLoaded = $(`link[href*="${style}.css"]`).length > 0;
                console.log(`- ${style}: ${isLoaded ? 'Loaded' : 'Not loaded'}`);
            });
            
            // Check if AJAX object is defined
            console.log('AJAX object:', typeof esc_ajax_object !== 'undefined' ? 'Defined' : 'Not defined');
            
            // Check form structure
            console.log('Form phases:');
            console.log('- AI Input phase:', $('#esc-phase-ai-input').length > 0);
            console.log('- Review & Edit phase:', $('#esc-phase-review-edit').length > 0);
            console.log('- Manual Entry phase:', $('#esc-phase-manual-entry').length > 0);
            
            // Check image uploader
            console.log('Image uploader:', $('.esc-image-uploader').length > 0);
            
            // Add a test button to the page
            $('<div class="esc-debug-panel" style="position: fixed; top: 100px; right: 20px; background: #fff; border: 1px solid #ddd; padding: 15px; z-index: 9999; box-shadow: 0 0 10px rgba(0,0,0,0.1);"><h3>Form Debug Panel</h3><button id="esc-test-form" class="button">Test Form Functionality</button></div>').appendTo('body');
            
            $('#esc-test-form').on('click', function() {
                console.log('Testing form functionality...');
                
                // Test AI input phase
                $('#esc_seed_name').val('Tomato');
                $('#esc_variety_name').val('Roma');
                
                // Trigger AI fetch
                $('#esc-ai-fetch-trigger').trigger('click');
                
                // Log the result
                setTimeout(function() {
                    console.log('AI fetch triggered, checking result...');
                    console.log('- AI loading visible:', $('.esc-ai-loading').is(':visible'));
                    console.log('- AI error visible:', $('.esc-ai-error').is(':visible'));
                    console.log('- Review phase visible:', $('#esc-phase-review-edit').is(':visible'));
                }, 1000);
            });
        } else {
            console.error('Form not found!');
            
            // Check if the shortcode is present but not rendering
            const shortcodeText = $('body').text().includes('[erins_seed_catalog_add_form_refactored]');
            if (shortcodeText) {
                console.error('Shortcode text found in page but not processed!');
                
                // Add a warning to the page
                $('<div class="esc-shortcode-error" style="background: #f8d7da; color: #721c24; padding: 15px; margin: 20px 0; border: 1px solid #f5c6cb; border-radius: 4px;"><h3>Shortcode Error</h3><p>The shortcode [erins_seed_catalog_add_form_refactored] was found in the page content but was not processed correctly. This may indicate a PHP error or missing template.</p></div>').appendTo('body');
            }
        }
    });

})(jQuery);

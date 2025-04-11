/**
 * Custom JavaScript fixes for AI Results Display
 */
jQuery(document).ready(function($) {
    'use strict';

    // Function to convert Sun Requirements to text display - DISABLED
    function convertSunRequirementsToText() {
        // Function disabled - we want to keep the original text input field
        // without any conversion or special handling
        return;
    }

    // Function to convert Seed Categories to single text display
    function convertSeedCategoriesToText() {
        // Find all seed category select fields
        $('.esc-form-field').each(function() {
            const $field = $(this);
            const $label = $field.find('label[for="esc_seed_category"]');

            if ($label.length) {
                const $select = $field.find('#esc_seed_category');

                // Create text display element if it doesn't exist
                if ($field.find('.esc-seed-category-text').length === 0) {
                    let categoryText = '';

                    // Get the selected option text
                    if ($select.val() && $select.val().length > 0) {
                        const selectedOption = $select.find('option:selected').first();
                        if (selectedOption.length) {
                            categoryText = selectedOption.text();
                        }
                    }

                    const $textDisplay = $('<div class="esc-seed-category-text"></div>').text(categoryText);
                    $select.after($textDisplay);
                }
            }
        });
    }

    // Function to rename the Save Seed button to Submit Seed
    function renameSaveButton() {
        $('.esc-button-primary').each(function() {
            const $button = $(this);
            const buttonText = $button.text().trim();

            if (buttonText === 'Save Seed' || buttonText.includes('Save')) {
                $button.text('Submit Seed');
                $button.attr('id', 'esc-submit-seed');
            }
        });
    }

    // Function to handle form submission
    function handleFormSubmission() {
        // This function is intentionally left empty as we're now handling
        // form submission through the esc-ai-results-enhanced.js file
        console.log('Form submission handled by esc-ai-results-enhanced.js');
    }

    // Function to fix text alignment in input fields
    function fixTextAlignment() {
        // Add a class to the body to help with CSS targeting
        $('body').addClass('esc-text-alignment-fixed');

        // Ensure input fields have proper height and padding
        $('.esc-seed-field input, .esc-variety-field input').css({
            'height': '40px',
            'line-height': '40px',
            'padding': '0 12px',
            'display': 'flex',
            'align-items': 'center'
        });

        // Fix for floating labels if present
        $('.esc-floating-label input').css({
            'height': '40px',
            'line-height': '40px',
            'padding': '0 12px'
        });
    }

    // Function to synchronize form fields with hidden fields
    function setupFormFieldSync() {
        // Track last values to prevent unnecessary updates
        const lastValues = {};

        // Find all input fields with data-target attribute
        $('input[data-target]').on('input', function() {
            const value = $(this).val();
            const targetId = $(this).data('target');

            // Only update if the value has changed
            if (lastValues[targetId] !== value) {
                lastValues[targetId] = value;

                // Update the hidden field
                $('#' + targetId).val(value);

                // Only log in debug mode or for significant changes
                if (value.length === 1 || value.length % 5 === 0) {
                    console.log('Updated hidden field ' + targetId + ' with value: ' + value);
                }

                // Also update any other visible fields with the same target
                $('input[data-target="' + targetId + '"]').not(this).val(value);
            }
        });

        // Initialize hidden fields with values from visible fields - only once
        $('input[data-target]').each(function() {
            const value = $(this).val();
            const targetId = $(this).data('target');

            if (value && !lastValues[targetId]) {
                lastValues[targetId] = value;
                $('#' + targetId).val(value);
                console.log('Initialized hidden field ' + targetId + ' with value: ' + value);
            }
        });
    }

    // Initialize all fixes
    function initFixes() {
        console.log('Initializing fixes...');
        convertSunRequirementsToText();
        convertSeedCategoriesToText();
        renameSaveButton();
        fixTextAlignment();
        setupFormFieldSync();

        // Wait a moment for other scripts to finish initializing
        setTimeout(function() {
            console.log('Initializing form submission handler...');
            handleFormSubmission();
            console.log('Form submission handler initialized');

            // Log if the submit button exists
            console.log('Submit button exists:', $('#esc-submit-seed').length > 0);

            // No need for a direct click handler as it's handled by esc-ai-results-enhanced.js
        }, 500);
    }

    // Run fixes when page loads
    $(document).ready(function() {
        console.log('Document ready - initializing fixes');
        initFixes();
    });

    // Also run fixes when AI results are loaded
    $(document).ajaxComplete(function(_, __, settings) {
        // Check if this is the AI search AJAX request
        if (settings.data && settings.data.includes('esc_gemini_search')) {
            console.log('AI search completed - reinitializing fixes');
            // Wait a short moment for the DOM to update
            setTimeout(function() {
                initFixes();
            }, 500);
        }
    });
});

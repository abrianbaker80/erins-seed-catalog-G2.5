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
        $(document).on('click', '#esc-submit-seed', function(e) {
            e.preventDefault();

            const $form = $(this).closest('form');
            const $submitButton = $(this);
            const $formContainer = $form.closest('.esc-container, .esc-modern-form');
            const $messageDiv = $('#esc-form-messages');

            // Clear previous messages and show loading state
            $messageDiv.empty().removeClass('success error').addClass('loading').text('Saving...').show();
            $submitButton.prop('disabled', true);

            // Serialize form data
            var formData = $form.serialize();

            // Add AJAX action and nonce
            formData += '&action=esc_add_seed&nonce=' + esc_ajax_object.nonce;

            // Submit the form via AJAX
            $.ajax({
                url: esc_ajax_object.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $messageDiv.removeClass('loading').addClass('success').text(response.data.message || 'Seed added successfully!');

                        // Reset form
                        $form[0].reset();

                        // Clear any AI results
                        $('#esc-ai-result-display').hide().empty();
                        $('#esc-ai-status').empty();

                        // Reset to initial AI search form
                        $('.esc-phase').hide();
                        $('#esc-phase-ai-input').show();

                        // Scroll to top of form to see message
                        $('html, body').animate({ scrollTop: $form.offset().top - 50 }, 500);
                    } else {
                        // Show error message
                        let errorMsg = response.data.message || 'Error adding seed.';
                        $messageDiv.removeClass('loading').addClass('error').text(errorMsg);
                        console.error('Add Seed Error:', response.data);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Show error message
                    $messageDiv.removeClass('loading').addClass('error').text('An error occurred.');
                    console.error('AJAX Error:', textStatus, errorThrown);
                },
                complete: function() {
                    // Re-enable submit button
                    $submitButton.prop('disabled', false);
                }
            });
        });
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

    // Initialize all fixes
    function initFixes() {
        convertSunRequirementsToText();
        convertSeedCategoriesToText();
        renameSaveButton();
        handleFormSubmission();
        fixTextAlignment();
    }

    // Run fixes when page loads
    initFixes();

    // Also run fixes when AI results are loaded
    $(document).ajaxComplete(function(event, xhr, settings) {
        // Check if this is the AI search AJAX request
        if (settings.data && settings.data.includes('esc_gemini_search')) {
            // Wait a short moment for the DOM to update
            setTimeout(function() {
                initFixes();
            }, 300);
        }
    });
});

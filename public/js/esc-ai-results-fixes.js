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
        // Function to process the form submission
        function processFormSubmission() {
            console.log('Processing form submission');

            const $form = $('#esc-add-seed-form');
            console.log('Form found:', $form.length > 0);

            const $submitButton = $('#esc-submit-seed');
            const $formContainer = $form.closest('.esc-container, .esc-modern-form');
            const $messageDiv = $('#esc-form-messages');
            console.log('Message div found:', $messageDiv.length > 0);

            // Check if esc_ajax_object is defined
            if (typeof esc_ajax_object === 'undefined') {
                console.error('esc_ajax_object is not defined');
                $messageDiv.removeClass('loading').addClass('error').text('Configuration error: AJAX object not defined').show();
                return;
            }
            console.log('AJAX URL:', esc_ajax_object.ajax_url);
            console.log('Nonce:', esc_ajax_object.nonce);

            // Clear previous messages and show loading state
            $messageDiv.empty().removeClass('success error').addClass('loading').text('Saving...').show();
            $submitButton.prop('disabled', true);

            // Make sure hidden fields are up to date with the latest values
            // This ensures we're submitting the most current data
            $('input[data-target]').each(function() {
                const value = $(this).val();
                const targetId = $(this).data('target');
                if (value) {
                    $('#' + targetId).val(value);
                }
            });

            // Check if seed_name is populated
            const seedNameValue = $('#esc_seed_name_hidden').val();
            console.log('Seed Name Value:', seedNameValue);

            if (!seedNameValue) {
                $messageDiv.removeClass('loading').addClass('error').text('Seed Type is required.').show();
                $submitButton.prop('disabled', false);
                return;
            }

            // Serialize form data
            var formData = $form.serialize();
            console.log('Form data:', formData);

            // Add AJAX action and nonce
            formData += '&action=esc_add_seed&nonce=' + esc_ajax_object.nonce;

            // Submit the form via AJAX
            console.log('Submitting form via AJAX...');
            $.ajax({
                url: esc_ajax_object.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX success:', response);
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
                    console.error('AJAX Error:', textStatus, errorThrown);
                    $messageDiv.removeClass('loading').addClass('error').text('An error occurred: ' + textStatus);
                },
                complete: function() {
                    // Re-enable submit button
                    $submitButton.prop('disabled', false);
                    console.log('AJAX request completed');
                }
            });
        }

        // Use multiple event handlers to ensure the button click is captured

        // 1. Direct click on the submit button
        $('#esc-submit-seed').on('click', function(e) {
            console.log('Submit button clicked directly');
            e.preventDefault();
            processFormSubmission();
        });

        // 2. Form submit event
        $('#esc-add-seed-form').on('submit', function(e) {
            console.log('Form submitted directly');
            e.preventDefault();
            processFormSubmission();
        });

        // 3. Custom event triggered by the onclick attribute
        $(document).on('esc_submit_seed_clicked', function() {
            console.log('Custom submit event triggered');
            processFormSubmission();
        });

        // 4. Direct binding with setTimeout as a fallback
        setTimeout(function() {
            $('#esc-submit-seed').off('click.fallback').on('click.fallback', function(e) {
                console.log('Fallback click handler triggered');
                e.preventDefault();
                processFormSubmission();
            });
        }, 1000);
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
        // Find all input fields with data-target attribute
        $('input[data-target]').on('input', function() {
            const value = $(this).val();
            const targetId = $(this).data('target');

            // Update the hidden field
            $('#' + targetId).val(value);
            console.log('Updated hidden field ' + targetId + ' with value: ' + value);

            // Also update any other visible fields with the same target
            $('input[data-target="' + targetId + '"]').not(this).val(value);
        });

        // Initialize hidden fields with values from visible fields
        $('input[data-target]').each(function() {
            const value = $(this).val();
            const targetId = $(this).data('target');

            if (value) {
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

            // Add a direct click handler as a fallback
            $('#esc-submit-seed').off('click.directHandler').on('click.directHandler', function(e) {
                console.log('Direct click handler triggered');
            });
        }, 500);
    }

    // Run fixes when page loads
    $(document).ready(function() {
        console.log('Document ready - initializing fixes');
        initFixes();
    });

    // Also run fixes when AI results are loaded
    $(document).ajaxComplete(function(event, xhr, settings) {
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

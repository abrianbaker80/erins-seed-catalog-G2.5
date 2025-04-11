/**
 * Enhanced JavaScript for AI Results Display
 */
jQuery(document).ready(function($) {
    'use strict';

    // Initialize enhancements
    function initEnhancements() {
        renameUIElements();
        enhanceSunRequirements();
        setupConfirmationFlow();
        improveFormFields();
        addAnimations();
    }

    // Rename UI elements
    function renameUIElements() {
        // Change "Seed Categories" to "Seed Category"
        $('label').each(function() {
            const $label = $(this);
            const text = $label.text().trim();

            if (text === 'Seed Categories') {
                $label.text('Seed Category');
            }
        });

        // Rename "Save Seed" button to "Submit Seed"
        $('.esc-button-primary').each(function() {
            const $button = $(this);
            const buttonText = $button.text().trim();

            if (buttonText === 'Save Seed') {
                $button.html('<span class="dashicons dashicons-saved"></span> Submit Seed');
                $button.attr('id', 'esc-submit-seed');
            }
        });
    }

    // Enhance sun requirements display - DISABLED to show only text input
    function enhanceSunRequirements() {
        // Function disabled - we want to keep the original text input field
        // without adding icons or enhanced UI
        return;
    }

    // Setup enhanced confirmation flow
    function setupConfirmationFlow() {
        // Create confirmation container if it doesn't exist
        if ($('.esc-confirmation-container').length === 0) {
            const confirmationHTML = `
                <div class="esc-confirmation-container">
                    <div class="esc-confirmation-message">
                        <div class="esc-confirmation-icon">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <h2 class="esc-confirmation-title">Seed Submitted Successfully!</h2>
                        <p class="esc-confirmation-text">Your seed has been added to the catalog.</p>
                        <div class="esc-confirmation-actions">
                            <button class="esc-confirmation-button esc-view-catalog">View Catalog</button>
                            <button class="esc-confirmation-button esc-add-another">Add Another Seed</button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(confirmationHTML);
        }

        // Flag to prevent duplicate submissions
        let isSubmitting = false;

        // Handle form submission
        $('#esc-submit-seed').on('click', function(e) {
            e.preventDefault();

            // Get the form
            const $form = $('#esc-add-seed-form');
            const $messageDiv = $('#esc-form-messages');

            // Prevent duplicate submissions
            if (isSubmitting) {
                console.log('Form already submitting, preventing duplicate submission');
                return;
            }

            // Make sure hidden fields are up to date with the latest values
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
                return;
            }

            // Mark as submitting
            isSubmitting = true;

            // Serialize form data
            var formData = $form.serialize();
            console.log('Form data:', formData);

            // Add AJAX action and nonce
            formData += '&action=esc_add_seed&nonce=' + esc_ajax_object.nonce;

            // Show loading state
            $messageDiv.empty().removeClass('success error').addClass('loading').text('Saving...').show();
            $(this).prop('disabled', true);

            // Submit the form via AJAX
            $.ajax({
                url: esc_ajax_object.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX success:', response);
                    if (response.success) {
                        // Hide message
                        $messageDiv.hide();

                        // Show confirmation
                        $('.esc-confirmation-container').addClass('active');

                        // Handle "Add Another Seed" button
                        $('.esc-add-another').off('click').on('click', function() {
                            // Hide confirmation
                            $('.esc-confirmation-container').removeClass('active');

                            // Reset form
                            $form[0].reset();

                            // Reset hidden fields
                            $('#esc_seed_name_hidden, #esc_variety_name_hidden').val('');

                            // Reset to initial AI search form
                            $('.esc-phase').hide();
                            $('#esc-phase-ai-input').show();

                            // Reset submitting flag
                            isSubmitting = false;

                            // Re-enable submit button
                            $('#esc-submit-seed').prop('disabled', false);
                        });

                        // Handle "View Catalog" button
                        $('.esc-view-catalog').off('click').on('click', function() {
                            // Redirect to catalog page
                            window.location.href = esc_ajax_object.catalog_url || '/';
                        });
                    } else {
                        // Show error message
                        let errorMsg = response.data.message || 'Error adding seed.';
                        $messageDiv.removeClass('loading').addClass('error').text(errorMsg);
                        console.error('Add Seed Error:', response.data);

                        // Reset submitting flag
                        isSubmitting = false;

                        // Re-enable submit button
                        $('#esc-submit-seed').prop('disabled', false);
                    }
                },
                error: function(_, textStatus, errorThrown) {
                    // Show error message
                    console.error('AJAX Error:', textStatus, errorThrown);
                    $messageDiv.removeClass('loading').addClass('error').text('An error occurred: ' + textStatus);

                    // Reset submitting flag
                    isSubmitting = false;

                    // Re-enable submit button
                    $('#esc-submit-seed').prop('disabled', false);
                }
            });
        });

        // Also handle form submit event
        $('#esc-add-seed-form').on('submit', function(e) {
            e.preventDefault();
            $('#esc-submit-seed').click();
        });
    }

    // Improve form fields
    function improveFormFields() {
        // Add placeholder text to empty fields
        $('.esc-form-field input, .esc-form-field textarea').each(function() {
            const $input = $(this);
            const $label = $input.closest('.esc-form-field').find('label');

            if (!$input.attr('placeholder') && $label.length) {
                const labelText = $label.text().trim();
                $input.attr('placeholder', `Enter ${labelText.toLowerCase()}...`);
            }
        });

        // Add subtle animation to fields when focused
        $('.esc-form-field input, .esc-form-field textarea, .esc-form-field select').on('focus', function() {
            $(this).closest('.esc-form-field').addClass('esc-field-focused');
        }).on('blur', function() {
            $(this).closest('.esc-form-field').removeClass('esc-field-focused');
        });
    }

    // Add animations
    function addAnimations() {
        // Add staggered fade-in animation to form cards
        $('.esc-form-card').each(function(index) {
            const $card = $(this);
            $card.css({
                'animation-delay': (index * 0.1) + 's',
                'animation-name': 'fadeIn',
                'animation-duration': '0.5s',
                'animation-fill-mode': 'both'
            });
        });
    }

    // Run enhancements when page loads
    initEnhancements();

    // Also run enhancements when AI results are loaded
    $(document).ajaxComplete(function(_, __, settings) {
        // Check if this is the AI search AJAX request
        if (settings.data && settings.data.includes('esc_gemini_search')) {
            // Wait a short moment for the DOM to update
            setTimeout(function() {
                initEnhancements();
            }, 300);
        }
    });
});

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

    // Enhance sun requirements display
    function enhanceSunRequirements() {
        const $sunField = $('#esc_sun_requirements, [name="sun_requirements"]').closest('.esc-form-field');
        
        if ($sunField.length) {
            // Get the current value
            const currentValue = $('#esc_sun_requirements, [name="sun_requirements"]').val();
            
            // Create the enhanced UI
            const $enhancedUI = $('<div class="esc-sun-requirements"></div>');
            
            // Add sun options
            const sunOptions = [
                { value: 'Full Sun', icon: '☀️', description: 'At least 6 hours of direct sunlight' },
                { value: 'Partial Sun', icon: '🌤️', description: '4-6 hours of direct sunlight' },
                { value: 'Partial Shade', icon: '⛅', description: '2-4 hours of direct sunlight' },
                { value: 'Full Shade', icon: '🌥️', description: 'Less than 2 hours of direct sunlight' }
            ];
            
            sunOptions.forEach(option => {
                const isSelected = currentValue && currentValue.includes(option.value);
                const $option = $(
                    `<div class="esc-sun-option ${isSelected ? 'selected' : ''}" data-value="${option.value}">
                        <span class="sun-icon">${option.icon}</span>
                        <div class="sun-name">${option.value}</div>
                        <div class="sun-description">${option.description}</div>
                    </div>`
                );
                
                $enhancedUI.append($option);
            });
            
            // Add the enhanced UI after the original input
            const $originalInput = $('#esc_sun_requirements, [name="sun_requirements"]');
            $originalInput.after($enhancedUI);
            
            // Hide the original input
            $originalInput.css('display', 'none');
            
            // Add click handler for sun options
            $('.esc-sun-option').on('click', function() {
                const $this = $(this);
                const value = $this.data('value');
                
                // Toggle selection
                $this.toggleClass('selected');
                
                // Update the hidden input value
                const selectedValues = [];
                $('.esc-sun-option.selected').each(function() {
                    selectedValues.push($(this).data('value'));
                });
                
                $originalInput.val(selectedValues.join(', '));
            });
        }
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
                        <button class="esc-confirmation-button">Add Another Seed</button>
                    </div>
                </div>
            `;
            
            $('body').append(confirmationHTML);
        }
        
        // Handle form submission
        $(document).on('submit', '#esc-add-seed-form', function(e) {
            // Note: We're not preventing default here to allow the normal form submission to proceed
            
            // Store the form for later reference
            const $form = $(this);
            
            // Show confirmation after successful submission
            $(document).ajaxComplete(function(event, xhr, settings) {
                // Check if this is the add seed AJAX request
                if (settings.data && settings.data.includes('esc_add_seed')) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            // Show confirmation
                            $('.esc-confirmation-container').addClass('active');
                            
                            // Handle "Add Another Seed" button
                            $('.esc-confirmation-button').off('click').on('click', function() {
                                // Hide confirmation
                                $('.esc-confirmation-container').removeClass('active');
                                
                                // Reset form
                                $form[0].reset();
                                
                                // Reset to initial AI search form
                                $('.esc-phase').hide();
                                $('#esc-phase-ai-input').show();
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing AJAX response:', e);
                    }
                }
            });
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
    $(document).ajaxComplete(function(event, xhr, settings) {
        // Check if this is the AI search AJAX request
        if (settings.data && settings.data.includes('esc_gemini_search')) {
            // Wait a short moment for the DOM to update
            setTimeout(function() {
                initEnhancements();
            }, 300);
        }
    });
});

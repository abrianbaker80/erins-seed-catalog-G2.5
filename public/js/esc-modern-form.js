/**
 * Modern Form JavaScript for Erin's Seed Catalog
 *
 * Handles the modern form UI and interactions.
 */
(function($) {
    'use strict';

    // Variables
    let aiPopulatedFields = {};
    let currentPhase = 'ai-input';
    let loadingStageInterval;
    let currentLoadingStage = 1;

    // Initialize
    function init() {
        // Initialize event listeners
        initEventListeners();

        // Initialize form phases
        initFormPhases();

        // Initialize floating labels
        initFloatingLabels();
    }

    // Initialize floating labels
    function initFloatingLabels() {
        console.log('Initializing floating labels');

        // Add has-value class to inputs that already have values
        $('.esc-floating-label input').each(function() {
            if ($(this).val().trim() !== '') {
                $(this).addClass('has-value');
                console.log('Added has-value class to input with value: ' + $(this).val());
            }
        });

        // Add event listeners for input changes
        $('.esc-floating-label input').off('input change blur').on('input change blur', function() {
            const value = $(this).val().trim();
            console.log('Input value changed: ' + value);

            if (value !== '') {
                $(this).addClass('has-value');
            } else {
                $(this).removeClass('has-value');
            }
        });

        // Force the label to be visible when input is focused
        $('.esc-floating-label input').off('focus').on('focus', function() {
            $(this).next('label').css('color', '#3498db');
        });

        // Reset label color on blur if empty
        $('.esc-floating-label input').off('blur').on('blur', function() {
            if ($(this).val().trim() === '') {
                $(this).next('label').css('color', '#666');
            }
        });

        // Trigger the input event on page load to set initial state
        $('.esc-floating-label input').trigger('input');

        // Add a small delay to ensure everything is properly initialized
        setTimeout(function() {
            $('.esc-floating-label input').each(function() {
                if ($(this).val().trim() !== '') {
                    $(this).addClass('has-value').trigger('input');
                }
            });
        }, 100);
    }

    // Initialize event listeners
    function initEventListeners() {
        // AI fetch trigger
        $('#esc-ai-fetch-trigger').on('click', handleAIFetchTrigger);

        // Toggle manual entry
        $('#esc-toggle-manual-entry').on('click', toggleManualEntry);
        $('#esc-back-to-ai-search').on('click', toggleAISearch);

        // Back to AI search from review
        $('#esc-back-to-ai').on('click', backToAISearch);

        // Toggle AI changes
        $('.esc-toggle-changes').on('click', toggleAIChanges);

        // Review mode toggle
        $('.esc-mode-button').on('click', switchReviewMode);

        // Card toggle
        $('.esc-card-toggle').on('click', toggleCard);

        // AI suggestions
        $('.esc-suggestion').on('click', applySuggestion);

        // Retry AI for section
        $('.esc-retry-ai button').on('click', retryAIForSection);

        // Range slider sync
        $('.esc-slider').on('input', syncRangeSlider);
        $('.esc-slider-value input').on('input', syncRangeSliderFromInput);
    }

    // Initialize form phases
    function initFormPhases() {
        // Show the active phase
        showPhase(currentPhase);
    }

    // Show a specific phase
    function showPhase(phase) {
        // Hide all phases
        $('.esc-phase').hide();

        // Show the requested phase
        switch(phase) {
            case 'ai-input':
                $('#esc-phase-ai-input').show();
                break;
            case 'review-edit':
                $('#esc-phase-review-edit').show();
                // Ensure form is properly initialized
                initReviewPhase();
                break;
            case 'manual-entry':
                $('#esc-phase-manual-entry').show();
                break;
        }

        currentPhase = phase;
    }

    // Initialize the review phase
    function initReviewPhase() {
        // Make sure all elements are properly initialized
        // This helps prevent errors when elements can't be found
        setTimeout(function() {
            // Trigger any necessary events
            $('.esc-floating-label input').trigger('input');

            // Update AI status badges
            updateAIStatusBadges();

            // Ensure proper tab is selected
            $('.esc-mode-button.active').trigger('click');
        }, 100);
    }

    // Handle AI fetch trigger
    function handleAIFetchTrigger() {
        const seedName = $('#esc_seed_name').val().trim();
        const varietyName = $('#esc_variety_name').val().trim();

        if (!seedName) {
            alert('Please enter a seed type.');
            return;
        }

        // Show loading state
        showAIStatus('loading');
        startLoadingStageAnimation();

        // Log the request parameters
        console.log('Sending AI request for:', { seed_name: seedName, variety_name: varietyName });

        // Make AJAX request to get seed info
        $.ajax({
            url: esc_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'esc_gemini_search',
                nonce: esc_ajax_object.nonce,
                seed_name: seedName,
                variety_name: varietyName
            },
            success: function(response) {
                stopLoadingStageAnimation();

                // Log the full response for debugging
                console.log('AI response received:', response);

                if (response.success) {
                    // Store the AI populated fields
                    aiPopulatedFields = response.data;

                    // Show success state
                    showAIStatus('success');

                    // Update seed name display
                    const displayName = varietyName ? seedName + ' (' + varietyName + ')' : seedName;
                    $('#esc-seed-name-display, #esc-seed-display-name').text(displayName);

                    // Ensure the review phase is visible before populating
                    // This fixes the issue with form not being found
                    $('#esc-phase-review-edit').show();

                    // Populate the review form
                    populateReviewForm(response.data);

                    // Show the review phase properly
                    setTimeout(function() {
                        showPhase('review-edit');
                    }, 1500);
                } else {
                    // Show error state
                    showAIStatus('error');
                    console.error('Error fetching seed info:', response.data ? response.data.message : 'Unknown error');

                    // Display error message to user
                    const errorMessage = response.data && response.data.message ? response.data.message : 'Unknown error occurred while fetching seed information.';
                    $('.esc-error-message h4').text('Error: Could Not Find Information');
                    $('.esc-error-message p').first().text(errorMessage);
                }
            },
            error: function(xhr, status, error) {
                stopLoadingStageAnimation();
                showAIStatus('error');
                console.error('AJAX error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);

                // Display error message to user
                $('.esc-error-message h4').text('Error: Request Failed');
                $('.esc-error-message p').first().text('There was a problem connecting to the server. Please try again later.');
            }
        });
    }

    // Show AI status
    function showAIStatus(status) {
        // Hide all status elements
        $('.esc-ai-initial, .esc-ai-loading, .esc-ai-success, .esc-ai-error').hide();

        // Show the requested status
        $('.esc-ai-' + status).show();
    }

    // Start loading stage animation
    function startLoadingStageAnimation() {
        currentLoadingStage = 1;
        updateLoadingStage();

        loadingStageInterval = setInterval(function() {
            currentLoadingStage++;
            if (currentLoadingStage > 3) {
                currentLoadingStage = 1;
            }
            updateLoadingStage();
        }, 2000);
    }

    // Stop loading stage animation
    function stopLoadingStageAnimation() {
        clearInterval(loadingStageInterval);
    }

    // Update loading stage
    function updateLoadingStage() {
        $('.esc-loading-stage').removeClass('active');
        $('.esc-loading-stage[data-stage="' + currentLoadingStage + '"]').addClass('active');
    }

    // Populate the review form
    function populateReviewForm(data) {
        // Reset form - with error checking
        // The form element is the parent form that contains all phases
        const $form = $('#esc-add-seed-form');
        if ($form.length && $form[0]) {
            // Don't reset the entire form as it would clear the seed name and variety
            // Instead, just reset the fields in the review phase
            $('#esc-phase-review-edit input:not([id="esc_seed_name_review"]):not([id="esc_variety_name_review"])').val('');
            $('#esc-phase-review-edit textarea').val('');
            $('#esc-phase-review-edit select').prop('selectedIndex', 0);
            $('#esc-phase-review-edit input[type="checkbox"]').prop('checked', false);
            $('#esc-phase-review-edit input[type="radio"]').prop('checked', false);
        } else {
            console.warn('Form element not found for reset');
        }

        // Clear previous changes list
        $('.esc-changes-list').empty();

        // Log the data we're populating
        console.log('Populating form with data:', data);

        // Check if data is valid
        if (!data || typeof data !== 'object') {
            console.error('Invalid data received for form population:', data);
            return;
        }

        // Populate fields
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                const value = data[key];

                // Skip null or undefined values
                if (value === null || value === undefined) {
                    continue;
                }

                // Find the field - try both with and without review suffix
                let $field = $('#esc_' + key + '_review');
                if (!$field.length) {
                    $field = $('#esc_' + key);
                }

                if ($field.length) {
                    console.log('Setting field', key, 'to value:', value);

                    // Set field value
                    if ($field.is('select')) {
                        $field.val(value);
                    } else if ($field.is('input[type="checkbox"]')) {
                        $field.prop('checked', value === '1' || value === true);
                    } else if ($field.is('input[type="radio"]')) {
                        $('input[name="' + $field.attr('name') + '"][value="' + value + '"]').prop('checked', true);
                    } else if ($field.is('input[type="range"]')) {
                        // For range sliders, update both the slider and the number input
                        $field.val(value);
                        const sliderId = $field.attr('id') + '_slider';
                        $('#' + sliderId).val(value);
                    } else {
                        $field.val(value);
                    }

                    // Mark as AI populated
                    $field.closest('.esc-form-field').addClass('esc-ai-populated');

                    // Ensure floating label behavior works
                    if ($field.closest('.esc-floating-label').length) {
                        $field.addClass('has-value');
                    }

                    // Add to changes list
                    addToChangesList(key, value);
                } else {
                    console.log('Field not found for key:', key);
                }
            }
        }

        // Special handling for categories if present
        if (data.suggested_term_ids && Array.isArray(data.suggested_term_ids) && data.suggested_term_ids.length > 0) {
            const $categorySelect = $('#esc_seed_category, #esc_seed_category_review');
            if ($categorySelect.length) {
                $categorySelect.val(data.suggested_term_ids);
                $categorySelect.closest('.esc-form-field').addClass('esc-ai-populated');
                addToChangesList('categories', 'Suggested categories');
            }
        }

        // Update AI status badges
        updateAIStatusBadges();

        // Populate attention needed fields
        populateAttentionNeededFields();
    }

    // Add to changes list
    function addToChangesList(key, value) {
        // Try to find the label for the field
        let fieldLabel = $('label[for="esc_' + key + '"]').text();

        // If not found, try with _review suffix
        if (!fieldLabel) {
            fieldLabel = $('label[for="esc_' + key + '_review"]').text();
        }

        // If still not found, use a formatted version of the key
        if (!fieldLabel) {
            fieldLabel = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        // Create a list item with the field label and value
        const listItem = $('<li>');
        if (typeof value === 'string' && value.length > 50) {
            // For long text, just show that it was populated
            listItem.text(fieldLabel + ': ' + 'Populated');
        } else if (value !== null && value !== undefined) {
            // For shorter values, show the actual value
            listItem.text(fieldLabel + ': ' + value);
        } else {
            // For null/undefined values, just show the field name
            listItem.text(fieldLabel);
        }

        // Add to the changes list
        $('.esc-changes-list').append(listItem);
    }

    // Update AI status badges
    function updateAIStatusBadges() {
        $('.esc-form-card').each(function() {
            const $card = $(this);
            const $fields = $card.find('.esc-form-field');
            const $populatedFields = $card.find('.esc-form-field.esc-ai-populated');

            if ($populatedFields.length === 0) {
                $card.attr('data-ai-status', 'not-populated');
                $card.find('.esc-ai-status-badge .esc-badge-text').text('Not Found');
                $card.find('.esc-ai-status-badge .dashicons')
                    .removeClass('dashicons-yes dashicons-marker')
                    .addClass('dashicons-warning');
            } else if ($populatedFields.length === $fields.length) {
                $card.attr('data-ai-status', 'fully-populated');
                $card.find('.esc-ai-status-badge .esc-badge-text').text('AI Complete');
                $card.find('.esc-ai-status-badge .dashicons')
                    .removeClass('dashicons-warning dashicons-marker')
                    .addClass('dashicons-yes');
            } else {
                $card.attr('data-ai-status', 'partially-populated');
                $card.find('.esc-ai-status-badge .esc-badge-text').text('Needs Review');
                $card.find('.esc-ai-status-badge .dashicons')
                    .removeClass('dashicons-yes dashicons-warning')
                    .addClass('dashicons-marker');
            }
        });
    }

    // Populate attention needed fields
    function populateAttentionNeededFields() {
        console.log('Populating attention needed fields');
        const $attentionFields = $('.esc-attention-fields');
        $attentionFields.empty();

        // First check if we have any cards that need attention
        const $needsAttentionCards = $('.esc-form-card[data-ai-status="not-populated"], .esc-form-card[data-ai-status="partially-populated"]');
        console.log('Found ' + $needsAttentionCards.length + ' cards needing attention');

        // If we have cards that need attention, process them
        if ($needsAttentionCards.length > 0) {
            $needsAttentionCards.each(function() {
                const $card = $(this);
                const cardTitle = $card.find('.esc-card-header h3').text();
                console.log('Processing card: ' + cardTitle);

                const $attentionCard = $('<div class="esc-attention-card"></div>');
                $attentionCard.append('<h5>' + cardTitle + '</h5>');

                const $fieldList = $('<div class="esc-attention-field-list"></div>');

                // Find fields that need attention
                const $fieldsNeedingAttention = $card.find('.esc-form-field:not(.esc-ai-populated)');
                console.log('Found ' + $fieldsNeedingAttention.length + ' fields needing attention in ' + cardTitle);

                $fieldsNeedingAttention.each(function() {
                    const $field = $(this);
                    const fieldLabel = $field.find('label').text();

                    // Special handling for radio button groups
                    if ($field.find('input[type="radio"]').length > 0) {
                        // For radio buttons, we need to handle the entire group
                        const radioName = $field.find('input[type="radio"]').first().attr('name');
                        console.log('Processing radio field: ' + fieldLabel + ' (Name: ' + radioName + ')');

                        if (fieldLabel && radioName) {
                            const $attentionField = $('<div class="esc-attention-field"></div>');
                            $attentionField.append('<label>' + fieldLabel + '</label>');

                            // Create a container for the radio buttons
                            const $radioContainer = $('<div class="esc-radio-group"></div>');

                            // Clone each radio button
                            $field.find('input[type="radio"]').each(function() {
                                const $radio = $(this);
                                const radioValue = $radio.val();
                                const radioId = $radio.attr('id') + '_attention';
                                const radioLabel = $field.find('label[for="' + $radio.attr('id') + '"]').text();

                                const $radioClone = $('<div class="esc-radio-option"></div>');
                                $radioClone.append('<input type="radio" id="' + radioId + '" name="' + radioName + '_attention" value="' + radioValue + '"' + ($radio.is(':checked') ? ' checked' : '') + '>');
                                $radioClone.append('<label for="' + radioId + '">' + radioLabel + '</label>');

                                // Sync the cloned radio with the original
                                $radioClone.find('input').on('change', function() {
                                    if ($(this).is(':checked')) {
                                        $('input[name="' + radioName + '"][value="' + radioValue + '"]').prop('checked', true).trigger('change');
                                    }
                                });

                                $radioContainer.append($radioClone);
                            });

                            $attentionField.append($radioContainer);
                            $fieldList.append($attentionField);
                        }
                    } else {
                        // Regular field handling
                        const $input = $field.find('input, textarea, select');
                        const fieldId = $input.attr('id');

                        console.log('Processing field: ' + fieldLabel + ' (ID: ' + fieldId + ')');

                        if (fieldLabel && fieldId) {
                            const $attentionField = $('<div class="esc-attention-field"></div>');
                            $attentionField.append('<label for="' + fieldId + '_attention">' + fieldLabel + '</label>');

                            // Clone the input field
                            const $inputClone = $input.clone();
                            $inputClone.attr('id', fieldId + '_attention');

                            // Make sure the clone has the current value
                            $inputClone.val($input.val());

                            // Sync the cloned field with the original
                            $inputClone.on('input change', function() {
                                $('#' + fieldId).val($(this).val()).trigger('change');
                            });

                            $attentionField.append($inputClone);
                            $fieldList.append($attentionField);
                        }
                    }
                });

                // Only add the card if it has fields
                if ($fieldList.children().length > 0) {
                    $attentionCard.append($fieldList);
                    $attentionFields.append($attentionCard);
                }
            });
        }

        // If no attention needed fields, show a message
        if ($attentionFields.children().length === 0) {
            $attentionFields.append('<p>All fields have been populated by AI. You can review them in Detailed Edit mode.</p>');
        }

        // Make sure the quick review tab is visible
        $('.esc-mode-quick').show();
    }

    // Toggle manual entry
    function toggleManualEntry(e) {
        e.preventDefault();
        showPhase('manual-entry');
    }

    // Toggle AI search
    function toggleAISearch(e) {
        e.preventDefault();
        showPhase('ai-input');
    }

    // Back to AI search from review
    function backToAISearch() {
        showPhase('ai-input');
    }

    // Toggle AI changes
    function toggleAIChanges() {
        $('.esc-changes-detail').slideToggle();
    }

    // Switch review mode
    function switchReviewMode() {
        const mode = $(this).data('mode');
        console.log('Switching to review mode: ' + mode);

        // Update active button
        $('.esc-mode-button').removeClass('active');
        $(this).addClass('active');

        // Show the selected mode
        $('.esc-review-mode').hide();
        $('.esc-mode-' + mode).show();

        // If switching to quick mode, make sure attention fields are populated
        if (mode === 'quick') {
            populateAttentionNeededFields();
        }

        // Ensure floating labels are properly initialized in the visible mode
        setTimeout(function() {
            $('.esc-mode-' + mode + ' .esc-floating-label input').each(function() {
                if ($(this).val().trim() !== '') {
                    $(this).addClass('has-value').trigger('input');
                }
            });
        }, 100);
    }

    // Toggle card
    function toggleCard() {
        const $card = $(this).closest('.esc-form-card');
        $card.find('.esc-card-content').slideToggle();
        $(this).find('.dashicons').toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
    }

    // Apply suggestion
    function applySuggestion() {
        const field = $(this).data('field');
        const value = $(this).data('value');

        $('#esc_' + field).val(value).trigger('change');
    }

    // Retry AI for section
    function retryAIForSection() {
        const section = $(this).data('section');

        // Show loading state
        const $card = $(this).closest('.esc-form-card');
        $card.find('.esc-retry-ai').hide();
        $card.find('.esc-card-content').append('<div class="esc-section-loading"><span class="dashicons dashicons-update-alt esc-spin"></span> Searching for more information...</div>');

        // TODO: Implement section-specific AI retry
        // For now, just simulate a delay and then show a message
        setTimeout(function() {
            $card.find('.esc-section-loading').remove();
            $card.find('.esc-retry-ai').show();

            // Show a message
            $card.find('.esc-card-content').append('<div class="esc-section-message">No additional information found for this section.</div>');

            // Remove the message after a few seconds
            setTimeout(function() {
                $card.find('.esc-section-message').fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }, 2000);
    }

    // Sync range slider
    function syncRangeSlider() {
        const value = $(this).val();
        const inputId = $(this).attr('id').replace('_slider', '');
        $('#' + inputId).val(value);
    }

    // Sync range slider from input
    function syncRangeSliderFromInput() {
        const value = $(this).val();
        const sliderId = $(this).attr('id') + '_slider';
        $('#' + sliderId).val(value);
    }

    // Initialize when document is ready
    $(document).ready(function() {
        init();
    });

})(jQuery);

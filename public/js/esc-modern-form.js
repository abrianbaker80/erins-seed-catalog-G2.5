/**
 * Modern Form JavaScript for Erin's Seed Catalog
 *
 * Handles the modern form UI and interactions.
 */
(function($) {
    'use strict';

    // Variables
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

        // Process all form elements with floating labels (inputs, textareas, selects)
        $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').each(function() {
            var $field = $(this);

            // Check if the field has a value
            if ($field.val() && $field.val().trim() !== '') {
                $field.addClass('has-value');
                console.log('Added has-value class to field with value: ' + $field.val());

                // Ensure the label is properly positioned
                var $label = $field.siblings('label');
                if ($label.length) {
                    $label.addClass('active');
                }
            }

            // Ensure placeholder attribute exists (critical for CSS selectors)
            if (!$field.attr('placeholder')) {
                $field.attr('placeholder', ' ');
            }
        });

        // Add event listeners for input changes
        $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').off('input change blur').on('input change blur', function() {
            var $field = $(this);
            var value = $field.val();
            var hasValue = value && value.trim() !== '';

            if (hasValue) {
                $field.addClass('has-value');
            } else {
                $field.removeClass('has-value');
            }
        });

        // Force the label to be visible when field is focused
        $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').off('focus').on('focus', function() {
            $(this).siblings('label').css('color', '#3498db');
        });

        // Reset label color on blur if empty
        $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').off('blur').on('blur', function() {
            if (!$(this).val() || $(this).val().trim() === '') {
                $(this).siblings('label').css('color', '#666');
            }
        });

        // Trigger the input event on page load to set initial state
        $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').trigger('input');

        // Add a small delay to ensure everything is properly initialized
        // This is especially important for dynamically loaded content
        setTimeout(function() {
            $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').each(function() {
                if ($(this).val() && $(this).val().trim() !== '') {
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
                // Scroll to the top of the form
                scrollToElement('#esc-add-seed-form-container', 100);
                break;
            case 'review-edit':
                $('#esc-phase-review-edit').show();
                // Ensure form is properly initialized
                initReviewPhase();
                // Scroll to the review phase with a slight delay to ensure it's visible
                setTimeout(function() {
                    scrollToElement('#esc-phase-review-edit', 80);
                }, 200);
                break;
            case 'manual-entry':
                $('#esc-phase-manual-entry').show();
                // Scroll to the manual entry phase
                scrollToElement('#esc-phase-manual-entry', 100);
                break;
        }

        currentPhase = phase;
    }

    // Helper function to scroll to an element
    function scrollToElement(selector, offset = 0) {
        const $element = $(selector);
        if ($element.length) {
            const scrollPosition = $element.offset().top - offset;
            $('html, body').animate({
                scrollTop: scrollPosition
            }, 600, 'swing');
        }
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

            // Make sure the detailed view is visible
            $('.esc-review-mode').show();
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

        // Scroll to the loading status
        scrollToElement('.esc-ai-status-container', 120);

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
                variety: varietyName
            },
            success: function(response) {
                stopLoadingStageAnimation();

                // Log the full response for debugging
                console.log('AI response received:', response);

                if (response.success) {
                    // Process the AI data - response.data contains the seed information
                    console.log('Success! Raw response:', response);

                    // Show success state
                    showAIStatus('success');

                    // Update seed name display
                    const displayName = varietyName ? seedName + ' (' + varietyName + ')' : seedName;
                    $('#esc-seed-name-display, #esc-seed-display-name').text(displayName);

                    // Ensure the review phase is visible before populating
                    $('#esc-phase-review-edit').show();
                    $('.esc-review-mode').show();

                    // Add a small delay to ensure the DOM is ready
                    setTimeout(function() {
                        try {
                            // Make sure we have the seed name in the data
                            if (!response.data.seed_name && seedName) {
                                response.data.seed_name = seedName;
                            }

                            // Special handling for variety name
                            if (varietyName) {
                                // If user entered a variety name, use it
                                response.data.variety_name = varietyName;
                                console.log('Using user-entered variety name:', varietyName);
                            }

                            // Populate the review form with the seed data
                            populateReviewForm(response.data);

                            // Switch to review phase
                            showPhase('review-edit');

                            // Ensure floating labels are properly initialized
                            $('.esc-floating-label input, .esc-floating-label textarea').each(function() {
                                if ($(this).val()) {
                                    const val = $(this).val();
                                    if (typeof val === 'string' && val.trim() !== '') {
                                        $(this).addClass('has-value');
                                    } else if (val) {
                                        $(this).addClass('has-value');
                                    }
                                }
                            });
                        } catch (error) {
                            console.error('Error populating form:', error);
                            alert('There was an error populating the form. Please try again or enter details manually.');
                        }
                    }, 100);
                } else {
                    // Show error state
                    showAIStatus('error');
                    console.error('Error fetching seed information:', response.data?.message);
                }
            },
            error: function(xhr, status, error) {
                stopLoadingStageAnimation();
                showAIStatus('error');
                console.error('AJAX Error:', status, error);
            }
        });
    }

    // This function is no longer used - we use populateReviewForm instead
    function fillFormFields(data) {
        console.log('fillFormFields is deprecated - using populateReviewForm instead');
        populateReviewForm(data);
    }

    // Show AI status
    function showAIStatus(status) {
        // Hide all status elements
        $('.esc-ai-initial, .esc-ai-loading, .esc-ai-success, .esc-ai-error').hide();

        // Show the requested status
        $('.esc-ai-' + status).show();

        // If showing error or success, scroll to make it visible
        if (status === 'error' || status === 'success') {
            setTimeout(function() {
                scrollToElement('.esc-ai-status-container', 120);
            }, 100);
        }
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
        // Add more detailed logging
        console.log('Starting form population process...');
        console.log('Form data to populate:', data);

        // Make sure the review phase is visible
        $('#esc-phase-review-edit').show();
        $('.esc-review-mode').show();

        // Scroll to the review phase with a slight delay to ensure it's visible
        setTimeout(function() {
            scrollToElement('#esc-phase-review-edit', 80);
        }, 300);

        // Reset form - with error checking
        // The form element is the parent form that contains all phases
        const $form = $('#esc-add-seed-form');
        if ($form.length && $form[0]) {
            console.log('Form found, resetting fields...');
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

        // Check if data is valid
        if (!data || typeof data !== 'object') {
            console.error('Invalid data received for form population:', data);
            return;
        }

        // Populate fields in all form phases (review and manual)
        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                const value = data[key];
                console.log('Processing field:', key, 'with value:', value);

                // Special handling for image_url
                if (key === 'image_url' && value) {
                    console.log('Setting image URL:', value);
                    $('#esc_image_url').val(value);
                    // Show the image preview
                    $('.esc-image-preview').show();
                    $('.esc-preview-image').attr('src', value);
                    $('.esc-dropzone').addClass('has-image');
                    $('.esc-form-field:has(#esc_image_url)').addClass('esc-ai-populated');
                    addToChangesList('image', 'Image URL');
                    continue;
                }

                // Handle null, undefined, or string "null" values
                if (value === null || value === undefined || value === 'null' || value === "null") {
                    console.log('Null/empty value for field:', key);
                    // For null values, clear the field but still mark it as processed by AI
                    let $field = $('#esc_' + key + '_review');
                    if (!$field.length) {
                        $field = $('#esc_' + key);
                    }
                    if (!$field.length) {
                        $field = $('#esc_' + key + '_manual');
                    }

                    if ($field.length) {
                        console.log('Found field for null value:', $field.attr('id'));
                        // Clear the field
                        if ($field.is('select')) {
                            $field.val('');
                        } else if ($field.is('input[type="checkbox"]')) {
                            $field.prop('checked', false);
                        } else if ($field.is('input[type="radio"]')) {
                            $('input[name="' + $field.attr('name') + '"]').prop('checked', false);
                        } else {
                            $field.val('');
                        }

                        // Still mark as processed by AI
                        $field.closest('.esc-form-field').addClass('esc-ai-processed');
                    } else {
                        console.log('No field found for null value:', key);
                    }

                    continue;
                }

                // Try to find the field in all possible locations
                // 1. Review form fields
                let $field = $('#esc_' + key + '_review');
                if (!$field.length) {
                    // 2. Main form fields without suffix
                    $field = $('#esc_' + key);
                }

                // Add this field to the changes list if it's not a system field
                if (!key.startsWith('esc_') && !key.startsWith('suggested_') && key !== 'seed_name' && key !== 'variety_name') {
                    addToChangesList(key, key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
                }
                if (!$field.length) {
                    // 3. Manual form fields
                    $field = $('#esc_' + key + '_manual');
                }

                console.log('Looking for field:', key, 'Found:', $field.length ? $field.attr('id') : 'Not found');

                if ($field.length) {
                    console.log('Setting field', key, 'to value:', value);

                    try {
                        // Set field value
                        if ($field.is('select')) {
                            $field.val(value).trigger('change');
                            console.log('Set select field value:', value);
                        } else if ($field.is('input[type="checkbox"]')) {
                            const isChecked = value === '1' || value === true || value === 'true';
                            $field.prop('checked', isChecked);
                            console.log('Set checkbox field value:', isChecked);
                        } else if ($field.is('input[type="radio"]')) {
                            // For radio buttons, we need to check the one with the matching value
                            $('input[name="' + $field.attr('name') + '"][value="' + value + '"]').prop('checked', true);
                            console.log('Set radio field value:', value);
                        } else if ($field.is('input[type="range"]')) {
                            // For range sliders, update both the slider and the number input
                            $field.val(value);
                            const sliderId = $field.attr('id') + '_slider';
                            $('#' + sliderId).val(value);
                            console.log('Set range field value:', value);
                        } else if ($field.is('textarea')) {
                            $field.val(value).trigger('input');
                            console.log('Set textarea field value:', value);
                        } else {
                            $field.val(value).trigger('input');
                            console.log('Set input field value:', value);
                        }

                        // Mark as AI populated
                        $field.closest('.esc-form-field').addClass('esc-ai-populated');

                        // Ensure floating label behavior works
                        if ($field.closest('.esc-floating-label').length) {
                            $field.addClass('has-value');

                            // Make sure the field has a placeholder attribute
                            if (!$field.attr('placeholder')) {
                                $field.attr('placeholder', ' ');
                            }
                        }

                        // Add to changes list
                        addToChangesList(key, value);

                        // Also populate the corresponding field in other form phases
                        // This ensures all instances of the field are populated
                        if ($field.attr('id').includes('_review')) {
                            // If we found a review field, also populate the manual field
                            const manualId = $field.attr('id').replace('_review', '_manual');
                            const $manualField = $('#' + manualId);
                            if ($manualField.length) {
                                if ($manualField.is('select')) {
                                    $manualField.val(value).trigger('change');
                                } else if ($manualField.is('input[type="checkbox"]')) {
                                    $manualField.prop('checked', value === '1' || value === true || value === 'true');
                                } else if ($manualField.is('textarea')) {
                                    $manualField.val(value).trigger('input');
                                } else {
                                    $manualField.val(value).trigger('input');
                                }
                                $manualField.closest('.esc-form-field').addClass('esc-ai-populated');
                            }
                        } else if ($field.attr('id').includes('_manual')) {
                            // If we found a manual field, also populate the review field
                            const reviewId = $field.attr('id').replace('_manual', '_review');
                            const $reviewField = $('#' + reviewId);
                            if ($reviewField.length) {
                                if ($reviewField.is('select')) {
                                    $reviewField.val(value).trigger('change');
                                } else if ($reviewField.is('input[type="checkbox"]')) {
                                    $reviewField.prop('checked', value === '1' || value === true || value === 'true');
                                } else if ($reviewField.is('textarea')) {
                                    $reviewField.val(value).trigger('input');
                                } else {
                                    $reviewField.val(value).trigger('input');
                                }
                                $reviewField.closest('.esc-form-field').addClass('esc-ai-populated');
                            }
                        }
                    } catch (error) {
                        console.error('Error setting field value:', error);
                    }
                } else {
                    console.log('Field not found for key:', key);
                }
            }
        }

        // Special handling for sunlight radio buttons
        if (data.sunlight) {
            // First uncheck all options
            $('input[name="sunlight"]').prop('checked', false);

            // Then check only the one that matches exactly
            const sunlightValue = data.sunlight.trim();
            $('input[name="sunlight"][value="' + sunlightValue + '"]').prop('checked', true);

            // If none match exactly, check the first one
            if ($('input[name="sunlight"]:checked').length === 0 && $('input[name="sunlight"]').length > 0) {
                $('input[name="sunlight"]:first').prop('checked', true);
            }

            $('input[name="sunlight"]').closest('.esc-form-field').addClass('esc-ai-populated');
            addToChangesList('sunlight', $('input[name="sunlight"]:checked').val() || sunlightValue);
        }

        // Special handling for sowing method dropdown
        if (data.sowing_method) {
            $('#esc_sowing_method, #esc_sowing_method_review, #esc_sowing_method_manual').val(data.sowing_method);
            $('#esc_sowing_method, #esc_sowing_method_review, #esc_sowing_method_manual').closest('.esc-form-field').addClass('esc-ai-populated');
            addToChangesList('sowing_method', data.sowing_method);
        }

        // Special handling for container_suitability - default to No if null or not true
        if (data.container_suitability === true || data.container_suitability === 1 || data.container_suitability === '1') {
            // Only set to Yes if explicitly true
            $('#esc_container_suitability, #esc_container_suitability_review, #esc_container_suitability_manual').val('1');
            addToChangesList('container_suitability', 'Yes');
        } else {
            // For null, false, or any other value, set to No
            $('#esc_container_suitability, #esc_container_suitability_review, #esc_container_suitability_manual').val('0');
            addToChangesList('container_suitability', 'No');
        }
        $('#esc_container_suitability, #esc_container_suitability_review, #esc_container_suitability_manual').closest('.esc-form-field').addClass('esc-ai-populated');

        // Special handling for cut_flower_potential - default to No if null or not true
        if (data.cut_flower_potential === true || data.cut_flower_potential === 1 || data.cut_flower_potential === '1') {
            // Only set to Yes if explicitly true
            $('#esc_cut_flower_potential, #esc_cut_flower_potential_review, #esc_cut_flower_potential_manual').val('1');
            addToChangesList('cut_flower_potential', 'Yes');
        } else {
            // For null, false, or any other value, set to No
            $('#esc_cut_flower_potential, #esc_cut_flower_potential_review, #esc_cut_flower_potential_manual').val('0');
            addToChangesList('cut_flower_potential', 'No');
        }
        $('#esc_cut_flower_potential, #esc_cut_flower_potential_review, #esc_cut_flower_potential_manual').closest('.esc-form-field').addClass('esc-ai-populated');

        // Special handling for pollinator information
        if (data.pollinator_info) {
            $('#esc_pollinator_info').val(data.pollinator_info).trigger('input');
            $('#esc_pollinator_info').closest('.esc-form-field').addClass('esc-ai-populated');
            addToChangesList('pollinator_info', 'Pollinator information added');
        }

        // Special handling for categories if present
        if (data.suggested_term_ids && Array.isArray(data.suggested_term_ids) && data.suggested_term_ids.length > 0) {
            console.log('Setting categories from suggested_term_ids:', data.suggested_term_ids);
            const $categorySelect = $('#esc_seed_category, #esc_seed_category_review, #esc_seed_category_manual');
            if ($categorySelect.length) {
                // Only select the first category from the suggested list
                const firstCategory = [data.suggested_term_ids[0]];
                $categorySelect.val(firstCategory).trigger('change');
                $categorySelect.closest('.esc-form-field').addClass('esc-ai-populated');
                addToChangesList('categories', 'Selected category from AI');

                // Add a visual indicator that this was AI-selected
                $categorySelect.closest('.esc-form-field').find('label:not(:has(.esc-ai-badge))').append(' <span class="esc-ai-badge">AI</span>');
            }
        }

        // Special handling for category suggestion from text
        if (data.esc_seed_category_suggestion && typeof data.esc_seed_category_suggestion === 'string') {
            console.log('Processing category suggestion:', data.esc_seed_category_suggestion);
            // Try to find matching categories in the dropdown
            const suggestions = data.esc_seed_category_suggestion.split(',').map(s => s.trim());
            const $categorySelect = $('#esc_seed_category, #esc_seed_category_review, #esc_seed_category_manual');

            if ($categorySelect.length) {
                // Get all available options
                let selectedValue = null;
                $categorySelect.find('option').each(function() {
                    if (selectedValue) return; // Only select the first match

                    const optionText = $(this).text().toLowerCase();
                    // Check if any suggestion matches this option
                    for (const suggestion of suggestions) {
                        if (optionText.includes(suggestion.toLowerCase())) {
                            selectedValue = $(this).val();
                            console.log('Found matching category:', optionText, 'for suggestion:', suggestion);
                            break;
                        }
                    }
                });

                if (selectedValue) {
                    $categorySelect.val([selectedValue]).trigger('change');
                    $categorySelect.closest('.esc-form-field').addClass('esc-ai-populated');
                    addToChangesList('categories', 'Category from AI suggestion');
                }
            }
        }

        // Special handling for seed_name
        if (data.seed_name) {
            console.log('Setting seed name:', data.seed_name);
            $('#esc_seed_name_review').val(data.seed_name).trigger('input');
            $('#esc_seed_name_review').closest('.esc-form-field').addClass('esc-ai-populated');
            $('#esc_seed_name_review').closest('.esc-form-field').find('label:not(:has(.esc-ai-badge))').append(' <span class="esc-ai-badge">AI</span>');
            addToChangesList('seed_name', 'Seed name');
        }

        // Special handling for variety_name
        if (data.variety_name) {
            // Make sure variety_name is not the same as seed_name (which can happen with some AI responses)
            if (data.seed_name && data.variety_name === data.seed_name) {
                console.log('Variety name is same as seed name, not using:', data.variety_name);
                // Don't use the variety name if it's the same as the seed name
            } else {
                console.log('Setting variety name:', data.variety_name);
                $('#esc_variety_name_review').val(data.variety_name).trigger('input');
                $('#esc_variety_name_review').closest('.esc-form-field').addClass('esc-ai-populated');
                $('#esc_variety_name_review').closest('.esc-form-field').find('label:not(:has(.esc-ai-badge))').append(' <span class="esc-ai-badge">AI</span>');
                addToChangesList('variety_name', 'Variety name');
            }
        }

        // Update AI status badges
        updateAIStatusBadges();
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
        try {
            $('.esc-form-card').each(function() {
                const $card = $(this);
                const $fields = $card.find('.esc-form-field');

                // First mark fields with values as populated
                $fields.each(function() {
                    try {
                        const $field = $(this);
                        const $input = $field.find('input, textarea, select');

                        // Skip if already marked as populated
                        if ($field.hasClass('esc-ai-populated')) {
                            return;
                        }

                        // Check if the field has a value
                        if ($input.is('input[type="radio"]')) {
                            // For radio buttons, check if any in the group is checked
                            const radioName = $input.attr('name');
                            if ($('input[name="' + radioName + '"]:checked').length > 0) {
                                $field.addClass('esc-ai-populated');
                            }
                        } else if ($input.length && $input.val()) {
                            const val = $input.val();
                            if (typeof val === 'string' && val.trim() !== '') {
                                $field.addClass('esc-ai-populated');
                            } else if (val) {
                                $field.addClass('esc-ai-populated');
                            }
                        }
                    } catch (e) {
                        console.warn('Error processing field:', e);
                    }
                });

                // Now count populated fields
                const $populatedFields = $card.find('.esc-form-field.esc-ai-populated');
                console.log('Card:', $card.find('.esc-card-header h3').text(),
                          'Fields:', $fields.length,
                          'Populated:', $populatedFields.length);

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
        } catch (error) {
            console.error('Error updating AI status badges:', error);
        }
    }

    // Removed populateAISummary function as we no longer have a quick review mode

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

    // Removed switchReviewMode function as we no longer have mode switching

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

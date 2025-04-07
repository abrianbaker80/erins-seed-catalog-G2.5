/**
 * Modern JavaScript for Erin's Seed Catalog
 * Enhanced for better user experience and visual appeal
 */
(function($) {
    'use strict';

    // Variables
    let currentPhase = 'ai-input';
    let loadingStageInterval;
    let currentLoadingStage = 1;
    let mutationObserver = null;

    // Initialize
    function init() {
        // Create modern UI structure if it doesn't exist
        createModernUIStructure();

        // Initialize event listeners
        initEventListeners();

        // Initialize form phases
        initFormPhases();

        // Initialize floating labels
        initFloatingLabels();
    }

    // Create modern UI structure
    function createModernUIStructure() {
        // Only create the structure if it doesn't already exist
        if ($('.esc-form-header').length === 0) {
            // Wrap the existing form in our new structure
            const $form = $('#esc-add-seed-form-container');

            if ($form.length) {
                // Add the header
                $form.prepend(`
                    <div class="esc-form-header">
                        <h2>Add New Seed to Catalog</h2>
                        <p>Use AI to automatically fill in seed details or enter them manually</p>
                    </div>
                `);

                // Wrap the AI input phase content
                const $aiPhase = $('#esc-phase-ai-input');
                if ($aiPhase.length) {
                    $aiPhase.wrapInner('<div class="esc-form-body"></div>');

                    // Add the AI info box if it doesn't exist
                    if ($aiPhase.find('.esc-ai-info').length === 0) {
                        $aiPhase.find('.esc-form-body').prepend(`
                            <div class="esc-ai-info">
                                <p>Enter the seed type and variety to automatically retrieve detailed information using AI.</p>
                            </div>
                        `);
                    }

                    // Style the seed and variety inputs
                    const $seedInput = $aiPhase.find('#esc_seed_name');
                    const $varietyInput = $aiPhase.find('#esc_variety_name');

                    if ($seedInput.length && $varietyInput.length) {
                        // Get the existing labels
                        const seedLabel = $seedInput.siblings('label').text() || 'Seed Type *';
                        const varietyLabel = $varietyInput.siblings('label').text() || 'Variety (Optional)';

                        // Get the existing help text
                        const seedHelp = $seedInput.siblings('.esc-field-help').text() || 'The main name of the seed';
                        const varietyHelp = $varietyInput.siblings('.esc-field-help').text() || 'Specific variety if known';

                        // Create the new structure
                        const $seedVarietyRow = $(`
                            <div class="esc-seed-variety-row">
                                <div class="esc-field-group">
                                    <label for="esc_seed_name">${seedLabel}</label>
                                    <div class="esc-field-help">${seedHelp}</div>
                                </div>
                                <div class="esc-field-group">
                                    <label for="esc_variety_name">${varietyLabel}</label>
                                    <div class="esc-field-help">${varietyHelp}</div>
                                </div>
                            </div>
                        `);

                        // Move the inputs to the new structure
                        $seedInput.closest('.esc-floating-label, .esc-form-field').before($seedVarietyRow);
                        $seedVarietyRow.find('.esc-field-group').eq(0).prepend($seedInput);
                        $seedVarietyRow.find('.esc-field-group').eq(1).prepend($varietyInput);

                        // Remove the old containers
                        $seedInput.closest('.esc-floating-label, .esc-form-field').remove();
                        $varietyInput.closest('.esc-floating-label, .esc-form-field').remove();
                    }

                    // Style the AI button
                    const $aiButton = $aiPhase.find('#esc-ai-fetch-trigger');
                    if ($aiButton.length) {
                        $aiButton.addClass('esc-ai-button');

                        // Add icon to the button if it doesn't have one
                        if (!$aiButton.find('svg').length) {
                            $aiButton.html(`
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px;">
                                    <path d="M12 16.99V17M12 7V14M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Generate Seed Details with AI
                            `);
                        }
                    }

                    // Style the manual entry link
                    const $manualLink = $aiPhase.find('#esc-toggle-manual-entry');
                    if ($manualLink.length) {
                        $manualLink.addClass('esc-manual-link');
                    }
                }

                // Add the footer
                if ($form.find('.esc-form-footer').length === 0) {
                    $form.append(`
                        <div class="esc-form-footer">
                            <button class="esc-back-button" id="esc-back-to-ai-search">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px;">
                                    <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Back
                            </button>

                            <button class="esc-submit-button" id="esc-submit-seed">
                                Submit Seed
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left: 8px;">
                                    <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    `);

                    // Move the existing back button functionality to our new button
                    const $oldBackButton = $('.esc-back-button:not(#esc-back-to-ai-search)');
                    if ($oldBackButton.length) {
                        const oldBackClick = $oldBackButton.attr('onclick');
                        if (oldBackClick) {
                            $('#esc-back-to-ai-search').attr('onclick', oldBackClick);
                        }
                        $oldBackButton.hide();
                    }

                    // Move the existing submit button functionality to our new button
                    const $oldSubmitButton = $('.esc-submit-button:not(#esc-submit-seed)');
                    if ($oldSubmitButton.length) {
                        const oldSubmitClick = $oldSubmitButton.attr('onclick');
                        if (oldSubmitClick) {
                            $('#esc-submit-seed').attr('onclick', oldSubmitClick);
                        }
                        $oldSubmitButton.hide();
                    }
                }
            }
        }
    }

    // Initialize floating labels
    function initFloatingLabels() {
        console.log('Initializing floating labels');

        // Process all form elements with floating labels
        $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').each(function() {
            var $field = $(this);

            // Check if the field has a value
            if ($field.val() && $field.val().trim() !== '') {
                $field.addClass('has-value');
            }

            // Ensure placeholder attribute exists
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
        // Apply modern styling to the review phase
        applyModernReviewStyling();

        // Make sure all elements are properly initialized
        setTimeout(function() {
            // Trigger any necessary events
            $('.esc-floating-label input').trigger('input');

            // Update AI status badges
            updateAIStatusBadges();

            // Make sure the detailed view is visible
            $('.esc-review-mode').show();
        }, 100);
    }

    // Apply modern styling to the review phase
    function applyModernReviewStyling() {
        const $reviewPhase = $('#esc-phase-review-edit');

        if ($reviewPhase.length && !$reviewPhase.find('.esc-form-body').length) {
            // Wrap the content in a form body
            $reviewPhase.wrapInner('<div class="esc-form-body"></div>');

            // Add a header to each card
            $reviewPhase.find('.esc-form-card').each(function() {
                const $card = $(this);

                // If the card doesn't have a proper header, add one
                if (!$card.find('.esc-card-header h3').length) {
                    const cardTitle = $card.find('h3, h4').first().text() || 'Seed Information';

                    // Create a header if it doesn't exist
                    if (!$card.find('.esc-card-header').length) {
                        $card.prepend(`
                            <div class="esc-card-header">
                                <h3>${cardTitle}</h3>
                                <div class="esc-ai-status-badge">
                                    <span class="dashicons dashicons-marker"></span>
                                    <span class="esc-badge-text">Needs Review</span>
                                </div>
                            </div>
                        `);
                    }
                }

                // Ensure the content is wrapped in a card content div
                if (!$card.find('.esc-card-content').length) {
                    const $content = $card.contents().not('.esc-card-header');
                    $content.wrapAll('<div class="esc-card-content"></div>');
                }
            });

            // Add a success message at the top of the review phase
            if (!$reviewPhase.find('.esc-review-success-message').length) {
                $reviewPhase.find('.esc-form-body').prepend(`
                    <div class="esc-review-success-message">
                        <div class="esc-success-icon">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572" stroke="#2a9d8f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M22 4L12 14.01L9 11.01" stroke="#2a9d8f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h3>Seed Information Found!</h3>
                        <p>We've gathered information about <span id="esc-seed-display-name"></span>. Please review and edit the details below before submitting.</p>
                    </div>
                `);
            }

            // Style the form fields
            $reviewPhase.find('.esc-form-field').each(function() {
                const $field = $(this);

                // Skip if already styled
                if ($field.hasClass('esc-field-group')) {
                    return;
                }

                // Add the field group class
                $field.addClass('esc-field-group');

                // Style the label
                const $label = $field.find('label');
                if ($label.length) {
                    $label.addClass('esc-field-label');
                }

                // Style the input
                const $input = $field.find('input, textarea, select');
                if ($input.length) {
                    $input.addClass('esc-field-input');
                }
            });
        }
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
        console.log('Sending AI request for:', { seed_name: seedName, variety: varietyName });

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

    // Show AI status
    function showAIStatus(status) {
        // Create modern status containers if they don't exist
        createModernStatusContainers();

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

    // Create modern status containers
    function createModernStatusContainers() {
        const $statusContainer = $('.esc-ai-status-container');

        if ($statusContainer.length) {
            // Update loading container
            const $loadingContainer = $statusContainer.find('.esc-ai-loading');
            if ($loadingContainer.length && !$loadingContainer.find('.esc-loading-icon').length) {
                $loadingContainer.html(`
                    <div class="esc-loading-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2Z" stroke="#2a9d8f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="60 15" class="esc-loading-circle"/>
                        </svg>
                    </div>
                    <h3>Searching for Seed Information</h3>
                    <p>Our AI is gathering detailed information about your seeds...</p>
                    <div class="esc-loading-stages">
                        <div class="esc-loading-stage" data-stage="1">Analyzing seed type and variety...</div>
                        <div class="esc-loading-stage" data-stage="2">Gathering growing information...</div>
                        <div class="esc-loading-stage" data-stage="3">Compiling seed details...</div>
                    </div>
                `);

                // Add animation for the loading circle
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes rotate {
                        from { transform: rotate(0deg); }
                        to { transform: rotate(360deg); }
                    }
                    .esc-loading-circle {
                        animation: rotate 2s linear infinite;
                        transform-origin: center;
                    }
                `;
                document.head.appendChild(style);
            }

            // Update success container
            const $successContainer = $statusContainer.find('.esc-ai-success');
            if ($successContainer.length && !$successContainer.find('.esc-success-icon').length) {
                $successContainer.html(`
                    <div class="esc-success-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 11.0857V12.0057C21.9988 14.1621 21.3005 16.2604 20.0093 17.9875C18.7182 19.7147 16.9033 20.9782 14.8354 21.5896C12.7674 22.201 10.5573 22.1276 8.53447 21.3803C6.51168 20.633 4.78465 19.2518 3.61096 17.4428C2.43727 15.6338 1.87979 13.4938 2.02168 11.342C2.16356 9.19029 2.99721 7.14205 4.39828 5.5028C5.79935 3.86354 7.69279 2.72111 9.79619 2.24587C11.8996 1.77063 14.1003 1.98806 16.07 2.86572" stroke="#2a9d8f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 4L12 14.01L9 11.01" stroke="#2a9d8f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3>Seed Information Found!</h3>
                    <p>We've successfully gathered information about your seeds. Review and edit the details below.</p>
                `);
            }

            // Update error container
            const $errorContainer = $statusContainer.find('.esc-ai-error');
            if ($errorContainer.length && !$errorContainer.find('.esc-error-icon').length) {
                $errorContainer.html(`
                    <div class="esc-error-icon">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z" stroke="#e53e3e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3>Error Fetching Seed Information</h3>
                    <p>We couldn't find information about these seeds. Please try again with more specific details or enter the information manually.</p>
                    <button id="esc-toggle-manual-entry-error" class="esc-manual-button">Enter Details Manually</button>
                `);

                // Add event listener for the manual entry button in error state
                $('#esc-toggle-manual-entry-error').off('click').on('click', toggleManualEntry);
            }
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
                    } catch (error) {
                        console.error('Error setting field value:', error);
                    }
                } else {
                    console.log('Field not found for key:', key);
                }
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

        // Simulate a delay and then show a message
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

    // Clean up function to disconnect MutationObserver
    function cleanup() {
        if (mutationObserver) {
            mutationObserver.disconnect();
            mutationObserver = null;
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        init();

        // Clean up when navigating away from the page
        $(window).on('beforeunload', cleanup);
    });

})(jQuery);

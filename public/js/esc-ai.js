/**
 * Erin's Seed Catalog - AI Module
 *
 * Handles all AI-related functionality including:
 * - Gemini API integration
 * - AI result processing
 * - Confidence indicators
 * - AI loading states
 */

// Add the AI module to our ESC namespace
ESC.AI = (function($) {
    'use strict';

    // Private variables
    let _initialized = false;
    let _aiState = {
        isLoading: false,
        currentStage: 1,
        totalStages: 3,
        results: null,
        error: null
    };

    // Private methods
    function _initAIHandlers() {
        ESC.log('Initializing AI handlers');

        // AI fetch trigger button
        $('#esc-ai-fetch-trigger').on('click', function() {
            _handleAIFetchTrigger();
        });

        // Retry AI for specific sections
        $('.esc-retry-ai button').on('click', function() {
            const section = $(this).data('section');
            _retryAIForSection(section);
        });

        // Toggle AI changes summary
        $('.esc-toggle-changes').on('click', function() {
            $('.esc-changes-detail').slideToggle(300);
        });

        // Back to AI search button
        $('#esc-back-to-ai').on('click', function() {
            if (typeof ESC.UI.showPhase === 'function') {
                ESC.UI.showPhase('esc-phase-ai-input');
            } else {
                $('.esc-phase').hide();
                $('#esc-phase-ai-input').show();
            }
        });

        // Manual entry toggle
        $('#esc-toggle-manual-entry, #esc-back-to-ai-search').on('click', function(e) {
            e.preventDefault();
            if ($(this).attr('id') === 'esc-toggle-manual-entry') {
                if (typeof ESC.UI.showPhase === 'function') {
                    ESC.UI.showPhase('esc-phase-manual-entry');
                } else {
                    $('.esc-phase').hide();
                    $('#esc-phase-manual-entry').show();
                }
            } else {
                if (typeof ESC.UI.showPhase === 'function') {
                    ESC.UI.showPhase('esc-phase-ai-input');
                } else {
                    $('.esc-phase').hide();
                    $('#esc-phase-ai-input').show();
                }
            }
        });
    }

    function _handleAIFetchTrigger() {
        ESC.log('AI fetch triggered');

        // Get seed name and variety
        const seedName = $('#esc_seed_name').val().trim();
        const varietyName = $('#esc_variety_name').val().trim();

        // Validate input
        if (!seedName) {
            _showError('Please enter a seed type.');
            return;
        }

        // Show loading state
        _showLoadingState();

        // Make AJAX request to get seed info
        $.ajax({
            url: ESC.getConfig().ajaxUrl,
            type: 'POST',
            data: {
                action: 'esc_gemini_search',
                nonce: ESC.getConfig().nonce,
                seed_name: seedName,
                variety: varietyName
            },
            dataType: 'json',
            success: function(response) {
                ESC.log('AI fetch response:', response);

                if (response.success && response.data) {
                    // Process and display results
                    _processAIResults(response.data, seedName, varietyName);
                } else {
                    // Show error
                    _showError(response.data && response.data.message
                        ? response.data.message
                        : 'Error fetching seed information.');
                }
            },
            error: function(xhr, status, error) {
                ESC.error('AI fetch error:', status, error);
                _showError('Error communicating with the server.');
            }
        });
    }

    function _showLoadingState() {
        ESC.log('Showing AI loading state');

        // Update AI state
        _aiState.isLoading = true;
        _aiState.currentStage = 1;
        _aiState.error = null;

        // Hide other states
        $('.esc-ai-initial, .esc-ai-success, .esc-ai-error').hide();

        // Show loading state
        $('.esc-ai-loading').show();

        // Start loading animation
        _startLoadingAnimation();

        // Scroll to loading state
        _scrollToElement('.esc-ai-status-container');
    }

    function _startLoadingAnimation() {
        ESC.log('Starting loading animation');

        // Reset stages
        $('.esc-loading__stage').removeClass('esc-loading__stage--active');
        $('.esc-loading__stage[data-stage="1"]').addClass('esc-loading__stage--active');

        // Set initial stage
        _aiState.currentStage = 1;

        // Progress through stages
        _progressLoadingStage();
    }

    function _progressLoadingStage() {
        // If not loading anymore, stop
        if (!_aiState.isLoading) {
            return;
        }

        // Get current and next stages
        const currentStage = _aiState.currentStage;
        const nextStage = currentStage + 1;

        // If we've reached the end, loop back
        if (nextStage > _aiState.totalStages) {
            // Keep the last stage active until results arrive
            return;
        }

        // Update active stage after delay
        setTimeout(function() {
            $('.esc-loading__stage').removeClass('esc-loading__stage--active');
            $('.esc-loading__stage[data-stage="' + nextStage + '"]').addClass('esc-loading__stage--active');

            // Update state
            _aiState.currentStage = nextStage;

            // Continue progression
            _progressLoadingStage();
        }, 1500); // 1.5 seconds per stage
    }

    function _processAIResults(results, seedName, varietyName) {
        ESC.log('Processing AI results');

        // Store results
        _aiState.results = results;
        _aiState.isLoading = false;

        // Update hidden fields
        $('#esc_seed_name_hidden').val(seedName);
        $('#esc_variety_name_hidden').val(varietyName || '');

        // Populate form fields
        _populateFormFields(results);

        // Show success state
        _showSuccessState(seedName, varietyName);

        // Generate changes summary
        _generateChangesSummary(results);

        // Add confidence indicators
        _addConfidenceIndicators(results);

        // Trigger AI results event
        $(document).trigger('esc:aiResultsReceived', [results]);
    }

    function _populateFormFields(results) {
        ESC.log('Populating form fields with AI results');

        // Basic information
        $('#esc_seed_name_review').val(results.seed_name || $('#esc_seed_name').val());
        $('#esc_variety_name_review').val(results.variety_name || $('#esc_variety_name').val());
        $('#esc_description').val(results.description || '');

        // If image source URL is provided, show the manual download instructions
        if (results.image_source_url) {
            // Show manual download instructions
            _showManualDownloadInstructions(results.image_source_url);
        }

        // Plant characteristics
        $('#esc_plant_type').val(results.plant_type || '');
        $('#esc_growth_habit').val(results.growth_habit || '');
        $('#esc_days_to_maturity').val(results.days_to_maturity || '');
        $('#esc_plant_size').val(results.plant_size || '');
        $('#esc_fruit_info').val(results.fruit_info || '');
        $('#esc_flavor_profile').val(results.flavor_profile || '');
        $('#esc_scent').val(results.scent || '');
        $('#esc_bloom_time').val(results.bloom_time || '');
        $('#esc_special_characteristics').val(results.special_characteristics || '');

        // Growing instructions
        // Handle sowing method dropdown
        if (results.sowing_method) {
            const lowerValue = results.sowing_method.toLowerCase();
            if (lowerValue.includes('direct') && lowerValue.includes('indoor')) {
                $('#esc_sowing_method').val('Both');
            } else if (lowerValue.includes('direct')) {
                $('#esc_sowing_method').val('Direct Sow');
            } else if (lowerValue.includes('indoor') || lowerValue.includes('start indoor')) {
                $('#esc_sowing_method').val('Start Indoors');
            } else {
                $('#esc_sowing_method').val(results.sowing_method);
            }
        }
        $('#esc_sowing_depth').val(results.sowing_depth || '');
        $('#esc_sowing_spacing').val(results.sowing_spacing || '');
        $('#esc_germination_temp').val(results.germination_temp || results.germination_temperature || '');
        $('#esc_sun_requirements').val(results.sunlight || results.sun_requirements || '');
        $('#esc_watering').val(results.watering || '');
        $('#esc_fertilizer').val(results.fertilizer || '');
        $('#esc_pest_disease_info').val(results.pest_disease_info || '');
        $('#esc_harvesting_tips').val(results.harvesting_tips || '');
        $('#esc_storage_recommendations').val(results.storage_recommendations || '');

        // Additional information
        $('#esc_usda_zones').val(results.usda_zones || '');
        $('#esc_pollinator_info').val(results.pollinator_info || '');

        // Handle container suitability as boolean or string
        if (results.container_suitability !== undefined) {
            if (typeof results.container_suitability === 'boolean') {
                $('#esc_container_suitability').val(results.container_suitability ? '1' : '0');
            } else if (typeof results.container_suitability === 'string') {
                const value = results.container_suitability.toLowerCase();
                if (value === 'yes' || value === 'true' || value === '1') {
                    $('#esc_container_suitability').val('1');
                } else if (value === 'no' || value === 'false' || value === '0') {
                    $('#esc_container_suitability').val('0');
                }
            } else if (typeof results.container_suitability === 'number') {
                $('#esc_container_suitability').val(results.container_suitability ? '1' : '0');
            }
        }

        // Handle cut flower potential as boolean or string
        if (results.cut_flower_potential !== undefined) {
            if (typeof results.cut_flower_potential === 'boolean') {
                $('#esc_cut_flower_potential').val(results.cut_flower_potential ? '1' : '0');
            } else if (typeof results.cut_flower_potential === 'string') {
                const value = results.cut_flower_potential.toLowerCase();
                if (value === 'yes' || value === 'true' || value === '1') {
                    $('#esc_cut_flower_potential').val('1');
                } else if (value === 'no' || value === 'false' || value === '0') {
                    $('#esc_cut_flower_potential').val('0');
                }
            } else if (typeof results.cut_flower_potential === 'number') {
                $('#esc_cut_flower_potential').val(results.cut_flower_potential ? '1' : '0');
            }
        }

        $('#esc_edible_parts').val(results.edible_parts || '');
        $('#esc_historical_background').val(results.historical_background || '');
        $('#esc_companion_plants').val(results.companion_plants || '');
        $('#esc_regional_tips').val(results.regional_tips || '');
        $('#esc_seed_saving_info').val(results.seed_saving_info || '');

        // Categories
        if (results.suggested_term_ids && results.suggested_term_ids.length) {
            // Initialize Select2 if it's available but not initialized
            if ($.fn.select2 && !$('#esc_seed_category').data('select2')) {
                $('#esc_seed_category').select2({
                    placeholder: 'Select categories',
                    allowClear: true,
                    width: '100%'
                });
            }

            // If using Select2
            if ($.fn.select2 && $('#esc_seed_category').data('select2')) {
                $('#esc_seed_category').val(results.suggested_term_ids).trigger('change');
            } else {
                // Standard multi-select
                $('#esc_seed_category').val(results.suggested_term_ids);
            }
        } else {
            // Initialize Select2 even if no categories are suggested
            if ($.fn.select2 && !$('#esc_seed_category').data('select2')) {
                $('#esc_seed_category').select2({
                    placeholder: 'Select categories',
                    allowClear: true,
                    width: '100%'
                });
            }
        }
    }

    function _showSuccessState(seedName, varietyName) {
        ESC.log('Showing AI success state');

        // Hide loading state
        $('.esc-ai-loading').hide();

        // Update seed display name
        const displayName = varietyName
            ? seedName + ' - ' + varietyName
            : seedName;

        $('#esc-seed-display-name').text(displayName);

        // Show review phase
        if (typeof ESC.UI.showPhase === 'function') {
            ESC.UI.showPhase('esc-phase-review-edit');
        } else {
            $('.esc-phase').hide();
            $('#esc-phase-review-edit').show();
        }

        // Scroll to top of review phase
        _scrollToElement('#esc-phase-review-edit');
    }

    function _showError(message) {
        ESC.log('Showing AI error:', message);

        // Update AI state
        _aiState.isLoading = false;
        _aiState.error = message;

        // Hide other states
        $('.esc-ai-initial, .esc-ai-loading, .esc-ai-success').hide();

        // Show error state
        $('.esc-ai-error').show();

        // Update error message if provided
        if (message) {
            $('.esc-alert__message p').text(message);
        }

        // Scroll to error message
        _scrollToElement('.esc-ai-error');
    }

    function _generateChangesSummary(results) {
        ESC.log('Generating AI changes summary');

        // Clear existing summary
        $('.esc-changes-list').empty();

        // Count populated fields
        let populatedCount = 0;
        const fieldLabels = {
            'description': 'Description',
            'plant_type': 'Plant Type',
            'growth_habit': 'Growth Habit',
            'days_to_maturity': 'Days to Maturity',
            'plant_size': 'Plant Size',
            'fruit_info': 'Fruit/Flower Info',
            'flavor_profile': 'Flavor Profile',
            'scent': 'Scent',
            'bloom_time': 'Bloom Time',
            'special_characteristics': 'Special Characteristics',
            'sowing_method': 'Sowing Method',
            'sowing_depth': 'Sowing Depth',
            'sowing_spacing': 'Sowing Spacing',
            'germination_temp': 'Germination Temperature',
            'sunlight': 'Sun Requirements',
            'watering': 'Watering Needs',
            'fertilizer': 'Fertilizer',
            'pest_disease_info': 'Pest & Disease Info',
            'harvesting_tips': 'Harvesting Tips',
            'storage_recommendations': 'Storage Recommendations',
            'usda_zones': 'USDA Zones',
            'pollinator_info': 'Pollinator Information',
            'container_suitability': 'Container Suitability',
            'cut_flower_potential': 'Cut Flower Potential',
            'edible_parts': 'Edible Parts',
            'historical_background': 'Historical Background',
            'companion_plants': 'Companion Plants',
            'regional_tips': 'Regional Tips',
            'seed_saving_info': 'Seed Saving Info'
        };

        // Add each populated field to the summary
        for (const key in results) {
            if (results[key] && fieldLabels[key]) {
                $('.esc-changes-list').append('<li>' + fieldLabels[key] + '</li>');
                populatedCount++;
            }
        }

        // Update summary text
        if (populatedCount === 0) {
            $('.esc-changes-list').append('<li>No fields were populated by AI</li>');
        }
    }

    function _addConfidenceIndicators(results) {
        ESC.log('Adding confidence indicators');

        // Define confidence levels for different fields
        const confidenceLevels = {
            'high': ['seed_name', 'variety_name', 'plant_type', 'sowing_method'],
            'medium': ['description', 'growth_habit', 'days_to_maturity', 'sunlight', 'watering'],
            'low': ['special_characteristics', 'historical_background', 'regional_tips']
        };

        // Add confidence indicators to fields
        $('.esc-input-with-confidence').each(function() {
            const $container = $(this);
            const $input = $container.find('input, textarea, select');
            const fieldName = $input.attr('name') || $input.attr('id').replace('esc_', '');

            // Determine confidence level
            let confidenceLevel = 'medium'; // Default

            for (const level in confidenceLevels) {
                if (confidenceLevels[level].includes(fieldName)) {
                    confidenceLevel = level;
                    break;
                }
            }

            // Set confidence level
            $container.find('.esc-confidence-indicator').attr('data-confidence', confidenceLevel);
        });
    }

    function _retryAIForSection(section) {
        ESC.log('Retrying AI for section:', section);

        // Get seed name and variety
        const seedName = $('#esc_seed_name_review').val().trim() || $('#esc_seed_name').val().trim();
        const varietyName = $('#esc_variety_name_review').val().trim() || $('#esc_variety_name').val().trim();

        // Validate input
        if (!seedName) {
            alert('Seed name is required to retry AI search.');
            return;
        }

        // Show loading state for the section
        const $sectionCard = $('[data-section="' + section + '"]').closest('.esc-form-card');
        $sectionCard.addClass('esc-card--loading');

        // Make AJAX request to get seed info
        $.ajax({
            url: ESC.getConfig().ajaxUrl,
            type: 'POST',
            data: {
                action: 'esc_gemini_search',
                nonce: ESC.getConfig().nonce,
                seed_name: seedName,
                variety: varietyName,
                section: section // Pass section to backend
            },
            dataType: 'json',
            success: function(response) {
                ESC.log('AI retry response:', response);

                if (response.success && response.data) {
                    // Update only fields in this section
                    _updateSectionFields(section, response.data);
                } else {
                    // Show error
                    alert('Error fetching additional information: ' +
                        (response.data && response.data.message ? response.data.message : 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                ESC.error('AI retry error:', status, error);
                alert('Error communicating with the server.');
            },
            complete: function() {
                // Remove loading state
                $sectionCard.removeClass('esc-card--loading');
            }
        });
    }

    function _updateSectionFields(section, data) {
        ESC.log('Updating section fields:', section);

        // Define which fields belong to which section
        const sectionFields = {
            'basic': ['description', 'image_url'],
            'characteristics': ['plant_type', 'growth_habit', 'days_to_maturity', 'plant_size',
                              'fruit_info', 'flavor_profile', 'scent', 'bloom_time', 'special_characteristics'],
            'growing': ['sowing_method', 'sowing_depth', 'sowing_spacing', 'germination_temp',
                       'sunlight', 'watering', 'fertilizer', 'pest_disease_info', 'harvesting_tips', 'storage_recommendations'],
            'additional': ['usda_zones', 'pollinator_info', 'container_suitability', 'cut_flower_potential',
                          'edible_parts', 'historical_background', 'companion_plants', 'regional_tips', 'seed_saving_info']
        };

        // Get fields for the specified section
        const fields = sectionFields[section] || [];

        // Update only those fields
        fields.forEach(function(field) {
            if (data[field] !== undefined) {
                // Handle special cases
                if (field === 'image_source_url') {
                    // Show manual download instructions if image source URL is available
                    if (data[field]) {
                        _showManualDownloadInstructions(data[field]);
                    }
                }
                else if (field === 'sunlight' || field === 'sun_requirements') {
                    $('#esc_sun_requirements').val(data[field] || '');
                }
                else if (field === 'germination_temp' || field === 'germination_temperature') {
                    $('#esc_germination_temp').val(data[field] || '');
                }
                else if (field === 'sowing_method') {
                    // Handle sowing method dropdown
                    const value = data[field];
                    if (value) {
                        const lowerValue = value.toLowerCase();
                        if (lowerValue.includes('direct') && lowerValue.includes('indoor')) {
                            $('#esc_sowing_method').val('Both');
                        } else if (lowerValue.includes('direct')) {
                            $('#esc_sowing_method').val('Direct Sow');
                        } else if (lowerValue.includes('indoor') || lowerValue.includes('start indoor')) {
                            $('#esc_sowing_method').val('Start Indoors');
                        } else {
                            $('#esc_sowing_method').val(value);
                        }
                    }
                }
                else if (field === 'container_suitability' || field === 'cut_flower_potential') {
                    // Handle boolean or string values
                    const value = data[field];
                    if (typeof value === 'boolean') {
                        $('#esc_' + field).val(value ? '1' : '0');
                    } else if (typeof value === 'string') {
                        const strValue = value.toLowerCase();
                        if (strValue === 'yes' || strValue === 'true' || strValue === '1') {
                            $('#esc_' + field).val('1');
                        } else if (strValue === 'no' || strValue === 'false' || strValue === '0') {
                            $('#esc_' + field).val('0');
                        }
                    } else if (typeof value === 'number') {
                        $('#esc_' + field).val(value ? '1' : '0');
                    }
                }
                else {
                    $('#esc_' + field).val(data[field] || '');
                }
            }
        });

        // Update section status
        const $sectionCard = $('[data-section="' + section + '"]').closest('.esc-form-card');
        $sectionCard.attr('data-ai-status', 'fully-populated');

        // Update AI badge
        const $badge = $sectionCard.find('.esc-ai-status-badge');
        $badge.html('<span class="dashicons dashicons-yes"></span><span class="esc-badge-text">AI Complete</span>');
    }

    function _scrollToElement(selector, offset = 100) {
        const $element = $(selector);

        if ($element.length) {
            $('html, body').animate({
                scrollTop: $element.offset().top - offset
            }, 300);
        }
    }



    // Function to show image message
    function _showImageMessage(message, type) {
        const $error = $('.esc-upload-error');
        if ($error.length) {
            $error.text(message);
            $error.removeClass('esc-success esc-error esc-manual-download').addClass('esc-' + type);
            $error.show();

            // Hide the message after 5 seconds
            setTimeout(function() {
                $error.fadeOut();
            }, 5000);
        }
    }

    // Function to show manual download instructions
    function _showManualDownloadInstructions(sourceUrl) {
        // Create a container for the manual download UI if it doesn't exist
        let $container = $('.esc-manual-download-container');
        if (!$container.length) {
            // Create a new container after the image preview
            $container = $('<div class="esc-manual-download-container"></div>');
            $('.esc-image-preview').after($container);
        }

        // Clear any existing content
        $container.empty();

        let html = '<div class="esc-manual-download-instructions">';
        html += '<h4>Manual Image Download Required</h4>';
        html += '<p>The image could not be downloaded automatically. Please follow these steps:</p>';
        html += '<ol>';
        html += '<li><strong>Click the "Visit Image Source" button below</strong> to open the website where the image is located</li>';
        html += '<li><strong>Find and right-click on the plant image</strong> and select "Save Image As..." (or "Save Picture As...")</li>';
        html += '<li><strong>Save the image to your Downloads folder</strong> or another location you can easily find</li>';
        html += '<li><strong>Return to this page</strong> and click the "Upload Image" button below</li>';
        html += '<li><strong>Select the image file</strong> you just downloaded when the file browser opens</li>';
        html += '<li>The image will be uploaded to WordPress and automatically linked to this seed entry</li>';
        html += '</ol>';

        // Add source URL button if available
        if (sourceUrl) {
            html += '<div class="esc-button-group">';
            html += '<a href="' + sourceUrl + '" target="_blank" class="esc-button esc-button-primary">Visit Image Source</a>';
            html += '<button type="button" class="esc-button esc-button-secondary esc-upload-trigger">Upload Image</button>';
            html += '</div>';
            html += '<p class="esc-manual-download-note">Note: If you can\'t find a suitable image on the source page, you can search for "' + $('#esc_seed_name').val() + ' plant" or "' + $('#esc_seed_name').val() + ' fruit" on Google Images and download an appropriate image.</p>';
        } else {
            html += '<div class="esc-button-group">';
            html += '<button type="button" class="esc-button esc-button-secondary esc-upload-trigger">Upload Image</button>';
            html += '</div>';
        }

        html += '<div class="esc-upload-progress"><div class="esc-progress-bar"></div></div>';
        html += '</div>';

        $container.html(html).addClass('esc-manual-download').show();

        // Add click handler for the upload trigger button
        $('.esc-upload-trigger').on('click', function() {
            // Create a hidden file input
            const $fileInput = $('<input type="file" accept="image/*" style="display:none">');
            $('body').append($fileInput);

            // Trigger the file selection dialog
            $fileInput.trigger('click');

            // Handle file selection
            $fileInput.on('change', function() {
                if (this.files && this.files[0]) {
                    // Create a FormData object
                    const formData = new FormData();
                    formData.append('action', 'esc_upload_image');
                    formData.append('nonce', ESC.getConfig().nonce);
                    formData.append('image', this.files[0]);

                    // Show progress indicator if available
                    const $progress = $('.esc-upload-progress');
                    const $progressBar = $('.esc-progress-bar');
                    if ($progress.length && $progressBar.length) {
                        $progress.show();
                        $progressBar.css('width', '0%');
                    }

                    // Send AJAX request to upload the image
                    $.ajax({
                        url: ESC.getConfig().ajaxUrl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        xhr: function() {
                            const xhr = new window.XMLHttpRequest();
                            xhr.upload.addEventListener('progress', function(e) {
                                if (e.lengthComputable && $progressBar.length) {
                                    const percent = (e.loaded / e.total) * 100;
                                    $progressBar.css('width', percent + '%');
                                }
                            }, false);
                            return xhr;
                        },
                        success: function(response) {
                            // Hide progress indicator
                            if ($progress.length) {
                                $progress.hide();
                            }

                            // Remove the file input
                            $fileInput.remove();

                            if (response.success) {
                                // Update the image URL in the form
                                $('#esc_image_url').val(response.data.url);

                                // Update the image preview
                                if ($('.esc-image-preview').length) {
                                    $('.esc-image-preview').show();
                                    $('.esc-preview-image').attr('src', response.data.url);
                                }

                                // Update the dropzone to show it has an image
                                $('.esc-dropzone').addClass('has-image');

                                // Hide the manual download container
                                $('.esc-manual-download-container').hide();

                                // Show success message
                                _showImageMessage('Image uploaded successfully and linked to this seed.', 'success');
                            } else {
                                // Show error message
                                _showImageMessage(response.data?.message || 'Error uploading image.', 'error');
                            }
                        },
                        error: function() {
                            // Hide progress indicator
                            if ($progress.length) {
                                $progress.hide();
                            }

                            // Remove the file input
                            $fileInput.remove();

                            // Show error message
                            _showImageMessage('Error uploading image. Please try again.', 'error');
                        }
                    });
                }
            });
        });
    }



    function _initSelect2() {
        ESC.log('Initializing Select2 for category dropdown');

        // Initialize Select2 for the category dropdown if it's available
        if ($.fn.select2 && $('#esc_seed_category').length && !$('#esc_seed_category').data('select2')) {
            $('#esc_seed_category').select2({
                placeholder: 'Select categories',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#esc_seed_category').closest('.esc-form-field')
            });
        }
    }

    function _init() {
        if (_initialized) {
            return;
        }

        ESC.log('Initializing AI module');

        // Initialize AI handlers
        _initAIHandlers();

        // Initialize Select2
        _initSelect2();

        // Setup event listeners
        $(document).on('esc:reinitAI', function() {
            ESC.log('Reinitializing AI handlers');
            _initAIHandlers();
            _initSelect2();
        });

        _initialized = true;
    }

    // Public API
    return {
        init: _init,
        getAIState: function() {
            return { ..._aiState }; // Return a copy to prevent modification
        },
        retryAIForSection: _retryAIForSection
    };
})(jQuery);

/**
 * Variety Suggestions JavaScript
 *
 * Handles the variety suggestions functionality for the add seed form.
 */
(function($) {
    'use strict';

    // Variables
    let typingTimer;
    const doneTypingInterval = 800; // Time in ms after user stops typing
    let currentSeedType = '';
    let varietiesCache = {};

    // Initialize
    function init() {
        // Add event listeners
        $('#esc_seed_name').on('keyup', handleSeedNameKeyup);
        $('#esc_seed_name').on('change', handleSeedNameChange);
        $('#esc_seed_name').on('blur', convertToTitleCase);

        // Setup variety field behavior
        setupVarietyField();
    }

    // Convert seed name to Title Case
    function convertToTitleCase() {
        const seedNameInput = $('#esc_seed_name');
        const seedName = seedNameInput.val().trim();

        if (seedName) {
            // Convert to title case (capitalize first letter of each word)
            const titleCaseName = seedName.replace(/\w\S*/g, function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            });

            // Set the value back to the input
            seedNameInput.val(titleCaseName);
        }
    }

    // Handle keyup event on seed name field
    function handleSeedNameKeyup() {
        clearTimeout(typingTimer);

        const seedType = $(this).val().trim();

        // Only proceed if we have at least 3 characters
        if (seedType.length < 3) {
            hideVarietyDropdown();
            return;
        }

        // Set a timer to fetch varieties after user stops typing
        typingTimer = setTimeout(function() {
            fetchVarietiesForSeedType(seedType);
        }, doneTypingInterval);
    }

    // Handle change event on seed name field
    function handleSeedNameChange() {
        // Convert to title case first
        convertToTitleCase();

        const seedType = $(this).val().trim();

        if (seedType.length >= 3) {
            fetchVarietiesForSeedType(seedType);
        } else {
            hideVarietyDropdown();
        }
    }

    // Setup variety field behavior
    function setupVarietyField() {
        // Add event listener for variety field focus
        $('#esc_variety_name').on('focus', function() {
            const seedType = $('#esc_seed_name').val().trim();
            if (seedType.length >= 3) {
                fetchVarietiesForSeedType(seedType);
            }
        });

        // Add event listener for variety field input
        $('#esc_variety_name').on('input', function() {
            const input = $(this).val().toLowerCase().trim();

            // If we have cached varieties, filter them
            if (currentSeedType && varietiesCache[currentSeedType]) {
                filterVarietyOptions(input);
            }
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.esc-variety-field-container').length) {
                hideVarietyDropdown();
            }
        });
    }

    // Fetch varieties for a seed type
    function fetchVarietiesForSeedType(seedType) {
        // Don't fetch again if we already have this seed type
        if (seedType === currentSeedType && varietiesCache[seedType]) {
            showVarietyDropdown();
            return;
        }

        currentSeedType = seedType;

        // Check if we have cached varieties for this seed type
        if (varietiesCache[seedType]) {
            populateVarietyDropdown(varietiesCache[seedType]);
            return;
        }

        // Show loading indicator
        $('.esc-variety-loading').show();

        // Make AJAX request to get varieties
        $.ajax({
            url: esc_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'esc_get_varieties',
                nonce: esc_ajax_object.nonce,
                seed_type: seedType
            },
            success: function(response) {
                $('.esc-variety-loading').hide();

                if (response.success && response.data.varieties) {
                    // Cache the varieties
                    varietiesCache[seedType] = response.data.varieties;

                    // Populate the dropdown
                    populateVarietyDropdown(response.data.varieties);
                } else {
                    console.error('Error fetching varieties:', response.data ? response.data.message : 'Unknown error');
                }
            },
            error: function(xhr, status, error) {
                $('.esc-variety-loading').hide();
                console.error('AJAX error:', error);
            }
        });
    }

    // Populate the variety dropdown with options
    function populateVarietyDropdown(varieties) {
        const $dropdown = $('#esc-variety-dropdown');

        // Clear existing options
        $dropdown.empty();

        // Add each variety as an option
        varieties.forEach(function(variety) {
            const $option = $('<div class="esc-variety-option">' + variety + '</div>');

            // Add click handler to select the variety
            $option.on('click', function() {
                selectVariety(variety);
            });

            $dropdown.append($option);
        });

        // Show the dropdown
        showVarietyDropdown();
    }

    // Filter variety options based on input
    function filterVarietyOptions(input) {
        const $options = $('.esc-variety-option');

        $options.each(function() {
            const optionText = $(this).text().toLowerCase();

            if (optionText.includes(input)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Select a variety
    function selectVariety(variety) {
        const $varietyField = $('#esc_variety_name');
        $varietyField.val(variety);

        // Trigger input event to handle floating label
        $varietyField.trigger('input');

        // Add has-value class for floating label
        $varietyField.addClass('has-value');

        hideVarietyDropdown();
    }

    // Show the variety dropdown
    function showVarietyDropdown() {
        $('#esc-variety-dropdown').show();
    }

    // Hide the variety dropdown
    function hideVarietyDropdown() {
        $('#esc-variety-dropdown').hide();
    }

    // Initialize when document is ready
    $(document).ready(function() {
        init();
    });

})(jQuery);

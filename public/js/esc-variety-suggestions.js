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
                // If we already have varieties cached, show them immediately
                if (currentSeedType === seedType && varietiesCache[seedType]) {
                    populateVarietyDropdown(varietiesCache[seedType]);
                } else {
                    // Otherwise fetch new varieties
                    fetchVarietiesForSeedType(seedType);
                }
            }
        });

        // Add click event to toggle dropdown
        $('#esc_variety_name').on('click', function(e) {
            e.stopPropagation(); // Prevent document click from immediately closing it

            const seedType = $('#esc_seed_name').val().trim();
            if (seedType.length >= 3) {
                const $dropdown = $('#esc-variety-dropdown');

                // Toggle dropdown visibility
                if ($dropdown.is(':visible')) {
                    hideVarietyDropdown();
                } else if (currentSeedType === seedType && varietiesCache[seedType]) {
                    populateVarietyDropdown(varietiesCache[seedType]);
                } else {
                    fetchVarietiesForSeedType(seedType);
                }
            }
        });

        // Add event listener for variety field input
        $('#esc_variety_name').on('input', function() {
            const input = $(this).val().toLowerCase().trim();

            // If the field is cleared, remove the selected variety data
            if (input === '') {
                $(this).removeData('selected-variety');
                hideVarietyDropdown();
                return;
            }

            // If we have cached varieties, filter them
            if (currentSeedType && varietiesCache[currentSeedType]) {
                filterVarietyOptions(input);
                showVarietyDropdown(); // Ensure dropdown is visible after filtering
            }
        });

        // Handle clicks on variety options using event delegation
        $(document).on('click', '.esc-variety-option', function(e) {
            e.stopPropagation(); // Prevent the document click handler from firing
            const variety = $(this).text();
            selectVariety(variety);
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            // Don't close if clicking on the variety field or dropdown
            if (!$(e.target).closest('.esc-variety-field').length &&
                !$(e.target).closest('#esc-variety-dropdown').length) {
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

                    // Show error message in dropdown
                    const $dropdown = $('#esc-variety-dropdown');
                    $dropdown.empty();
                    $dropdown.append('<div class="esc-variety-error">Error: ' + (response.data ? response.data.message : 'Unable to fetch varieties') + '</div>');
                    showVarietyDropdown();
                }
            },
            error: function(_, textStatus, errorThrown) {
                $('.esc-variety-loading').hide();
                console.error('AJAX error:', textStatus, errorThrown);

                // Show error message in dropdown
                const $dropdown = $('#esc-variety-dropdown');
                $dropdown.empty();
                $dropdown.append('<div class="esc-variety-error">Unable to fetch varieties. Please try again later.</div>');
                showVarietyDropdown();
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
            // Create option with proper styling
            const $option = $('<div class="esc-variety-option">' + variety + '</div>');

            // Add hover effect for better UX
            $option.hover(
                function() { $(this).css('background-color', '#ebf5fb'); },
                function() { $(this).css('background-color', ''); }
            );

            $dropdown.append($option);
        });

        // Add a message if no varieties were found
        if (varieties.length === 0) {
            $dropdown.append('<div class="esc-variety-empty">No varieties found</div>');
        }

        // Log for debugging
        console.log('Populated dropdown with', varieties.length, 'varieties');

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

        // Hide the dropdown but keep the varieties cached
        hideVarietyDropdown();

        // Store the selected variety for reference
        $varietyField.data('selected-variety', variety);

        console.log('Selected variety:', variety);
    }

    // Show the variety dropdown
    function showVarietyDropdown() {
        const $dropdown = $('#esc-variety-dropdown');
        const $varietyField = $('#esc_variety_name');

        // Only show if we have options
        if ($dropdown.children().length === 0) {
            return;
        }

        // Position the dropdown relative to the variety field
        const fieldOffset = $varietyField.offset();
        const fieldHeight = $varietyField.outerHeight();
        const fieldWidth = $varietyField.outerWidth();

        // Set position and dimensions
        $dropdown.css({
            'position': 'absolute',
            'top': (fieldOffset.top + fieldHeight) + 'px',
            'left': fieldOffset.left + 'px',
            'width': fieldWidth + 'px',
            'z-index': 9999,
            'display': 'block'
        });

        // Move to body to avoid containment issues
        if ($dropdown.parent().is('.esc-variety-field-container, .esc-floating-label, .esc-variety-field')) {
            $dropdown.detach().appendTo('body');
        }

        // Add a class to the variety field to indicate the dropdown is open
        $varietyField.addClass('dropdown-open');

        console.log('Showing variety dropdown at position:', fieldOffset.top + fieldHeight, fieldOffset.left);
    }

    // Hide the variety dropdown
    function hideVarietyDropdown() {
        const $dropdown = $('#esc-variety-dropdown');
        const $varietyField = $('#esc_variety_name');

        // Hide the dropdown
        $dropdown.hide();

        // Remove the open class from the variety field
        $varietyField.removeClass('dropdown-open');

        // If the dropdown is in the body, move it back to its original container
        if ($dropdown.parent().is('body')) {
            const $fieldContainer = $varietyField.closest('.esc-variety-field');

            if ($fieldContainer.length) {
                $dropdown.detach().appendTo($fieldContainer);
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        init();
    });

})(jQuery);

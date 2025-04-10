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
                }
            },
            error: function(_, textStatus, errorThrown) {
                $('.esc-variety-loading').hide();
                console.error('AJAX error:', textStatus, errorThrown);

                // Use fallback data if AJAX fails
                useFallbackVarieties(seedType);
            }
        });
    }

    // Use fallback variety data when AJAX is not available
    function useFallbackVarieties(seedType) {
        console.log('Using fallback varieties for:', seedType);

        // Common varieties for popular seed types
        const fallbackData = {
            'tomato': ['Brandywine', 'Cherokee Purple', 'San Marzano', 'Roma', 'Better Boy', 'Early Girl', 'Beefsteak', 'Cherry', 'Grape', 'Sungold', 'Black Krim', 'Green Zebra', 'Mortgage Lifter', 'Amish Paste', 'Yellow Pear'],
            'pepper': ['Bell', 'Jalapeño', 'Habanero', 'Cayenne', 'Serrano', 'Poblano', 'Anaheim', 'Thai', 'Ghost', 'Banana', 'Sweet Italian', 'Hungarian Wax', 'Shishito', 'Carolina Reaper', 'Scotch Bonnet'],
            'bean': ['Kentucky Wonder', 'Blue Lake', 'Pinto', 'Black', 'Navy', 'Lima', 'Kidney', 'Fava', 'Garbanzo', 'Green', 'Yellow Wax', 'Dragon Tongue', 'Scarlet Runner', 'Cannellini', 'Great Northern'],
            'lettuce': ['Romaine', 'Butterhead', 'Iceberg', 'Loose Leaf', 'Red Leaf', 'Green Leaf', 'Bibb', 'Arugula', 'Oak Leaf', 'Batavian', 'Mesclun Mix', 'Little Gem', 'Butter Crunch', 'Salad Bowl', 'Lollo Rossa'],
            'cucumber': ['Straight Eight', 'Marketmore', 'Pickling', 'English', 'Armenian', 'Lemon', 'Persian', 'Japanese', 'Kirby', 'Burpless', 'Slicing', 'Boston Pickling', 'Suyo Long', 'Mexican Sour Gherkin', 'Muncher'],
            'squash': ['Zucchini', 'Yellow Summer', 'Butternut', 'Acorn', 'Spaghetti', 'Delicata', 'Hubbard', 'Pumpkin', 'Patty Pan', 'Crookneck', 'Kabocha', 'Buttercup', 'Carnival', 'Sweet Dumpling', 'Turban'],
            'corn': ['Sweet', 'Silver Queen', 'Butter and Sugar', 'Peaches and Cream', 'Golden Bantam', 'Honey Select', 'Ambrosia', 'Jubilee', 'Bodacious', 'Incredible', 'Kandy Korn', 'Silver King', 'Honey and Cream', 'Stowell\'s Evergreen', 'Country Gentleman'],
            'carrot': ['Danvers', 'Nantes', 'Imperator', 'Chantenay', 'Little Finger', 'Purple Dragon', 'Cosmic Purple', 'Rainbow', 'Scarlet Nantes', 'Thumbelina', 'Bolero', 'Yellowstone', 'White Satin', 'Atomic Red', 'Paris Market'],
            'radish': ['Cherry Belle', 'French Breakfast', 'White Icicle', 'Watermelon', 'Black Spanish', 'Daikon', 'Easter Egg', 'China Rose', 'Purple Plum', 'Champion', 'White Beauty', 'Red King', 'Sparkler', 'Green Meat', 'Zlata'],
            'onion': ['Yellow Sweet Spanish', 'Red Burgundy', 'White Sweet Spanish', 'Walla Walla', 'Vidalia', 'Texas Supersweet', 'Candy', 'Red Wing', 'Evergreen Bunching', 'Crystal White Wax', 'Southport White Globe', 'Ailsa Craig', 'Red Baron', 'Stuttgarter', 'Cipollini']
        };

        // Normalize seed type to lowercase for matching
        const normalizedSeedType = seedType.toLowerCase();

        // Find the closest match in our fallback data
        let bestMatch = null;
        let bestMatchScore = 0;

        for (const key in fallbackData) {
            if (normalizedSeedType === key) {
                // Exact match
                bestMatch = key;
                break;
            } else if (normalizedSeedType.includes(key) || key.includes(normalizedSeedType)) {
                // Partial match - use the longer match as better
                const matchScore = key.length;
                if (matchScore > bestMatchScore) {
                    bestMatch = key;
                    bestMatchScore = matchScore;
                }
            }
        }

        if (bestMatch && fallbackData[bestMatch]) {
            // Cache the varieties
            varietiesCache[seedType] = fallbackData[bestMatch];

            // Populate the dropdown
            populateVarietyDropdown(fallbackData[bestMatch]);
        } else {
            // No match found, use generic varieties
            const genericVarieties = ['Common', 'Heirloom', 'Hybrid', 'Organic', 'Heritage', 'Standard', 'Dwarf', 'Giant', 'Early', 'Late', 'Mid-Season', 'Compact', 'Climbing', 'Bush', 'Trailing'];

            // Cache the varieties
            varietiesCache[seedType] = genericVarieties;

            // Populate the dropdown
            populateVarietyDropdown(genericVarieties);
        }
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

        hideVarietyDropdown();
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

        console.log('Showing variety dropdown at position:', fieldOffset.top + fieldHeight, fieldOffset.left);
    }

    // Hide the variety dropdown
    function hideVarietyDropdown() {
        const $dropdown = $('#esc-variety-dropdown');

        // Hide the dropdown
        $dropdown.hide();

        // If the dropdown is in the body, move it back to its original container
        if ($dropdown.parent().is('body')) {
            const $varietyField = $('#esc_variety_name');
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

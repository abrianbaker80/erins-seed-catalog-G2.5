/**
 * Erin's Seed Catalog - Variety Module
 *
 * Handles variety suggestions functionality including:
 * - Fetching variety suggestions
 * - Displaying dropdown suggestions
 * - Selection handling
 */

// Add the Variety module to our ESC namespace
if (typeof window.ESC === 'undefined') {
    console.error('ESC namespace is not defined. Creating a placeholder.');
    window.ESC = {
        log: function() {
            if (window.console && window.console.log) {
                console.log.apply(console, ['[ESC]'].concat(Array.prototype.slice.call(arguments)));
            }
        },
        error: function() {
            if (window.console && window.console.error) {
                console.error.apply(console, ['[ESC]'].concat(Array.prototype.slice.call(arguments)));
            }
        },
        getConfig: function() {
            return {
                ajaxUrl: typeof esc_ajax_object !== 'undefined' ? esc_ajax_object.ajax_url : '',
                nonce: typeof esc_ajax_object !== 'undefined' ? esc_ajax_object.nonce : ''
            };
        }
    };
}

window.ESC.Variety = (function($) {
    'use strict';

    // Private variables
    let _initialized = false;
    let _cache = {};
    let _currentSeedType = '';
    let _debounceTimer = null;
    let _isLoading = false;

    // Private methods
    function _initVarietySuggestions() {
        ESC.log('Initializing variety suggestions');

        // Get seed name input - try both the initial and review form fields
        const $seedNameInput = $('#esc_seed_name, #esc_seed_name_review').first();
        const $varietyInput = $('#esc_variety_name, #esc_variety_name_review').first();
        const $varietyDropdown = $('#esc-variety-dropdown');
        const $loadingIndicator = $('.esc-variety-loading');

        ESC.log('Seed name input found:', $seedNameInput.length > 0);
        ESC.log('Variety input found:', $varietyInput.length > 0);
        ESC.log('Variety dropdown found:', $varietyDropdown.length > 0);
        ESC.log('Loading indicator found:', $loadingIndicator.length > 0);

        if (!$seedNameInput.length || !$varietyInput.length || !$varietyDropdown.length) {
            ESC.log('Variety suggestion elements not found, skipping initialization');
            return;
        }

        // Track seed name changes
        $seedNameInput.off('input.esc-variety').on('input.esc-variety', function() {
            const seedName = $(this).val().trim();
            ESC.log('Seed name changed to:', seedName);

            // Clear variety if seed name changes significantly
            if (_currentSeedType && seedName !== _currentSeedType) {
                $varietyInput.val('');
                $varietyDropdown.empty().hide();
            }

            _currentSeedType = seedName;

            // If seed name is at least 3 characters, fetch varieties
            if (seedName.length >= 3) {
                // Debounce requests
                clearTimeout(_debounceTimer);
                _debounceTimer = setTimeout(function() {
                    _fetchVarietySuggestions(seedName, '');
                }, 500);
            }
        });

        // Setup variety input
        $varietyInput.off('input.esc-variety focus.esc-variety').on('input.esc-variety focus.esc-variety', function() {
            const seedName = $seedNameInput.val().trim();
            const varietyInput = $(this).val().trim();

            ESC.log('Variety input event, seed name:', seedName, 'variety input:', varietyInput);

            // Don't show suggestions if no seed name
            if (!seedName || seedName.length < 3) {
                return;
            }

            // Debounce requests
            clearTimeout(_debounceTimer);
            _debounceTimer = setTimeout(function() {
                _fetchVarietySuggestions(seedName, varietyInput);
            }, 300);
        });

        // Hide dropdown when clicking outside
        $(document).off('click.esc-variety').on('click.esc-variety', function(e) {
            if (!$(e.target).closest('.esc-variety-field').length &&
                !$(e.target).closest('#esc-variety-dropdown').length) {
                $varietyDropdown.hide();
            }
        });

        // Handle suggestion selection
        $varietyDropdown.off('click.esc-variety').on('click.esc-variety', '.esc-variety-suggestion', function() {
            const variety = $(this).text();
            ESC.log('Variety selected:', variety);
            $varietyInput.val(variety);
            $varietyDropdown.hide();

            // Update hidden field if exists
            if ($('#esc_variety_name_hidden').length) {
                $('#esc_variety_name_hidden').val(variety);
            }

            // Trigger change event
            $varietyInput.trigger('change');
        });

        // Initial check if seed name already has a value
        const initialSeedName = $seedNameInput.val().trim();
        if (initialSeedName && initialSeedName.length >= 3) {
            ESC.log('Initial seed name found, fetching varieties:', initialSeedName);
            _currentSeedType = initialSeedName;
            _fetchVarietySuggestions(initialSeedName, '');
        }
    }

    function _fetchVarietySuggestions(seedName, varietyInput = '') {
        ESC.log('Fetching variety suggestions for:', seedName);

        // Don't fetch if no seed name or if it's too short
        if (!seedName || seedName.length < 3) {
            ESC.log('Seed name too short, skipping fetch');
            return;
        }

        // Check cache first
        const cacheKey = seedName.toLowerCase();
        if (_cache[cacheKey]) {
            ESC.log('Using cached varieties for:', seedName);
            _displayVarietySuggestions(_cache[cacheKey], varietyInput);
            return;
        }

        // Show loading indicator
        $('.esc-variety-loading').show();
        _isLoading = true;

        // Determine which AJAX URL and nonce to use
        let ajaxUrl, nonce;

        if (typeof ESC !== 'undefined' && ESC.getConfig) {
            // Use ESC namespace if available
            ajaxUrl = ESC.getConfig().ajaxUrl;
            nonce = ESC.getConfig().nonce;
            ESC.log('Using ESC namespace for AJAX config');
        } else if (typeof esc_ajax_object !== 'undefined') {
            // Fall back to global esc_ajax_object
            ajaxUrl = esc_ajax_object.ajax_url;
            nonce = esc_ajax_object.nonce;
            ESC.log('Using global esc_ajax_object for AJAX config');
        } else {
            // Last resort, try to find it in wp_localize_script data
            ajaxUrl = window.ajaxurl || '/wp-admin/admin-ajax.php';
            nonce = '';
            ESC.log('Using fallback AJAX URL:', ajaxUrl);
        }

        // Make AJAX request
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'esc_get_varieties',
                nonce: nonce,
                seed_type: seedName
            },
            dataType: 'json',
            success: function(response) {
                ESC.log('Variety suggestions response:', response);

                if (response.success && response.data && response.data.varieties) {
                    // Cache the results
                    _cache[cacheKey] = response.data.varieties;

                    // Display suggestions
                    _displayVarietySuggestions(response.data.varieties, varietyInput);
                } else {
                    // Show empty dropdown with message
                    const $dropdown = $('#esc-variety-dropdown');
                    $dropdown.empty().append('<div class="esc-variety-empty">No varieties found</div>');
                    _displayVarietySuggestions([], varietyInput);
                    ESC.log('No varieties found or error in response');
                }
            },
            error: function(xhr, status, error) {
                ESC.error('Variety suggestions error:', status, error);
                // Show error in dropdown
                const $dropdown = $('#esc-variety-dropdown');
                $dropdown.empty().append('<div class="esc-variety-error">Error loading varieties</div>');
                _displayVarietySuggestions([], varietyInput);
            },
            complete: function() {
                // Hide loading indicator
                $('.esc-variety-loading').hide();
                _isLoading = false;
            }
        });
    }

    function _displayVarietySuggestions(varieties, filterText = '') {
        ESC.log('Displaying variety suggestions, count:', varieties.length);

        const $dropdown = $('#esc-variety-dropdown');

        // If dropdown doesn't exist, create it
        if ($dropdown.length === 0) {
            ESC.log('Dropdown not found, creating it');
            $('body').append('<div id="esc-variety-dropdown" class="esc-dropdown__menu" style="display: none;"></div>');
            return _displayVarietySuggestions(varieties, filterText); // Retry after creating
        }

        // Make sure the dropdown has the correct CSS class
        if (!$dropdown.hasClass('esc-dropdown__menu')) {
            $dropdown.addClass('esc-dropdown__menu');
        }

        // Clear dropdown
        $dropdown.empty();

        // Filter varieties if needed
        let filteredVarieties = varieties;
        if (filterText) {
            const lowerFilter = filterText.toLowerCase();
            filteredVarieties = varieties.filter(function(variety) {
                return variety.toLowerCase().includes(lowerFilter);
            });
            ESC.log('Filtered varieties to:', filteredVarieties.length);
        }

        // Limit to top 15
        const limitedVarieties = filteredVarieties.slice(0, 15);
        ESC.log('Limited to top 15 varieties:', limitedVarieties.length);

        // If no varieties, show a message
        if (limitedVarieties.length === 0) {
            $dropdown.append('<div class="esc-variety-empty">No varieties found</div>');
        } else {
            // Add varieties to dropdown
            limitedVarieties.forEach(function(variety) {
                const $suggestion = $('<div class="esc-variety-suggestion"></div>').text(variety);
                $dropdown.append($suggestion);
            });
        }

        // Position the dropdown relative to the variety field
        const $varietyInput = $('#esc_variety_name, #esc_variety_name_review').first();
        if ($varietyInput.length) {
            const fieldOffset = $varietyInput.offset();
            const fieldHeight = $varietyInput.outerHeight();
            const fieldWidth = $varietyInput.outerWidth();

            $dropdown.css({
                'position': 'absolute',
                'top': (fieldOffset.top + fieldHeight) + 'px',
                'left': fieldOffset.left + 'px',
                'width': fieldWidth + 'px',
                'z-index': 9999,
                'max-height': '200px',
                'overflow-y': 'auto',
                'background-color': '#fff',
                'border': '1px solid #ddd',
                'border-radius': '4px',
                'box-shadow': '0 2px 5px rgba(0,0,0,0.1)'
            });
        }

        // Show dropdown
        $dropdown.show();
        ESC.log('Dropdown shown with', limitedVarieties.length, 'varieties');
    }

    function _init() {
        if (_initialized) {
            return;
        }

        ESC.log('Initializing Variety module');

        // Create variety dropdown if it doesn't exist
        if ($('#esc-variety-dropdown').length === 0) {
            $('body').append('<div id="esc-variety-dropdown" class="esc-dropdown__menu" style="display: none;"></div>');
            ESC.log('Created variety dropdown element');
        } else {
            ESC.log('Variety dropdown element already exists');
        }

        // Create loading indicator if it doesn't exist
        if ($('.esc-variety-loading').length === 0) {
            $('#esc_variety_name').parent().append('<div class="esc-variety-loading" style="display: none;"><span class="dashicons dashicons-update-alt esc-animate-pulse"></span> Loading varieties...</div>');
            ESC.log('Created variety loading indicator');
        } else {
            ESC.log('Variety loading indicator already exists');
        }

        // Initialize variety suggestions
        _initVarietySuggestions();

        // Setup event listeners
        $(document).off('esc:reinitVariety').on('esc:reinitVariety', function() {
            ESC.log('Reinitializing variety suggestions');
            _initVarietySuggestions();
        });

        // Also initialize when document is ready
        $(document).ready(function() {
            ESC.log('Document ready, initializing variety suggestions');
            _initVarietySuggestions();
        });

        // Initialize immediately if document is already ready
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(function() {
                ESC.log('Document already ready, initializing variety suggestions immediately');
                _initVarietySuggestions();
            }, 1);
        }

        _initialized = true;
    }

    // Public API
    return {
        init: _init,
        refreshSuggestions: function(seedName, varietyInput) {
            _fetchVarietySuggestions(seedName, varietyInput);
        },
        isLoading: function() {
            return _isLoading;
        }
    };
})(jQuery);

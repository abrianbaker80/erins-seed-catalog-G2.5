/**
 * Enhanced Seed Cards JavaScript
 */
(function($) {
    'use strict';

    // Initialize enhanced seed cards
    function initEnhancedCards() {
        const $modal = $('#esc-seed-detail-modal');
        const $modalContent = $('#esc-seed-detail-content');
        const $body = $('body');
        const $resultsContainer = $('#esc-catalog-results');
        const $searchForm = $('#esc-search-form');

        // Initialize search form if it exists
        if ($searchForm.length) {
            initSearchForm();
        }

        // Handle clicking on seed cards - use document to ensure it works with AJAX loaded content
        $(document).on('click', '.esc-enhanced-catalog .esc-seed-card', function(e) {
            console.log('Card clicked with ID: ' + $(this).data('seed-id'));

            // Don't trigger if clicking on a link inside the card
            if ($(e.target).closest('a').length) {
                console.log('Link clicked, ignoring card click');
                return;
            }

            const seedId = $(this).data('seed-id');
            if (!seedId) {
                console.log('No seed ID found');
                return;
            }

            console.log('Opening seed detail for ID: ' + seedId);
            openSeedDetail(seedId);
        });

        // Handle clicking on the view details button specifically - use document for AJAX loaded content
        $(document).on('click', '.esc-enhanced-catalog .esc-view-details', function(e) {
            e.stopPropagation(); // Prevent the card click handler from firing
            console.log('View details button clicked');

            const seedId = $(this).closest('.esc-seed-card').data('seed-id');
            if (!seedId) {
                console.log('No seed ID found for view details button');
                return;
            }

            console.log('Opening seed detail from view button for ID: ' + seedId);
            openSeedDetail(seedId);
        });

        // Close modal when clicking the close button - use document for better event delegation
        $(document).on('click', '.esc-modal-close', function() {
            console.log('Close button clicked');
            closeModal();
        });

        // Close modal when clicking outside the content - use document for better event delegation
        $(document).on('click', '#esc-seed-detail-modal', function(e) {
            if ($(e.target).is('#esc-seed-detail-modal')) {
                console.log('Modal background clicked');
                closeModal();
            }
        });

        // Close modal with escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $modal.hasClass('show')) {
                closeModal();
            }
        });

        // Function to open seed detail modal
        function openSeedDetail(seedId) {
            console.log('Opening seed detail modal for ID: ' + seedId);

            // Get fresh references to modal elements
            const $modal = $('#esc-seed-detail-modal');
            const $modalContent = $('#esc-seed-detail-content');
            const $body = $('body');

            // Show loading state in modal
            $modalContent.html('<div class="esc-loading">' + (esc_ajax_object.loading_text || 'Loading...') + '</div>');

            // Make sure modal is visible with proper styling
            $modal.css('display', 'block').addClass('show');
            
            // Scroll modal to top
            $modal.scrollTop(0);
            
            // Prevent body scrolling when modal is open
            $body.css('overflow', 'hidden');

            // Fetch seed details
            $.ajax({
                url: esc_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'esc_get_seed_details',
                    nonce: esc_ajax_object.nonce,
                    seed_id: seedId,
                    enhanced: true // Flag to use enhanced template
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $modalContent.html(response.data.html);
                        
                        // Ensure modal scrolls to top after content is loaded
                        setTimeout(function() {
                            $modal.scrollTop(0);
                        }, 100);

                        // Add to browser history so back button works
                        if (window.history && window.history.pushState) {
                            const url = new URL(window.location);
                            url.searchParams.set('seed_id', seedId);
                            window.history.pushState({ seedId: seedId }, '', url);
                        }
                    } else {
                        $modalContent.html('<div class="esc-error">' + (response.data.message || 'Error loading seed details.') + '</div>');
                    }
                },
                error: function() {
                    $modalContent.html('<div class="esc-error">' + (esc_ajax_object.error_text || 'An error occurred.') + '</div>');
                }
            });
        }

        // Function to close the modal
        function closeModal() {
            console.log('Closing modal');

            // Get fresh references to modal elements
            const $modal = $('#esc-seed-detail-modal');
            const $body = $('body');

            // Hide modal with proper styling
            $modal.removeClass('show').css('display', 'none');

            // Restore body scrolling
            $body.css('overflow', '');

            // Remove seed_id from URL
            if (window.history && window.history.pushState) {
                const url = new URL(window.location);
                url.searchParams.delete('seed_id');
                window.history.pushState({}, '', url);
            }
        }

        // Handle browser back/forward buttons
        $(window).on('popstate', function(e) {
            if (e.originalEvent.state && e.originalEvent.state.seedId) {
                openSeedDetail(e.originalEvent.state.seedId);
            } else if ($modal.hasClass('show')) {
                closeModal();
            }
        });

        // Check if there's a seed_id in the URL on page load
        const urlParams = new URLSearchParams(window.location.search);
        const seedIdParam = urlParams.get('seed_id');
        if (seedIdParam) {
            openSeedDetail(seedIdParam);
        }

        // Debug helpers (only active if debug mode is enabled)
        const debugMode = (window.escDebugMode === true) || false;
        
        function debugLog(...args) {
            if (debugMode) {
                console.log('[ESC Debug]', ...args);
            }
        }
        
        // Initialize debug mode if needed
        function initDebugMode() {
            if (!debugMode) return;
            
            debugLog('Debug mode enabled for enhanced seed cards');
            
            // Add debug overlay to the modal
            const $modal = $('#esc-seed-detail-modal');
            if ($modal.length) {
                $modal.append('<div class="esc-debug-overlay" style="position:fixed; top:5px; left:5px; background:rgba(0,0,0,0.7); color:white; padding:5px 10px; font-size:12px; z-index:10000; border-radius:4px;">Debug Mode</div>');
                
                // Log modal events
                $modal.on('click', function(e) {
                    debugLog('Modal clicked at', e.clientX, e.clientY);
                    debugLog('Modal scroll position', $modal.scrollTop());
                    debugLog('Window size', window.innerWidth, window.innerHeight);
                });
            }
        }
        
        // Call debug init if enabled
        if (debugMode) {
            initDebugMode();
        }
    }

    // Initialize search form
    function initSearchForm() {
        const $searchForm = $('#esc-search-form');
        const $resultsContainer = $('#esc-catalog-results');

        // Handle form submission
        $searchForm.on('submit', function(e) {
            e.preventDefault();
            loadSeeds(1);
        });

        // Handle category filter change - only auto-submit if there's already a search term
        $searchForm.find('#esc-filter-category').on('change', function() {
            if ($searchForm.find('#esc-search-input').val() || $(this).val()) {
                loadSeeds(1);
            }
        });

        // Handle search input - add clear button when text is entered
        const $searchInput = $searchForm.find('#esc-search-input');
        const $searchWrapper = $searchForm.find('.esc-search-input-wrapper');
        const $clearButton = $('<button>', {
            type: 'button',
            class: 'esc-clear-input',
            'aria-label': 'Clear search',
            html: '&times;',
        }).hide();
        
        // Insert clear button inside the wrapper
        $searchWrapper.append($clearButton);
        
        // Show/hide clear button based on input content
        $searchInput.on('input', function() {
            $clearButton.toggle($(this).val().length > 0);
        });
        
        // Clear input when clear button clicked
        $clearButton.on('click', function() {
            $searchInput.val('').focus();
            $(this).hide();
        });
        
        // Initialize clear button state on page load
        $clearButton.toggle($searchInput.val().length > 0);

        // Enhance search form accessibility with keyboard shortcuts
        $searchForm.on('keydown', function(e) {
            // Submit form on Enter when focus is in the form
            if (e.key === 'Enter' && !$(e.target).is('textarea')) {
                if (!$(e.target).is('button[type="submit"]')) {
                    e.preventDefault();
                    $searchForm.find('button[type="submit"]').trigger('click');
                }
            }
            
            // Focus next element on Tab
            if (e.key === 'Tab') {
                // Allow natural tab order
            }
        });

        // Handle pagination clicks
        $resultsContainer.on('click', '.esc-pagination a', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            const page = href.match(/[?&]paged=(\d+)/) || href.match(/page\/(\d+)/);
            if (page && page[1]) {
                loadSeeds(parseInt(page[1], 10));
                
                // Scroll to top of results
                $('html, body').animate({
                    scrollTop: $resultsContainer.offset().top - 100
                }, 300);
            }
        });
        
        // Handle reset search from search summary
        $resultsContainer.on('click', '.esc-reset-search', function(e) {
            $searchForm.find('#esc-search-input').val('');
            $searchForm.find('#esc-filter-category').val('');
            $clearButton.hide();
            loadSeeds(1);
        });

        // Handle reset button click
        $searchForm.find('.esc-reset-button').on('click', function(e) {
            e.preventDefault(); // Prevent form submission 
            $searchForm.find('#esc-search-input').val('');
            $searchForm.find('#esc-filter-category').val('');
            $clearButton.hide();
            
            // Only reload if we had a previous search or filter
            const currentUrl = new URL(window.location);
            if (currentUrl.searchParams.has('s_seed') || 
                currentUrl.searchParams.has('seed_category') || 
                currentUrl.searchParams.has('paged')) {
                loadSeeds(1);
            }
        });

        // Handle browser back/forward button
        $(window).on('popstate', function(e) {
            if (e.originalEvent.state) {
                const state = e.originalEvent.state;
                
                // Update form fields to match URL state
                if (state.search !== undefined) {
                    $searchForm.find('#esc-search-input').val(state.search);
                    $clearButton.toggle(state.search.length > 0);
                }
                
                if (state.category !== undefined) {
                    $searchForm.find('#esc-filter-category').val(state.category);
                }
                
                // Only reload if not showing a seed modal (handled by other popstate listener)
                const seedModal = $('#esc-seed-detail-modal');
                if (!seedModal.hasClass('show')) {
                    loadSeeds(state.page || 1);
                }
            }
        });
        
        // Add keyboard accessibility for search input
        $searchInput.on('keydown', function(e) {
            // If Escape key is pressed and input has value, clear it
            if (e.key === 'Escape' && $(this).val()) {
                e.preventDefault(); // Prevent closing modal/other behaviors
                $(this).val('');
                $clearButton.hide();
            }
        });
    }

    // Function to load seeds via AJAX
    function loadSeeds(page = 1) {
        const $resultsContainer = $('#esc-catalog-results');
        const $searchForm = $('#esc-search-form');

        // Show loading state
        $resultsContainer.addClass('esc-loading-state');
        $resultsContainer.html('<div class="esc-loading"><div class="esc-loading-spinner"></div><p>' + (esc_ajax_object.loading_text || 'Loading...') + '</p></div>');
        
        // Disable search button during load
        const $searchButton = $searchForm.find('button[type="submit"]');
        const originalButtonText = $searchButton.text();
        $searchButton.prop('disabled', true).addClass('esc-button-loading').text('Searching...');

        let ajaxData = {            action: 'esc_filter_seeds',
            nonce: esc_ajax_object.nonce,
            paged: page,
            enhanced: true,
            per_page: 12
        };

        // Get search/filter values
        if ($searchForm.length > 0) {
            ajaxData.search = $searchForm.find('#esc-search-input').val() || '';
            ajaxData.category = $searchForm.find('#esc-filter-category').val() || '';
        }

        $.ajax({
            url: esc_ajax_object.ajax_url,
            type: 'POST',
            data: ajaxData,
            dataType: 'json',
            success: function(response) {
                $resultsContainer.removeClass('esc-loading-state');
                
                if (response.success) {
                    $resultsContainer.html(response.data.html);
                          // Add search result summary at the top if a search was performed
                if (ajaxData.search || ajaxData.category) {
                    const summaryHtml = createSearchSummary(
                        response.data.total_found, 
                        ajaxData.search, 
                        ajaxData.category,
                        response.data.category_name || ''
                    );
                    $resultsContainer.find('.esc-seed-list').before(summaryHtml);
                }
                    
                    // Update browser URL for better history management
                    if (window.history && window.history.pushState) {
                        const url = new URL(window.location);
                        if (ajaxData.search) url.searchParams.set('s_seed', ajaxData.search);
                        else url.searchParams.delete('s_seed');
                        
                        if (ajaxData.category) url.searchParams.set('seed_category', ajaxData.category);
                        else url.searchParams.delete('seed_category');
                        
                        if (page > 1) url.searchParams.set('paged', page);
                        else url.searchParams.delete('paged');
                        
                        window.history.pushState({search: ajaxData.search, category: ajaxData.category, page: page}, '', url);
                    }
                } else {
                    $resultsContainer.html('<div class="esc-error"><p>' + (response.data.message || 'Error loading seeds.') + '</p></div>');
                    console.error("Filter Seeds Error:", response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $resultsContainer.removeClass('esc-loading-state');
                $resultsContainer.html('<div class="esc-error"><p>' + (esc_ajax_object.error_text || 'An error occurred.') + '</p></div>');
                console.error("AJAX Error:", textStatus, errorThrown);
            },
            complete: function() {
                // Re-enable search button and restore text
                $searchButton.prop('disabled', false).removeClass('esc-button-loading').text(originalButtonText);
            }
        });
    }

    // Helper function to create search summary text
    function createSearchSummary(totalFound, searchTerm, categoryId, categoryName) {
        let summaryText = '';
        
        if (totalFound === 0) {
            summaryText = 'No seeds found';
            if (searchTerm) summaryText += ' matching "' + searchTerm + '"';
        } else {
            summaryText = 'Found ' + totalFound + ' seed' + (totalFound !== 1 ? 's' : '');
            if (searchTerm) summaryText += ' matching "' + searchTerm + '"';
        }
        
        if (categoryId) {
            // Use the category name from the response if available, otherwise try to get it from the select element
            let catName = categoryName;
            if (!catName) {
                catName = $('#esc-filter-category option[value="' + categoryId + '"]').text();
            }
            
            if (catName) {
                summaryText += ' in category "' + catName + '"';
            }
        }
        
        return '<div class="esc-search-summary">' + summaryText + 
               '<button type="button" class="esc-reset-search">Clear search</button></div>';
    }

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('Enhanced cards script initialized');
        initEnhancedCards();

        // Check for search parameters in URL and initialize search if needed
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('s_seed');
        const categoryParam = urlParams.get('seed_category');
        const pagedParam = urlParams.get('paged');
        
        const $searchForm = $('#esc-search-form');
        if ($searchForm.length && (searchParam || categoryParam)) {
            // Set form values from URL parameters
            if (searchParam) {
                $searchForm.find('#esc-search-input').val(searchParam);
            }
            
            if (categoryParam) {
                $searchForm.find('#esc-filter-category').val(categoryParam);
            }
            
            // If we have search parameters but haven't loaded results yet, trigger a search
            if ((searchParam || categoryParam) && !$('#esc-catalog-results').data('searched')) {
                console.log('URL has search parameters, triggering search');
                loadSeeds(pagedParam ? parseInt(pagedParam, 10) : 1);
                $('#esc-catalog-results').data('searched', true);
            }
        }

        // Add a test click handler to verify event binding
        console.log('Adding test click handler to seed cards');
        $('.esc-seed-card').on('click', function() {
            console.log('Direct click handler fired for card: ' + $(this).data('seed-id'));
        });

        // Add keyboard navigation support
        $('.esc-seed-card').attr('tabindex', '0');
        $(document).on('keydown', '.esc-seed-card', function(e) {
            // Open card on Enter or Space
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const seedId = $(this).data('seed-id');
                if (seedId) {
                    console.log('Opening seed detail via keyboard for ID: ' + seedId);
                    openSeedDetail(seedId);
                }
            }
        });

        // Enhance image loading experience
        $('.esc-seed-image').on('load', function() {
            $(this).addClass('esc-loaded');
            $(this).closest('.esc-seed-image-container').addClass('esc-image-loaded');
        }).on('error', function() {
            $(this).closest('.esc-seed-image-container').addClass('esc-image-error');
            console.error('Image failed to load: ' + $(this).attr('src'));
        });
        
        // Initialize images that are already loaded
        $('.esc-seed-image').each(function() {
            if (this.complete) {
                $(this).trigger('load');
            }
        });
    });

})(jQuery);

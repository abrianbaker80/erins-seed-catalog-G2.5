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

        // Handle category filter change
        $searchForm.find('#esc-filter-category').on('change', function() {
            loadSeeds(1);
        });

        // Handle pagination clicks
        $resultsContainer.on('click', '.esc-pagination a', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            const page = href.match(/\?paged=(\d+)/) || href.match(/page\/(\d+)/);
            if (page && page[1]) {
                loadSeeds(parseInt(page[1], 10));
            }
        });
    }

    // Function to load seeds via AJAX
    function loadSeeds(page = 1) {
        const $resultsContainer = $('#esc-catalog-results');
        const $searchForm = $('#esc-search-form');

        $resultsContainer.html('<div class="esc-loading">' + (esc_ajax_object.loading_text || 'Loading...') + '</div>');

        let ajaxData = {
            action: 'esc_filter_seeds',
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
                if (response.success) {
                    $resultsContainer.html(response.data.html);
                } else {
                    $resultsContainer.html('<p class="esc-no-results error">' + (response.data.message || 'Error loading seeds.') + '</p>');
                    console.error("Filter Seeds Error:", response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $resultsContainer.html('<p class="esc-no-results error">' + (esc_ajax_object.error_text || 'An error occurred.') + '</p>');
                console.error("AJAX Error:", textStatus, errorThrown);
            }
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('Enhanced cards script initialized');
        initEnhancedCards();

        // Add a test click handler to verify event binding
        console.log('Adding test click handler to seed cards');
        $('.esc-seed-card').on('click', function() {
            console.log('Direct click handler fired for card: ' + $(this).data('seed-id'));
        });
    });

})(jQuery);

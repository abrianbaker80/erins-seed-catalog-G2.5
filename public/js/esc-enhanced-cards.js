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
        
        // Handle clicking on seed cards
        $('.esc-seed-list').on('click', '.esc-seed-card', function(e) {
            // Don't trigger if clicking on a link inside the card
            if ($(e.target).closest('a').length) {
                return;
            }
            
            const seedId = $(this).data('seed-id');
            if (!seedId) return;
            
            openSeedDetail(seedId);
        });
        
        // Handle clicking on the view details button specifically
        $('.esc-seed-list').on('click', '.esc-view-details', function(e) {
            e.stopPropagation(); // Prevent the card click handler from firing
            
            const seedId = $(this).closest('.esc-seed-card').data('seed-id');
            if (!seedId) return;
            
            openSeedDetail(seedId);
        });
        
        // Close modal when clicking the close button
        $modal.on('click', '.esc-modal-close', function() {
            closeModal();
        });
        
        // Close modal when clicking outside the content
        $modal.on('click', function(e) {
            if ($(e.target).is($modal)) {
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
            // Show loading state in modal
            $modalContent.html('<div class="esc-loading">' + (esc_ajax_object.loading_text || 'Loading...') + '</div>');
            $modal.fadeIn(200).addClass('show');
            
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
            $modal.removeClass('show').fadeOut(200);
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
    
    // Initialize when document is ready
    $(document).ready(function() {
        initEnhancedCards();
    });
    
})(jQuery);

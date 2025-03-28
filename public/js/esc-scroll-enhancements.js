/**
 * Scroll Enhancements for Erin's Seed Catalog
 */
(function($) {
    'use strict';
    
    // Initialize scroll enhancements
    function initScrollEnhancements() {
        // Add scroll progress indicator to the page
        $('body').append('<div class="esc-scroll-progress"></div>');
        
        // Add scroll-to-top button
        $('body').append('<div class="esc-scroll-indicator" title="Scroll to top"><span class="dashicons dashicons-arrow-up-alt"></span></div>');
        
        // Handle scroll events
        $(window).on('scroll', handleScroll);
        
        // Handle click on scroll-to-top button
        $('.esc-scroll-indicator').on('click', function() {
            scrollToTop();
        });
        
        // Initial check
        handleScroll();
    }
    
    // Handle scroll events
    function handleScroll() {
        // Update scroll progress indicator
        updateScrollProgress();
        
        // Show/hide scroll-to-top button
        toggleScrollIndicator();
    }
    
    // Update scroll progress indicator
    function updateScrollProgress() {
        const scrollTop = $(window).scrollTop();
        const docHeight = $(document).height();
        const winHeight = $(window).height();
        const scrollPercent = (scrollTop / (docHeight - winHeight)) * 100;
        $('.esc-scroll-progress').css('width', scrollPercent + '%');
    }
    
    // Toggle scroll-to-top button visibility
    function toggleScrollIndicator() {
        const scrollTop = $(window).scrollTop();
        const threshold = 300; // Show button after scrolling down 300px
        
        if (scrollTop > threshold) {
            $('.esc-scroll-indicator').addClass('visible');
        } else {
            $('.esc-scroll-indicator').removeClass('visible');
        }
    }
    
    // Scroll to top with animation
    function scrollToTop() {
        $('html, body').animate({
            scrollTop: 0
        }, 800, 'swing');
    }
    
    // Add highlight effect to the section being scrolled to
    function highlightSection(selector) {
        $(selector).addClass('esc-highlight-section');
        
        // Remove the class after animation completes
        setTimeout(function() {
            $(selector).removeClass('esc-highlight-section');
        }, 1000);
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        // Only initialize on pages with the modern form
        if ($('.esc-modern-form').length) {
            initScrollEnhancements();
        }
    });
    
})(jQuery);

/**
 * Erin's Seed Catalog - UI Module
 * 
 * Handles all UI-related functionality including:
 * - Component initialization
 * - UI state management
 * - Animations and transitions
 */

// Add the UI module to our ESC namespace
ESC.UI = (function($) {
    'use strict';

    // Private variables
    let _initialized = false;
    let _observers = [];

    // Private methods
    function _initComponents() {
        ESC.log('Initializing UI components');
        
        // Initialize floating labels
        _initFloatingLabels();
        
        // Initialize cards
        _initCards();
        
        // Initialize form phases
        _initFormPhases();
        
        // Initialize tooltips
        _initTooltips();
        
        // Initialize animations
        _initAnimations();
        
        // Initialize responsive adjustments
        _initResponsive();
    }

    function _initFloatingLabels() {
        ESC.log('Initializing floating labels');
        
        // Find all floating label containers
        $('.esc-floating-label').each(function() {
            const $container = $(this);
            const $input = $container.find('input, textarea');
            const $label = $container.find('label');
            
            // Skip if already initialized
            if ($container.hasClass('esc-floating-label--initialized')) {
                return;
            }
            
            // Add necessary classes
            $input.addClass('esc-floating-label__input');
            $label.addClass('esc-floating-label__label');
            
            // Set initial state based on input value
            if ($input.val()) {
                $container.addClass('esc-floating-label--active');
            }
            
            // Add event listeners
            $input.on('focus', function() {
                $container.addClass('esc-floating-label--active');
            }).on('blur', function() {
                if (!$input.val()) {
                    $container.removeClass('esc-floating-label--active');
                }
            });
            
            // Mark as initialized
            $container.addClass('esc-floating-label--initialized');
        });
    }

    function _initCards() {
        ESC.log('Initializing cards');
        
        // Find all cards
        $('.esc-card').each(function() {
            const $card = $(this);
            
            // Skip if already initialized
            if ($card.hasClass('esc-card--initialized')) {
                return;
            }
            
            // Add toggle functionality if needed
            const $header = $card.find('.esc-card__header');
            const $content = $card.find('.esc-card__content');
            const $toggle = $header.find('.esc-card__toggle');
            
            if ($toggle.length) {
                $toggle.on('click', function(e) {
                    e.preventDefault();
                    $card.toggleClass('esc-card--collapsed');
                    $content.slideToggle(300);
                });
            }
            
            // Mark as initialized
            $card.addClass('esc-card--initialized');
        });
    }

    function _initFormPhases() {
        ESC.log('Initializing form phases');
        
        // Find all phase containers
        const $phases = $('.esc-phase');
        
        // Skip if no phases found
        if (!$phases.length) {
            return;
        }
        
        // Initialize phase navigation
        $('.esc-phase-nav__item').on('click', function(e) {
            e.preventDefault();
            
            const $navItem = $(this);
            const targetPhase = $navItem.data('target');
            
            if (targetPhase) {
                _showPhase(targetPhase);
            }
        });
        
        // Phase transition buttons
        $('[data-phase-target]').on('click', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const targetPhase = $button.data('phase-target');
            
            if (targetPhase) {
                _showPhase(targetPhase);
            }
        });
    }

    function _showPhase(phaseId) {
        ESC.log('Showing phase:', phaseId);
        
        const $allPhases = $('.esc-phase');
        const $targetPhase = $('#' + phaseId);
        
        if (!$targetPhase.length) {
            ESC.error('Target phase not found:', phaseId);
            return;
        }
        
        // Hide all phases
        $allPhases.hide().removeClass('active');
        
        // Show target phase with animation
        $targetPhase.show().addClass('active');
        
        // Update navigation if exists
        $('.esc-phase-nav__item').removeClass('active');
        $('.esc-phase-nav__item[data-target="' + phaseId + '"]').addClass('active');
        
        // Scroll to top of the phase
        $('html, body').animate({
            scrollTop: $targetPhase.offset().top - 20
        }, 300);
        
        // Trigger phase change event
        $(document).trigger('esc:phaseChanged', [phaseId]);
    }

    function _initTooltips() {
        ESC.log('Initializing tooltips');
        
        // Find all elements with tooltips
        $('[data-tooltip]').each(function() {
            const $element = $(this);
            
            // Skip if already initialized
            if ($element.hasClass('esc-tooltip--initialized')) {
                return;
            }
            
            const tooltipText = $element.data('tooltip');
            
            // Create tooltip element
            const $tooltip = $('<div class="esc-tooltip">' + tooltipText + '</div>');
            $element.append($tooltip);
            
            // Position tooltip
            const position = $element.data('tooltip-position') || 'top';
            $tooltip.addClass('esc-tooltip--' + position);
            
            // Mark as initialized
            $element.addClass('esc-tooltip--initialized');
        });
    }

    function _initAnimations() {
        ESC.log('Initializing animations');
        
        // Add staggered animations to elements
        $('.esc-animate-stagger').each(function(index) {
            const $element = $(this);
            const delay = index * 100; // 100ms delay between each element
            
            $element.css('animation-delay', delay + 'ms');
        });
    }

    function _initResponsive() {
        ESC.log('Initializing responsive adjustments');
        
        // Handle responsive layout changes
        $(window).on('resize', function() {
            const windowWidth = $(window).width();
            
            if (windowWidth < 768) {
                $('body').addClass('esc-mobile');
            } else {
                $('body').removeClass('esc-mobile');
            }
        }).trigger('resize'); // Trigger once on init
    }

    function _setupMutationObserver() {
        ESC.log('Setting up mutation observer');
        
        // Create a mutation observer to watch for dynamically added elements
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    // Re-initialize components for new elements
                    _initComponents();
                }
            });
        });
        
        // Start observing the document body
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Store observer for cleanup
        _observers.push(observer);
    }

    function _init() {
        if (_initialized) {
            return;
        }
        
        ESC.log('Initializing UI module');
        
        // Initialize all UI components
        _initComponents();
        
        // Setup mutation observer for dynamic content
        _setupMutationObserver();
        
        // Setup event listeners
        $(document).on('esc:reinitUI', function() {
            ESC.log('Reinitializing UI components');
            _initComponents();
        });
        
        _initialized = true;
    }

    function _destroy() {
        ESC.log('Destroying UI module');
        
        // Disconnect all observers
        _observers.forEach(function(observer) {
            observer.disconnect();
        });
        
        _observers = [];
        _initialized = false;
    }

    // Public API
    return {
        init: _init,
        destroy: _destroy,
        showPhase: _showPhase,
        reinitComponents: _initComponents
    };
})(jQuery);

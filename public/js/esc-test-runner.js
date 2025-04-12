/**
 * Erin's Seed Catalog - Test Runner
 * 
 * This script helps automate testing of the refactored UI components.
 * It provides functions to test various aspects of the UI and compare
 * the original and refactored implementations.
 */

const ESCTestRunner = (function($) {
    'use strict';
    
    // Test configuration
    const config = {
        debug: true,
        logPrefix: '[ESC Test]',
        testDelay: 500, // Delay between test steps
        testTimeout: 10000, // Maximum time for a test to complete
    };
    
    // Test results
    let results = {
        passed: 0,
        failed: 0,
        skipped: 0,
        total: 0,
        tests: {}
    };
    
    // Log a message to the console
    function log(message, type = 'info') {
        if (!config.debug) return;
        
        const prefix = config.logPrefix;
        
        switch (type) {
            case 'error':
                console.error(prefix, message);
                break;
            case 'warn':
                console.warn(prefix, message);
                break;
            case 'success':
                console.log(prefix + ' %c' + message, 'color: green; font-weight: bold;');
                break;
            default:
                console.log(prefix, message);
        }
    }
    
    // Run a test
    function runTest(testName, testFn, options = {}) {
        log(`Running test: ${testName}`);
        
        // Initialize test result
        results.total++;
        results.tests[testName] = {
            name: testName,
            status: 'running',
            startTime: Date.now(),
            endTime: null,
            duration: null,
            error: null,
            details: {}
        };
        
        // Set timeout for test
        const timeoutId = setTimeout(() => {
            results.tests[testName].status = 'failed';
            results.tests[testName].error = 'Test timed out';
            results.tests[testName].endTime = Date.now();
            results.tests[testName].duration = results.tests[testName].endTime - results.tests[testName].startTime;
            results.failed++;
            
            log(`Test "${testName}" timed out after ${config.testTimeout}ms`, 'error');
            
            // Call the callback if provided
            if (options.callback) {
                options.callback(results.tests[testName]);
            }
        }, options.timeout || config.testTimeout);
        
        // Run the test
        try {
            const promise = testFn();
            
            // Handle promise if returned
            if (promise && typeof promise.then === 'function') {
                promise
                    .then((result) => {
                        clearTimeout(timeoutId);
                        
                        results.tests[testName].status = 'passed';
                        results.tests[testName].endTime = Date.now();
                        results.tests[testName].duration = results.tests[testName].endTime - results.tests[testName].startTime;
                        results.tests[testName].details = result || {};
                        results.passed++;
                        
                        log(`Test "${testName}" passed in ${results.tests[testName].duration}ms`, 'success');
                        
                        // Call the callback if provided
                        if (options.callback) {
                            options.callback(results.tests[testName]);
                        }
                    })
                    .catch((error) => {
                        clearTimeout(timeoutId);
                        
                        results.tests[testName].status = 'failed';
                        results.tests[testName].error = error.message || error;
                        results.tests[testName].endTime = Date.now();
                        results.tests[testName].duration = results.tests[testName].endTime - results.tests[testName].startTime;
                        results.failed++;
                        
                        log(`Test "${testName}" failed: ${error.message || error}`, 'error');
                        
                        // Call the callback if provided
                        if (options.callback) {
                            options.callback(results.tests[testName]);
                        }
                    });
            } else {
                // Handle synchronous test
                clearTimeout(timeoutId);
                
                results.tests[testName].status = 'passed';
                results.tests[testName].endTime = Date.now();
                results.tests[testName].duration = results.tests[testName].endTime - results.tests[testName].startTime;
                results.passed++;
                
                log(`Test "${testName}" passed in ${results.tests[testName].duration}ms`, 'success');
                
                // Call the callback if provided
                if (options.callback) {
                    options.callback(results.tests[testName]);
                }
            }
        } catch (error) {
            clearTimeout(timeoutId);
            
            results.tests[testName].status = 'failed';
            results.tests[testName].error = error.message || error;
            results.tests[testName].endTime = Date.now();
            results.tests[testName].duration = results.tests[testName].endTime - results.tests[testName].startTime;
            results.failed++;
            
            log(`Test "${testName}" failed: ${error.message || error}`, 'error');
            
            // Call the callback if provided
            if (options.callback) {
                options.callback(results.tests[testName]);
            }
        }
    }
    
    // Test the floating labels component
    function testFloatingLabels(container) {
        return new Promise((resolve, reject) => {
            const $container = $(container);
            const $inputs = $container.find('.esc-floating-label input, .esc-floating-label textarea');
            
            if ($inputs.length === 0) {
                reject(new Error('No floating label inputs found'));
                return;
            }
            
            // Test each input
            let passedCount = 0;
            let failedCount = 0;
            
            $inputs.each(function(index) {
                const $input = $(this);
                const $label = $input.siblings('label');
                
                // Test 1: Label should be visible
                if ($label.length === 0) {
                    failedCount++;
                    log(`Input #${index} has no label`, 'error');
                    return;
                }
                
                // Test 2: Input should have placeholder
                if (!$input.attr('placeholder')) {
                    failedCount++;
                    log(`Input #${index} has no placeholder`, 'error');
                    return;
                }
                
                // Test 3: Focus should move label
                $input.focus();
                setTimeout(() => {
                    const labelMoved = $label.hasClass('esc-floating-label__label') && 
                                      ($label.hasClass('active') || 
                                       $input.closest('.esc-floating-label').hasClass('esc-floating-label--active'));
                    
                    if (!labelMoved) {
                        failedCount++;
                        log(`Input #${index} label did not move on focus`, 'error');
                    } else {
                        passedCount++;
                    }
                    
                    // Test 4: Blur should reset label if empty
                    $input.blur();
                    setTimeout(() => {
                        const labelReset = $input.val() === '' && 
                                         (!$label.hasClass('active') || 
                                          !$input.closest('.esc-floating-label').hasClass('esc-floating-label--active'));
                        
                        if (!labelReset && $input.val() === '') {
                            failedCount++;
                            log(`Input #${index} label did not reset on blur`, 'error');
                        } else {
                            passedCount++;
                        }
                        
                        // If this is the last input, resolve the promise
                        if (index === $inputs.length - 1) {
                            if (failedCount > 0) {
                                reject(new Error(`${failedCount} floating label tests failed`));
                            } else {
                                resolve({
                                    inputsTested: $inputs.length,
                                    passedTests: passedCount
                                });
                            }
                        }
                    }, config.testDelay);
                }, config.testDelay);
            });
        });
    }
    
    // Test the form validation
    function testFormValidation(container) {
        return new Promise((resolve, reject) => {
            const $container = $(container);
            const $form = $container.find('form');
            
            if ($form.length === 0) {
                reject(new Error('No form found'));
                return;
            }
            
            // Find required fields
            const $requiredFields = $form.find('input[required], textarea[required], select[required]');
            
            if ($requiredFields.length === 0) {
                reject(new Error('No required fields found'));
                return;
            }
            
            // Clear all fields
            $requiredFields.val('');
            
            // Try to submit the form
            const $submitButton = $form.find('button[type="submit"], input[type="submit"]');
            
            if ($submitButton.length === 0) {
                reject(new Error('No submit button found'));
                return;
            }
            
            // Intercept form submission
            let formSubmitted = false;
            let validationTriggered = false;
            
            $form.on('submit.test', function(e) {
                e.preventDefault();
                formSubmitted = true;
            });
            
            // Click the submit button
            $submitButton.trigger('click');
            
            // Check if validation was triggered
            setTimeout(() => {
                // Look for error messages or error classes
                const $errorMessages = $form.find('.esc-form-error, .error-message, .invalid-feedback');
                const $errorFields = $form.find('.esc-form-input--error, .is-invalid, .error');
                
                validationTriggered = $errorMessages.length > 0 || $errorFields.length > 0;
                
                if (!validationTriggered) {
                    // If no visible validation, check if the form was prevented from submitting
                    validationTriggered = !formSubmitted;
                }
                
                // Clean up
                $form.off('submit.test');
                
                if (validationTriggered) {
                    resolve({
                        requiredFields: $requiredFields.length,
                        errorMessages: $errorMessages.length,
                        errorFields: $errorFields.length
                    });
                } else {
                    reject(new Error('Form validation was not triggered'));
                }
            }, config.testDelay);
        });
    }
    
    // Test the variety suggestions
    function testVarietySuggestions(container) {
        return new Promise((resolve, reject) => {
            const $container = $(container);
            const $seedNameInput = $container.find('#esc_seed_name');
            const $varietyInput = $container.find('#esc_variety_name');
            const $varietyDropdown = $container.find('#esc-variety-dropdown');
            
            if ($seedNameInput.length === 0 || $varietyInput.length === 0) {
                reject(new Error('Seed name or variety input not found'));
                return;
            }
            
            // Set seed name to trigger suggestions
            $seedNameInput.val('Tomato').trigger('input');
            
            // Focus variety input to trigger suggestions
            $varietyInput.focus();
            
            // Check if dropdown appears
            setTimeout(() => {
                const dropdownVisible = $varietyDropdown.is(':visible') || 
                                       $varietyDropdown.children().length > 0;
                
                if (dropdownVisible) {
                    resolve({
                        seedName: $seedNameInput.val(),
                        suggestionsShown: true,
                        suggestionsCount: $varietyDropdown.children().length
                    });
                } else {
                    // Check if there's a loading indicator
                    const $loadingIndicator = $container.find('.esc-variety-loading');
                    const loadingVisible = $loadingIndicator.is(':visible');
                    
                    if (loadingVisible) {
                        // Wait a bit longer for suggestions to load
                        setTimeout(() => {
                            const dropdownVisibleAfterLoading = $varietyDropdown.is(':visible') || 
                                                              $varietyDropdown.children().length > 0;
                            
                            if (dropdownVisibleAfterLoading) {
                                resolve({
                                    seedName: $seedNameInput.val(),
                                    suggestionsShown: true,
                                    suggestionsCount: $varietyDropdown.children().length,
                                    loadingShown: true
                                });
                            } else {
                                reject(new Error('Variety suggestions did not appear after loading'));
                            }
                        }, config.testDelay * 2);
                    } else {
                        reject(new Error('Variety suggestions did not appear and no loading indicator was shown'));
                    }
                }
            }, config.testDelay);
        });
    }
    
    // Test the AI fetch functionality
    function testAIFetch(container) {
        return new Promise((resolve, reject) => {
            const $container = $(container);
            const $seedNameInput = $container.find('#esc_seed_name');
            const $varietyInput = $container.find('#esc_variety_name');
            const $aiFetchButton = $container.find('#esc-ai-fetch-trigger');
            
            if ($seedNameInput.length === 0 || $aiFetchButton.length === 0) {
                reject(new Error('Seed name input or AI fetch button not found'));
                return;
            }
            
            // Set seed name and variety
            $seedNameInput.val('Tomato').trigger('input');
            $varietyInput.val('Brandywine').trigger('input');
            
            // Click the AI fetch button
            $aiFetchButton.trigger('click');
            
            // Check if loading state appears
            setTimeout(() => {
                const $loadingState = $container.find('.esc-ai-loading, .loading-indicator');
                const loadingVisible = $loadingState.is(':visible');
                
                if (loadingVisible) {
                    resolve({
                        seedName: $seedNameInput.val(),
                        variety: $varietyInput.val(),
                        loadingShown: true
                    });
                } else {
                    reject(new Error('AI fetch loading state did not appear'));
                }
            }, config.testDelay);
        });
    }
    
    // Run all tests
    function runAllTests(originalContainer, refactoredContainer, callback) {
        const tests = [
            {
                name: 'Floating Labels (Original)',
                fn: () => testFloatingLabels(originalContainer),
                skip: !$(originalContainer).length
            },
            {
                name: 'Floating Labels (Refactored)',
                fn: () => testFloatingLabels(refactoredContainer),
                skip: !$(refactoredContainer).length
            },
            {
                name: 'Form Validation (Original)',
                fn: () => testFormValidation(originalContainer),
                skip: !$(originalContainer).length
            },
            {
                name: 'Form Validation (Refactored)',
                fn: () => testFormValidation(refactoredContainer),
                skip: !$(refactoredContainer).length
            },
            {
                name: 'Variety Suggestions (Original)',
                fn: () => testVarietySuggestions(originalContainer),
                skip: !$(originalContainer).length
            },
            {
                name: 'Variety Suggestions (Refactored)',
                fn: () => testVarietySuggestions(refactoredContainer),
                skip: !$(refactoredContainer).length
            },
            {
                name: 'AI Fetch (Original)',
                fn: () => testAIFetch(originalContainer),
                skip: !$(originalContainer).length
            },
            {
                name: 'AI Fetch (Refactored)',
                fn: () => testAIFetch(refactoredContainer),
                skip: !$(refactoredContainer).length
            }
        ];
        
        // Reset results
        results = {
            passed: 0,
            failed: 0,
            skipped: 0,
            total: 0,
            tests: {}
        };
        
        // Run each test
        let testIndex = 0;
        
        function runNextTest() {
            if (testIndex >= tests.length) {
                // All tests complete
                log(`All tests complete: ${results.passed} passed, ${results.failed} failed, ${results.skipped} skipped`, 'info');
                
                if (callback) {
                    callback(results);
                }
                
                return;
            }
            
            const test = tests[testIndex];
            testIndex++;
            
            if (test.skip) {
                results.skipped++;
                log(`Skipping test: ${test.name}`, 'warn');
                
                // Run next test
                setTimeout(runNextTest, 0);
                return;
            }
            
            runTest(test.name, test.fn, {
                callback: () => {
                    // Run next test after a delay
                    setTimeout(runNextTest, config.testDelay);
                }
            });
        }
        
        // Start running tests
        runNextTest();
    }
    
    // Public API
    return {
        runTest,
        testFloatingLabels,
        testFormValidation,
        testVarietySuggestions,
        testAIFetch,
        runAllTests,
        getResults: () => ({ ...results })
    };
})(jQuery);

// Initialize when document is ready
jQuery(document).ready(function($) {
    // Add test runner to global scope for debugging
    window.ESCTestRunner = ESCTestRunner;
    
    // Add test buttons
    $('#run-all-tests').on('click', function() {
        const $originalUI = $('.original-ui');
        const $refactoredUI = $('.refactored-ui');
        
        $('#test-results').show();
        $('#test-output').html('Running all tests...\n');
        
        ESCTestRunner.runAllTests(
            $originalUI.length ? '.original-ui' : null,
            $refactoredUI.length ? '.refactored-ui' : null,
            function(results) {
                $('#test-output').append(`\nTests complete: ${results.passed} passed, ${results.failed} failed, ${results.skipped} skipped\n\n`);
                
                // Add detailed results
                for (const testName in results.tests) {
                    const test = results.tests[testName];
                    $('#test-output').append(`${testName}: ${test.status.toUpperCase()} (${test.duration}ms)\n`);
                    
                    if (test.error) {
                        $('#test-output').append(`  Error: ${test.error}\n`);
                    }
                    
                    if (test.details && Object.keys(test.details).length > 0) {
                        $('#test-output').append(`  Details: ${JSON.stringify(test.details, null, 2)}\n`);
                    }
                }
            }
        );
    });
});

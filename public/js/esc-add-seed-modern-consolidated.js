jQuery(document).ready(function($) {
    'use strict';

    class ModernSeedForm {
        constructor() {
            this.$form = $('#esc-add-seed-form');
            this.$aiButton = $('#esc-ai-identify-btn');
            this.$imageInput = $('#seed_image');
            this.$imagePreview = $('#image-preview-container');
            this.isProcessing = false;
            
            this.init();
        }

        init() {
            this.bindEvents();
            this.initializeFloatingLabels();
            this.initializeImagePreview();
        }

        bindEvents() {
            // Form submission
            this.$form.on('submit', this.handleFormSubmit.bind(this));
            
            // AI identification
            this.$aiButton.on('click', this.handleAIIdentification.bind(this));
            
            // Image selection
            this.$imageInput.on('change', this.handleImageChange.bind(this));
            
            // Floating labels
            this.$form.on('focus', 'input, textarea, select', this.activateLabel.bind(this));
            this.$form.on('blur', 'input, textarea, select', this.deactivateLabel.bind(this));
            this.$form.on('change', 'input, textarea, select', this.checkLabelState.bind(this));
        }

        initializeFloatingLabels() {
            // Check all inputs on page load
            this.$form.find('input, textarea, select').each(function() {
                const $input = $(this);
                const $label = $input.siblings('.esc-floating-label');
                
                if ($input.val() || $input.is(':focus')) {
                    $label.addClass('active');
                }
            });
        }

        activateLabel(e) {
            $(e.target).siblings('.esc-floating-label').addClass('active');
        }

        deactivateLabel(e) {
            const $input = $(e.target);
            const $label = $input.siblings('.esc-floating-label');
            
            if (!$input.val()) {
                $label.removeClass('active');
            }
        }

        checkLabelState(e) {
            const $input = $(e.target);
            const $label = $input.siblings('.esc-floating-label');
            
            if ($input.val()) {
                $label.addClass('active');
            } else {
                $label.removeClass('active');
            }
        }

        handleImageChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.previewImage(file);
                this.$aiButton.prop('disabled', false);
            }
        }

        previewImage(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.$imagePreview.html(`
                    <img src="${e.target.result}" alt="Seed preview" class="image-preview">
                `);
            };
            reader.readAsDataURL(file);
        }

        handleAIIdentification(e) {
            e.preventDefault();
            
            if (this.isProcessing) return;
            
            const file = this.$imageInput[0].files[0];
            if (!file) {
                this.showError('Please select an image first');
                return;
            }

            this.startAIProcessing();
            
            const formData = new FormData();
            formData.append('action', 'esc_ai_identify_seed');
            formData.append('image', file);
            formData.append('nonce', escAjax.nonce);

            $.ajax({
                url: escAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: this.handleAISuccess.bind(this),
                error: this.handleAIError.bind(this),
                complete: this.stopAIProcessing.bind(this)
            });
        }

        startAIProcessing() {
            this.isProcessing = true;
            this.$aiButton.prop('disabled', true)
                          .html('<span class="spinner-border spinner-border-sm mr-2"></span>Identifying...');
            this.hideMessages();
        }

        stopAIProcessing() {
            this.isProcessing = false;
            this.$aiButton.prop('disabled', false)
                          .html('<i class="fas fa-magic mr-2"></i>AI Identify Seed');
        }

        handleAISuccess(response) {
            if (response.success && response.data) {
                this.handleAIResults(response.data);
            } else {
                this.showError(response.data?.message || 'AI identification failed');
            }
        }

        handleAIError(xhr, status, error) {
            console.error('AI Error:', error);
            this.showError('An error occurred during AI identification. Please try again.');
        }

        handleAIResults(results) {
            if (!results || typeof results !== 'object') {
                this.showError('Invalid AI results received');
                return;
            }

            // Enhanced field mapping with multiple selector support (from fixes)
            const fieldMappings = {
                'seed_name': ['#seed_name', '#esc_seed_name', 'input[name="seed_name"]'],
                'seed_type': ['#seed_type', '#esc_seed_type', 'input[name="seed_type"]'],
                'seed_variety': ['#seed_variety', '#esc_seed_variety', 'input[name="seed_variety"]'],
                'latin_name': ['#latin_name', '#esc_latin_name', 'input[name="latin_name"]'],
                'description': ['#description', '#esc_description', 'textarea[name="description"]'],
                'planting_instructions': ['#planting_instructions', '#esc_planting_instructions', 'textarea[name="planting_instructions"]'],
                'growing_tips': ['#growing_tips', '#esc_growing_tips', 'textarea[name="growing_tips"]'],
                'days_to_germination': ['#days_to_germination', '#esc_days_to_germination', 'input[name="days_to_germination"]'],
                'days_to_maturity': ['#days_to_maturity', '#esc_days_to_maturity', 'input[name="days_to_maturity"]'],
                'sun_requirements': ['#sun_requirements', '#esc_sun_requirements', 'select[name="sun_requirements"]'],
                'water_requirements': ['#water_requirements', '#esc_water_requirements', 'select[name="water_requirements"]'],
                'culinary_uses': ['#culinary_uses', '#esc_culinary_uses', 'textarea[name="culinary_uses"]'],
                'companion_plants': ['#companion_plants', '#esc_companion_plants', 'textarea[name="companion_plants"]'],
                'common_pests': ['#common_pests', '#esc_common_pests', 'textarea[name="common_pests"]'],
                'medicinal_properties': ['#medicinal_properties', '#esc_medicinal_properties', 'textarea[name="medicinal_properties"]']
            };

            let fieldsPopulated = 0;

            // Populate fields with enhanced error checking and label activation (from fixes)
            Object.entries(fieldMappings).forEach(([aiField, selectors]) => {
                if (results[aiField] !== undefined && results[aiField] !== null && results[aiField] !== '') {
                    let fieldFound = false;
                    
                    // Try each selector until we find the field
                    for (const selector of selectors) {
                        const $field = $(selector);
                        if ($field.length > 0) {
                            $field.val(results[aiField]);
                            
                            // Trigger events to ensure proper state
                            $field.trigger('change').trigger('blur');
                            
                            // Explicitly activate floating label (from fixes)
                            const $formGroup = $field.closest('.esc-form-group');
                            const $label = $formGroup.find('.esc-floating-label');
                            if ($label.length > 0) {
                                $label.addClass('active');
                            }
                            
                            fieldsPopulated++;
                            fieldFound = true;
                            break;
                        }
                    }
                    
                    if (!fieldFound) {
                        console.warn(`Could not find field for ${aiField}`);
                    }
                }
            });

            // Special handling for select fields (from fixes)
            if (results.sun_requirements) {
                this.setSelectValue('sun_requirements', results.sun_requirements);
            }
            if (results.water_requirements) {
                this.setSelectValue('water_requirements', results.water_requirements);
            }

            // Ensure all textareas with content have active labels (from fixes)
            this.$form.find('textarea').each(function() {
                const $textarea = $(this);
                if ($textarea.val().trim() !== '') {
                    $textarea.closest('.esc-form-group').find('.esc-floating-label').addClass('active');
                }
            });

            // Display confidence badge if available (from fixes)
            if (results.confidence) {
                this.showConfidenceBadge(results.confidence);
            }

            // Show success message
            if (fieldsPopulated > 0) {
                this.showSuccess(`AI successfully populated ${fieldsPopulated} fields`);
            } else {
                this.showWarning('AI could not identify this seed. Please fill in the details manually.');
            }

            // Scroll to first populated field (from fixes)
            this.scrollToFirstPopulatedField();
        }

        setSelectValue(fieldName, value) {
            const $select = $(`select[name="${fieldName}"], #${fieldName}, #esc_${fieldName}`);
            if ($select.length > 0) {
                // Try to find exact match first
                const $option = $select.find(`option[value="${value}"]`);
                if ($option.length > 0) {
                    $select.val(value);
                } else {
                    // Try case-insensitive match
                    $select.find('option').each(function() {
                        if ($(this).text().toLowerCase() === value.toLowerCase()) {
                            $select.val($(this).val());
                            return false;
                        }
                    });
                }
                $select.trigger('change');
                $select.closest('.esc-form-group').find('.esc-floating-label').addClass('active');
            }
        }

        showConfidenceBadge(confidence) {
            const confidenceClass = confidence >= 80 ? 'high' : confidence >= 60 ? 'medium' : 'low';
            const badgeHtml = `
                <div class="ai-confidence-badge confidence-${confidenceClass}">
                    <i class="fas fa-brain"></i>
                    ${confidence}% confidence
                </div>
            `;
            
            // Remove any existing badges
            $('.ai-confidence-badge').remove();
            
            // Add new badge
            this.$imagePreview.append(badgeHtml);
        }

        scrollToFirstPopulatedField() {
            const $firstPopulated = this.$form.find('input[value!=""], textarea:not(:empty), select').first();
            if ($firstPopulated.length > 0) {
                $('html, body').animate({
                    scrollTop: $firstPopulated.offset().top - 100
                }, 500);
            }
        }

        handleFormSubmit(e) {
            e.preventDefault();
            
            if (!this.validateForm()) {
                return;
            }

            this.submitForm();
        }

        validateForm() {
            let isValid = true;
            const errors = [];

            // Required field validation
            this.$form.find('[required]').each(function() {
                const $field = $(this);
                if (!$field.val()) {
                    isValid = false;
                    const label = $field.siblings('.esc-floating-label').text() || $field.attr('name');
                    errors.push(`${label} is required`);
                    $field.addClass('error');
                }
            });

            if (!isValid) {
                this.showError(errors.join('<br>'));
            }

            return isValid;
        }

        submitForm() {
            const formData = new FormData(this.$form[0]);
            formData.append('action', 'esc_add_seed');
            formData.append('nonce', escAjax.nonce);

            this.showLoading('Saving seed...');

            $.ajax({
                url: escAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: this.handleSubmitSuccess.bind(this),
                error: this.handleSubmitError.bind(this),
                complete: this.hideLoading.bind(this)
            });
        }

        handleSubmitSuccess(response) {
            if (response.success) {
                this.showSuccess('Seed added successfully!');
                setTimeout(() => {
                    window.location.href = response.data.redirect || '/seed-catalog';
                }, 1500);
            } else {
                this.showError(response.data?.message || 'Failed to add seed');
            }
        }

        handleSubmitError(xhr, status, error) {
            console.error('Submit Error:', error);
            this.showError('An error occurred while saving. Please try again.');
        }

        showMessage(message, type) {
            const alertClass = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            }[type] || 'alert-info';

            const messageHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `;

            $('#esc-messages').html(messageHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                $('#esc-messages .alert').fadeOut();
            }, 5000);
        }

        showSuccess(message) {
            this.showMessage(message, 'success');
        }

        showError(message) {
            this.showMessage(message, 'error');
        }

        showWarning(message) {
            this.showMessage(message, 'warning');
        }

        showLoading(message = 'Loading...') {
            this.$form.find('button[type="submit"]').prop('disabled', true)
                      .html(`<span class="spinner-border spinner-border-sm mr-2"></span>${message}`);
        }

        hideLoading() {
            this.$form.find('button[type="submit"]').prop('disabled', false)
                      .html('Add Seed');
        }

        hideMessages() {
            $('#esc-messages').empty();
        }

        initializeImagePreview() {
            // Initialize image preview if needed
            if (this.$imageInput.val()) {
                this.$aiButton.prop('disabled', false);
            }
        }
    }

    // Initialize the form
    new ModernSeedForm();
});

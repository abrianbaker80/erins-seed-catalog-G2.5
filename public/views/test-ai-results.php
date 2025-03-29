<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Test AI Results Page', 'erins-seed-catalog'); ?></h1>
    
    <p><?php esc_html_e('This page demonstrates the enhanced AI results display.', 'erins-seed-catalog'); ?></p>
    
    <div class="esc-container esc-modern-form">
        <h2><?php esc_html_e('Add New Seed to Catalog', 'erins-seed-catalog'); ?></h2>
        
        <div id="esc-form-messages" class="esc-message" style="display: none;"></div>
        
        <form id="esc-add-seed-form" class="esc-form" method="post">
            <!-- Phase 2: Review & Edit AI Results -->
            <div class="esc-phase esc-phase-review" id="esc-phase-review-edit">
                <div class="esc-ai-result-summary">
                    <div class="esc-ai-result-icon">
                        <span class="dashicons dashicons-yes-alt"></span>
                    </div>
                    <div class="esc-ai-result-text">
                        <h3><?php esc_html_e('AI Found Information for', 'erins-seed-catalog'); ?> <span id="esc-seed-display-name">Tomato (Brandywine)</span></h3>
                        <p><?php esc_html_e('Review the details below and make any necessary adjustments.', 'erins-seed-catalog'); ?></p>
                    </div>
                </div>
                
                <!-- Basic Information Card -->
                <div class="esc-form-card" data-ai-status="needs-review">
                    <div class="esc-card-header">
                        <h3><?php esc_html_e('Basic Information', 'erins-seed-catalog'); ?></h3>
                        <div class="esc-ai-status-badge">
                            <span class="dashicons dashicons-yes"></span>
                            <span class="esc-badge-text"><?php esc_html_e('AI Complete', 'erins-seed-catalog'); ?></span>
                        </div>
                    </div>
                    
                    <div class="esc-card-content">
                        <div class="esc-form-row">
                            <div class="esc-form-field">
                                <label for="esc_seed_name_review"><?php esc_html_e('Seed Type', 'erins-seed-catalog'); ?> <span class="required">*</span></label>
                                <input type="text" id="esc_seed_name_review" name="seed_name" value="Tomato" required>
                            </div>
                            
                            <div class="esc-form-field">
                                <label for="esc_variety_name_review"><?php esc_html_e('Variety', 'erins-seed-catalog'); ?></label>
                                <input type="text" id="esc_variety_name_review" name="variety_name" value="Brandywine">
                            </div>
                        </div>
                        
                        <div class="esc-form-row">
                            <div class="esc-form-field esc-full-width">
                                <label for="esc_description"><?php esc_html_e('Description', 'erins-seed-catalog'); ?></label>
                                <textarea id="esc_description" name="description">Brandywine tomatoes are large, pink-red heirloom tomatoes known for their rich, sweet flavor and meaty texture. They are a beefsteak variety that typically grows to 1-2 pounds per fruit. The plants are indeterminate with potato-leaf foliage.</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Growing Instructions Card -->
                <div class="esc-form-card" data-ai-status="fully-populated">
                    <div class="esc-card-header">
                        <h3><?php esc_html_e('Growing Instructions', 'erins-seed-catalog'); ?></h3>
                        <div class="esc-ai-status-badge">
                            <span class="dashicons dashicons-yes"></span>
                            <span class="esc-badge-text"><?php esc_html_e('AI Complete', 'erins-seed-catalog'); ?></span>
                        </div>
                    </div>
                    
                    <div class="esc-card-content">
                        <div class="esc-form-row">
                            <div class="esc-form-field">
                                <label for="esc_sowing_method"><?php esc_html_e('Sowing Method', 'erins-seed-catalog'); ?></label>
                                <select id="esc_sowing_method" name="sowing_method" class="esc-select">
                                    <option value=""><?php esc_html_e('-- Select --', 'erins-seed-catalog'); ?></option>
                                    <option value="Direct Sow"><?php esc_html_e('Direct Sow', 'erins-seed-catalog'); ?></option>
                                    <option value="Start Indoors" selected><?php esc_html_e('Start Indoors', 'erins-seed-catalog'); ?></option>
                                    <option value="Both"><?php esc_html_e('Both', 'erins-seed-catalog'); ?></option>
                                </select>
                            </div>
                            
                            <div class="esc-form-field">
                                <label for="esc_sowing_depth"><?php esc_html_e('Sowing Depth', 'erins-seed-catalog'); ?></label>
                                <input type="text" id="esc_sowing_depth" name="sowing_depth" value="1/4 inch">
                            </div>
                        </div>
                        
                        <div class="esc-form-row">
                            <div class="esc-form-field">
                                <label for="esc_sun_requirements"><?php esc_html_e('Sun Requirements', 'erins-seed-catalog'); ?></label>
                                <input type="text" id="esc_sun_requirements" name="sun_requirements" value="Full Sun">
                            </div>
                            
                            <div class="esc-form-field">
                                <label for="esc_watering"><?php esc_html_e('Watering Needs', 'erins-seed-catalog'); ?></label>
                                <textarea id="esc_watering" name="watering">Keep soil consistently moist but not waterlogged. Water deeply at the base of plants, avoiding wetting the foliage to prevent disease.</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Categories & Notes Card -->
                <div class="esc-form-card">
                    <div class="esc-card-header">
                        <h3><?php esc_html_e('Categories & Notes', 'erins-seed-catalog'); ?></h3>
                    </div>
                    
                    <div class="esc-card-content">
                        <div class="esc-form-row">
                            <div class="esc-form-field esc-full-width">
                                <label for="esc_seed_category"><?php esc_html_e('Seed Category', 'erins-seed-catalog'); ?></label>
                                <select id="esc_seed_category" name="esc_seed_category[]" multiple="multiple" class="esc-select-multiple">
                                    <option value="1" selected>Vegetables</option>
                                    <option value="2" selected>Tomatoes</option>
                                    <option value="3">Heirloom</option>
                                </select>
                                <p class="description"><?php esc_html_e('Select one or more relevant categories.', 'erins-seed-catalog'); ?></p>
                            </div>
                        </div>
                        
                        <div class="esc-form-row">
                            <div class="esc-form-field esc-full-width">
                                <label for="esc_notes"><?php esc_html_e('Personal Notes', 'erins-seed-catalog'); ?></label>
                                <textarea id="esc_notes" name="notes">Started seeds on March 15th. Germinated in 7 days. Transplanted outdoors on May 10th.</textarea>
                                <p class="description"><?php esc_html_e('Your own observations, planting dates, results, etc.', 'erins-seed-catalog'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="esc-form-actions">
                    <button type="button" id="esc-back-to-ai" class="esc-button esc-button-secondary">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                        <?php esc_html_e('Back to AI Search', 'erins-seed-catalog'); ?>
                    </button>
                    
                    <button type="submit" class="esc-button esc-button-primary" id="esc-submit-seed">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e('Submit Seed', 'erins-seed-catalog'); ?>
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Enhanced Confirmation Modal -->
        <div class="esc-confirmation-container">
            <div class="esc-confirmation-message">
                <div class="esc-confirmation-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <h2 class="esc-confirmation-title"><?php esc_html_e('Seed Submitted Successfully!', 'erins-seed-catalog'); ?></h2>
                <p class="esc-confirmation-text"><?php esc_html_e('Your seed has been added to the catalog.', 'erins-seed-catalog'); ?></p>
                <button class="esc-confirmation-button"><?php esc_html_e('Add Another Seed', 'erins-seed-catalog'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    // For demo purposes only
    jQuery(document).ready(function($) {
        // Show confirmation when clicking the submit button
        $('#esc-submit-seed').on('click', function(e) {
            e.preventDefault();
            $('.esc-confirmation-container').addClass('active');
        });
        
        // Hide confirmation when clicking the "Add Another Seed" button
        $('.esc-confirmation-button').on('click', function() {
            $('.esc-confirmation-container').removeClass('active');
        });
    });
</script>

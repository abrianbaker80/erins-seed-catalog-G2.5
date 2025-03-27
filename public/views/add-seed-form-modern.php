<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Force-load the CSS directly in case of enqueuing issues
$css_url = plugin_dir_url( dirname( __FILE__ ) ) . 'css/esc-modern-form.css';
$css_version = ESC_VERSION . '.' . time(); // Force cache refresh
echo '<link rel="stylesheet" href="' . esc_url( $css_url . '?ver=' . $css_version ) . '" type="text/css" media="all" />';

// Add critical inline styles to ensure proper display
?>
<style>
/* Critical inline styles to ensure proper display */
.esc-modern-form {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    max-width: 1400px;
    margin: 0 auto;
    color: #2d3748;
    padding: 30px;
    background-color: #f7fafc;
    border-radius: 12px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.03);
}

.esc-form-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    margin-bottom: 40px;
    overflow: hidden;
    transition: all 0.3s ease;
    border: 1px solid rgba(226, 232, 240, 0.8);
    position: relative;
}

.esc-form-card:hover {
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    transform: translateY(-4px);
    border-color: rgba(203, 213, 224, 0.9);
}

.esc-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 30px;
    background-color: #ffffff;
    border-bottom: 1px solid #edf2f7;
    position: relative;
}

.esc-card-content {
    padding: 35px 30px;
    position: relative;
}

.esc-form-row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -15px 35px;
    position: relative;
    align-items: flex-start;
}

.esc-form-field {
    flex: 1 1 calc(50% - 30px);
    margin: 0 15px 20px;
    min-width: 250px;
    position: relative;
}

.esc-modern-form input[type="text"],
.esc-modern-form input[type="url"],
.esc-modern-form input[type="number"],
.esc-modern-form input[type="date"],
.esc-modern-form textarea,
.esc-modern-form select {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    background-color: #fff;
    font-size: 16px;
    line-height: 1.5;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    color: #2d3748;
}

.esc-modern-form input[type="text"]:hover,
.esc-modern-form input[type="url"]:hover,
.esc-modern-form input[type="number"]:hover,
.esc-modern-form input[type="date"]:hover,
.esc-modern-form textarea:hover,
.esc-modern-form select:hover {
    border-color: #cbd5e0;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
}

.esc-modern-form input[type="text"]:focus,
.esc-modern-form input[type="url"]:focus,
.esc-modern-form input[type="number"]:focus,
.esc-modern-form input[type="date"]:focus,
.esc-modern-form textarea:focus,
.esc-modern-form select:focus {
    border-color: #4299e1;
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
    outline: none;
    transform: translateY(-2px);
}

.esc-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 1px 3px rgba(0, 0, 0, 0.1);
    letter-spacing: 0.3px;
}

.esc-button-primary {
    background-color: #4299e1;
    color: white;
    background-image: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
}

.esc-button-primary:hover {
    background-color: #3182ce;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(66, 153, 225, 0.2), 0 2px 4px rgba(66, 153, 225, 0.1);
}

.esc-floating-label {
    position: relative;
    margin-bottom: 24px;
}

.esc-floating-label input,
.esc-floating-label textarea,
.esc-floating-label select {
    height: 62px;
    padding: 28px 18px 12px;
    font-size: 16px;
    position: relative;
    z-index: 1;
    background-color: #fff;
    color: #2d3748;
    transition: all 0.3s ease;
    width: 100%;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.esc-floating-label label {
    position: absolute;
    top: 20px;
    left: 18px;
    transition: all 0.3s ease;
    pointer-events: none;
    color: #718096;
    margin: 0;
    background-color: transparent;
    z-index: 2;
    font-size: 16px;
    font-weight: 500;
}

.esc-floating-label input:focus + label,
.esc-floating-label input:not(:placeholder-shown) + label,
.esc-floating-label input.has-value + label,
.esc-floating-label textarea:focus + label,
.esc-floating-label textarea:not(:placeholder-shown) + label,
.esc-floating-label textarea.has-value + label,
.esc-floating-label select:focus + label,
.esc-floating-label select:not([value=""]):not([value="0"]) + label,
.esc-floating-label select.has-value + label {
    top: 10px;
    font-size: 12px;
    color: #4299e1;
    transform: translateY(-5px);
    font-weight: 600;
    letter-spacing: 0.5px;
}

.esc-floating-label textarea {
    height: auto;
    min-height: 120px;
    padding-top: 32px;
}

/* AI Card Styling */
.esc-ai-card {
    border-left: 4px solid #4299e1;
}

.esc-ai-card .esc-card-header {
    background-color: #ebf8ff;
}

/* Success and Error States */
.esc-ai-success, .esc-ai-error {
    display: flex;
    align-items: flex-start;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.esc-ai-success {
    background-color: #f0fff4;
    border: 1px solid #c6f6d5;
}

.esc-ai-error {
    background-color: #fff5f5;
    border: 1px solid #fed7d7;
}

.esc-success-icon, .esc-error-icon {
    margin-right: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    flex-shrink: 0;
}

.esc-success-icon {
    background-color: #9ae6b4;
    color: #22543d;
}

.esc-error-icon {
    background-color: #feb2b2;
    color: #742a2a;
}

/* Button Styling Enhancements */
.esc-button-secondary {
    background-color: #edf2f7;
    color: #4a5568;
    border: 1px solid #e2e8f0;
}

.esc-button-secondary:hover {
    background-color: #e2e8f0;
    color: #2d3748;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05), 0 2px 4px rgba(0, 0, 0, 0.05);
}

.esc-button-large {
    width: 100%;
    padding: 14px 20px;
    font-size: 16px;
}

/* Desktop Optimizations */
@media screen and (min-width: 1200px) {
    .esc-modern-form {
        padding: 40px 50px;
    }

    .esc-card-content {
        padding: 40px 40px;
    }

    .esc-form-row {
        margin: 0 -20px 40px;
    }

    .esc-form-field {
        flex: 1 1 calc(50% - 40px);
        margin: 0 20px 25px;
    }

    .esc-button-large {
        max-width: 400px;
        margin: 0 auto;
    }
}
</style>

<div id="esc-add-seed-form-container" class="esc-container esc-modern-form">
    <h2><?php esc_html_e( 'Add New Seed to Catalog', 'erins-seed-catalog' ); ?></h2>

    <div id="esc-form-messages" class="esc-message" style="display: none;"></div>

    <form id="esc-add-seed-form" class="esc-form" method="post">
        <!-- Phase 1: AI Input -->
        <div class="esc-phase esc-phase-ai active" id="esc-phase-ai-input">
            <div class="esc-form-card esc-ai-card">
                <div class="esc-card-header">
                    <h3><?php esc_html_e('Add Seed with AI Assistance', 'erins-seed-catalog'); ?></h3>
                </div>
                <div class="esc-card-content">
                    <p class="esc-card-description"><?php esc_html_e('Enter the seed type and variety to automatically retrieve detailed information.', 'erins-seed-catalog'); ?></p>

                    <div class="esc-floating-label">
                        <input type="text" id="esc_seed_name" name="seed_name" placeholder=" " required autocomplete="off">
                        <label for="esc_seed_name"><?php esc_html_e('Seed Type', 'erins-seed-catalog'); ?> <span class="required">*</span></label>
                        <p class="description"><?php esc_html_e('The main name, e.g., "Tomato", "Bean", "Zinnia".', 'erins-seed-catalog'); ?></p>
                    </div>

                    <div class="esc-variety-field-container">
                        <div class="esc-floating-label">
                            <input type="text" id="esc_variety_name" name="variety_name" placeholder=" " autocomplete="off">
                            <label for="esc_variety_name"><?php esc_html_e('Variety (Optional)', 'erins-seed-catalog'); ?></label>
                            <p class="description"><?php esc_html_e('Specific variety, e.g., "Brandywine", "Kentucky Wonder", "California Giant".', 'erins-seed-catalog'); ?></p>
                        </div>
                        <div id="esc-variety-dropdown" class="esc-variety-dropdown"></div>
                        <div class="esc-variety-loading" style="display: none;">
                            <span class="dashicons dashicons-update-alt esc-spin"></span> <?php esc_html_e('Loading varieties...', 'erins-seed-catalog'); ?>
                        </div>
                    </div>

                    <button type="button" id="esc-ai-fetch-trigger" class="esc-button esc-button-primary esc-button-large">
                        <span class="dashicons dashicons-superhero"></span>
                        <span><?php esc_html_e('Generate Seed Details with AI', 'erins-seed-catalog'); ?></span>
                    </button>

                    <div class="esc-ai-status-container">
                        <!-- Initial State -->
                        <div class="esc-ai-initial">
                            <p><?php esc_html_e('AI will search for detailed information about your seeds.', 'erins-seed-catalog'); ?></p>
                        </div>

                        <!-- Loading State with Stages -->
                        <div class="esc-ai-loading" style="display: none;">
                            <div class="esc-loading-animation">
                                <div class="esc-loading-pulse"></div>
                            </div>
                            <div class="esc-loading-stages">
                                <div class="esc-loading-stage active" data-stage="1"><?php esc_html_e('Searching seed databases...', 'erins-seed-catalog'); ?></div>
                                <div class="esc-loading-stage" data-stage="2"><?php esc_html_e('Gathering growing information...', 'erins-seed-catalog'); ?></div>
                                <div class="esc-loading-stage" data-stage="3"><?php esc_html_e('Compiling seed details...', 'erins-seed-catalog'); ?></div>
                            </div>
                        </div>

                        <!-- Success State -->
                        <div class="esc-ai-success" style="display: none;">
                            <div class="esc-success-icon">
                                <span class="dashicons dashicons-yes-alt"></span>
                            </div>
                            <div class="esc-success-message">
                                <h4><?php esc_html_e('Information Retrieved!', 'erins-seed-catalog'); ?></h4>
                                <p><?php esc_html_e('AI found details for', 'erins-seed-catalog'); ?> <strong id="esc-seed-name-display"></strong>.</p>
                                <p><?php esc_html_e('Review the information below and make any necessary adjustments.', 'erins-seed-catalog'); ?></p>
                            </div>
                        </div>

                        <!-- Error State -->
                        <div class="esc-ai-error" style="display: none;">
                            <div class="esc-error-icon">
                                <span class="dashicons dashicons-warning"></span>
                            </div>
                            <div class="esc-error-message">
                                <h4><?php esc_html_e('Couldn\'t Find Complete Information', 'erins-seed-catalog'); ?></h4>
                                <p><?php esc_html_e('Some details couldn\'t be found. You can:', 'erins-seed-catalog'); ?></p>
                                <ul>
                                    <li><?php esc_html_e('Try a different variety name', 'erins-seed-catalog'); ?></li>
                                    <li><?php esc_html_e('Check your spelling', 'erins-seed-catalog'); ?></li>
                                    <li><?php esc_html_e('Fill in the missing fields manually', 'erins-seed-catalog'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="esc-manual-toggle">
                        <a href="#" id="esc-toggle-manual-entry"><?php esc_html_e('Prefer to enter details manually?', 'erins-seed-catalog'); ?></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phase 2: Review & Edit AI Results -->
        <div class="esc-phase esc-phase-review" id="esc-phase-review-edit" style="display: none;">
            <div class="esc-ai-result-summary">
                <div class="esc-ai-result-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="esc-ai-result-text">
                    <h3><?php esc_html_e('AI Found Information for', 'erins-seed-catalog'); ?> <span id="esc-seed-display-name"></span></h3>
                    <p><?php esc_html_e('Review the details below and make any necessary adjustments.', 'erins-seed-catalog'); ?></p>
                </div>
            </div>

            <div class="esc-ai-changes-summary">
                <button type="button" class="esc-toggle-changes">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php esc_html_e('Show AI Changes', 'erins-seed-catalog'); ?>
                </button>

                <div class="esc-changes-detail" style="display: none;">
                    <h4><?php esc_html_e('Fields Populated by AI', 'erins-seed-catalog'); ?></h4>
                    <ul class="esc-changes-list">
                        <!-- Dynamically populated -->
                    </ul>
                </div>
            </div>

            <!-- Review mode toggle removed -->

            <div class="esc-review-modes">
                <!-- Detailed Edit Mode -->
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
                                    <div class="esc-floating-label">
                                        <div class="esc-input-with-confidence">
                                            <input type="text" id="esc_seed_name_review" name="seed_name" placeholder=" " required>
                                            <label for="esc_seed_name_review"><?php esc_html_e('Seed Type', 'erins-seed-catalog'); ?> <span class="required">*</span></label>
                                            <div class="esc-confidence-indicator" data-confidence="high">
                                                <span class="dashicons dashicons-shield"></span>
                                                <span class="esc-confidence-tooltip"><?php esc_html_e('High confidence: This information comes from verified sources.', 'erins-seed-catalog'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="esc-form-field">
                                    <div class="esc-floating-label">
                                        <div class="esc-input-with-confidence">
                                            <input type="text" id="esc_variety_name_review" name="variety_name" placeholder=" ">
                                            <label for="esc_variety_name_review"><?php esc_html_e('Variety', 'erins-seed-catalog'); ?></label>
                                            <div class="esc-confidence-indicator" data-confidence="high">
                                                <span class="dashicons dashicons-shield"></span>
                                                <span class="esc-confidence-tooltip"><?php esc_html_e('High confidence: This information comes from verified sources.', 'erins-seed-catalog'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <div class="esc-floating-label">
                                        <input type="text" id="esc_brand" name="brand" placeholder=" ">
                                        <label for="esc_brand"><?php esc_html_e('Seed Brand/Source', 'erins-seed-catalog'); ?></label>
                                    </div>
                                </div>

                                <div class="esc-form-field">
                                    <div class="esc-floating-label">
                                        <input type="text" id="esc_sku_upc" name="sku_upc" placeholder=" ">
                                        <label for="esc_sku_upc"><?php esc_html_e('Item / SKU / UPC', 'erins-seed-catalog'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field esc-full-width">
                                    <div class="esc-floating-label">
                                        <textarea id="esc_description" name="description" placeholder=" "></textarea>
                                        <label for="esc_description"><?php esc_html_e('Description', 'erins-seed-catalog'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field esc-full-width">
                                    <?php
                                    // Use the new image uploader component
                                    ESC_Image_Uploader::render('image_url', 'esc_image_url', '', __('Image', 'erins-seed-catalog'));
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Plant Characteristics Card -->
                    <div class="esc-form-card" data-ai-status="partially-populated">
                        <div class="esc-card-header">
                            <h3><?php esc_html_e('Plant Characteristics', 'erins-seed-catalog'); ?></h3>
                            <div class="esc-ai-status-badge">
                                <span class="dashicons dashicons-marker"></span>
                                <span class="esc-badge-text"><?php esc_html_e('Needs Review', 'erins-seed-catalog'); ?></span>
                            </div>
                        </div>

                        <div class="esc-card-content">
                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_plant_type"><?php esc_html_e('Plant Type', 'erins-seed-catalog'); ?></label>
                                    <div class="esc-input-with-confidence">
                                        <input type="text" id="esc_plant_type" name="plant_type" placeholder="e.g., Determinate Tomato, Annual Flower">
                                        <div class="esc-confidence-indicator" data-confidence="medium">
                                            <span class="dashicons dashicons-shield"></span>
                                            <span class="esc-confidence-tooltip"><?php esc_html_e('Medium confidence: This information is likely correct but may need verification.', 'erins-seed-catalog'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_growth_habit"><?php esc_html_e('Growth Habit', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_growth_habit" name="growth_habit" placeholder="e.g., Bush, Vining, Upright">
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_days_to_maturity"><?php esc_html_e('Days to Maturity', 'erins-seed-catalog'); ?></label>
                                    <div class="esc-range-slider">
                                        <input type="range" id="esc_days_to_maturity_slider" min="1" max="365" value="60" class="esc-slider">
                                        <div class="esc-slider-value">
                                            <input type="number" id="esc_days_to_maturity" name="days_to_maturity" min="1" max="365" value="60">
                                            <span class="esc-unit"><?php esc_html_e('days', 'erins-seed-catalog'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_plant_size"><?php esc_html_e('Plant Size (H x W)', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_plant_size" name="plant_size" placeholder="e.g., 4-6 ft x 2-3 ft">
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field esc-full-width">
                                    <label for="esc_special_characteristics"><?php esc_html_e('Special Characteristics', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_special_characteristics" name="special_characteristics" placeholder="e.g., Disease resistant (VFN), Heat tolerant, Heirloom"></textarea>
                                </div>
                            </div>

                            <div class="esc-ai-suggestions">
                                <div class="esc-suggestion-header">
                                    <span class="dashicons dashicons-lightbulb"></span>
                                    <span><?php esc_html_e('AI Suggestions for Growth Habit', 'erins-seed-catalog'); ?></span>
                                </div>
                                <div class="esc-suggestions-list">
                                    <button type="button" class="esc-suggestion" data-field="growth_habit" data-value="Bush"><?php esc_html_e('Bush', 'erins-seed-catalog'); ?></button>
                                    <button type="button" class="esc-suggestion" data-field="growth_habit" data-value="Vining"><?php esc_html_e('Vining', 'erins-seed-catalog'); ?></button>
                                    <button type="button" class="esc-suggestion" data-field="growth_habit" data-value="Upright"><?php esc_html_e('Upright', 'erins-seed-catalog'); ?></button>
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
                                        <option value="Start Indoors"><?php esc_html_e('Start Indoors', 'erins-seed-catalog'); ?></option>
                                        <option value="Both"><?php esc_html_e('Both', 'erins-seed-catalog'); ?></option>
                                    </select>
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_sowing_depth"><?php esc_html_e('Sowing Depth', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_sowing_depth" name="sowing_depth" placeholder="e.g., 1/4 inch">
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_sunlight"><?php esc_html_e('Sunlight Requirements', 'erins-seed-catalog'); ?></label>
                                    <div class="esc-toggle-group">
                                        <label class="esc-toggle">
                                            <input type="radio" name="sunlight" value="Full Sun">
                                            <span class="esc-toggle-label"><?php esc_html_e('Full Sun', 'erins-seed-catalog'); ?></span>
                                        </label>
                                        <label class="esc-toggle">
                                            <input type="radio" name="sunlight" value="Partial Sun">
                                            <span class="esc-toggle-label"><?php esc_html_e('Partial Sun', 'erins-seed-catalog'); ?></span>
                                        </label>
                                        <label class="esc-toggle">
                                            <input type="radio" name="sunlight" value="Shade">
                                            <span class="esc-toggle-label"><?php esc_html_e('Shade', 'erins-seed-catalog'); ?></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_watering"><?php esc_html_e('Watering Needs', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_watering" name="watering" placeholder="e.g., Keep consistently moist"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information Card -->
                    <div class="esc-form-card" data-ai-status="not-populated">
                        <div class="esc-card-header">
                            <h3><?php esc_html_e('Additional Information', 'erins-seed-catalog'); ?></h3>
                            <div class="esc-ai-status-badge">
                                <span class="dashicons dashicons-warning"></span>
                                <span class="esc-badge-text"><?php esc_html_e('Not Found', 'erins-seed-catalog'); ?></span>
                            </div>
                        </div>

                        <div class="esc-card-content">
                            <div class="esc-form-row">
                                <div class="esc-form-field">
                                    <label for="esc_usda_zones"><?php esc_html_e('USDA Hardiness Zones', 'erins-seed-catalog'); ?></label>
                                    <input type="text" id="esc_usda_zones" name="usda_zones" placeholder="e.g., 3-9">
                                </div>

                                <div class="esc-form-field">
                                    <label for="esc_pollinator_info"><?php esc_html_e('Pollinator Information', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_pollinator_info" name="pollinator_info" placeholder="e.g., Attracts bees and butterflies"></textarea>
                                </div>
                            </div>

                            <div class="esc-retry-ai">
                                <button type="button" class="esc-button esc-button-secondary" data-section="additional">
                                    <span class="dashicons dashicons-update"></span>
                                    <?php esc_html_e('Try AI Again for This Section', 'erins-seed-catalog'); ?>
                                </button>
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
                                    <label for="esc_seed_category"><?php esc_html_e('Seed Categories', 'erins-seed-catalog'); ?></label>
                                    <select id="esc_seed_category" name="esc_seed_category[]" multiple="multiple" class="esc-select-multiple">
                                        <?php echo ESC_Taxonomy::get_category_dropdown_options(); // Get hierarchical options ?>
                                    </select>
                                    <p class="description"><?php esc_html_e('Select one or more relevant categories.', 'erins-seed-catalog'); ?></p>
                                </div>
                            </div>

                            <div class="esc-form-row">
                                <div class="esc-form-field esc-full-width">
                                    <label for="esc_notes"><?php esc_html_e('Personal Notes', 'erins-seed-catalog'); ?></label>
                                    <textarea id="esc_notes" name="notes" placeholder=" "></textarea>
                                    <p class="description"><?php esc_html_e('Your own observations, planting dates, results, etc.', 'erins-seed-catalog'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="esc-form-actions">
                <button type="button" id="esc-back-to-ai" class="esc-button esc-button-secondary">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    <?php esc_html_e('Back to AI Search', 'erins-seed-catalog'); ?>
                </button>

                <button type="submit" class="esc-button esc-button-primary">
                    <span class="dashicons dashicons-saved"></span>
                    <?php esc_html_e('Save Seed', 'erins-seed-catalog'); ?>
                </button>
            </div>
        </div>

        <!-- Manual Entry Mode -->
        <div class="esc-phase esc-phase-manual" id="esc-phase-manual-entry" style="display: none;">
            <div class="esc-manual-header">
                <h3><?php esc_html_e('Manual Seed Entry', 'erins-seed-catalog'); ?></h3>
                <p><?php esc_html_e('Fill in the details manually or', 'erins-seed-catalog'); ?> <a href="#" id="esc-back-to-ai-search"><?php esc_html_e('switch back to AI search', 'erins-seed-catalog'); ?></a>.</p>
            </div>

            <!-- Include the original form fields here -->
            <div id="esc-manual-form-fields">
                <?php include ESC_PLUGIN_DIR . 'public/views/add-seed-form-fields.php'; ?>
            </div>
        </div>
    </form>
</div>

<script>
// Ensure floating labels work properly
jQuery(document).ready(function($) {
    // Process all form elements with floating labels
    $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').each(function() {
        var $field = $(this);

        // Check if the field has a value
        if ($field.val() && $field.val().trim() !== '') {
            $field.addClass('has-value');

            // Ensure the label is properly positioned
            var $label = $field.siblings('label');
            if ($label.length) {
                $label.addClass('active');
            }
        }

        // Ensure placeholder attribute exists (critical for CSS selectors)
        if (!$field.attr('placeholder')) {
            $field.attr('placeholder', ' ');
        }
    });

    // Handle input events for floating labels
    $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').on('input change focus blur', function() {
        var $field = $(this);
        if ($field.val() && $field.val().trim() !== '') {
            $field.addClass('has-value');
        } else {
            $field.removeClass('has-value');
        }
    });

    // Trigger the input event on page load to set initial state
    $('.esc-floating-label input, .esc-floating-label textarea, .esc-floating-label select').trigger('input');
});
</script>

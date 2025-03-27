<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!-- Basic Information Card -->
<div class="esc-form-card">
    <div class="esc-card-header">
        <h3><?php esc_html_e('Basic Information', 'erins-seed-catalog'); ?></h3>
    </div>
    
    <div class="esc-card-content">
        <div class="esc-form-row">
            <div class="esc-form-field">
                <label for="esc_seed_name_manual"><?php esc_html_e('Seed Type', 'erins-seed-catalog'); ?> <span class="required">*</span></label>
                <input type="text" id="esc_seed_name_manual" name="seed_name" required>
                <p class="description"><?php esc_html_e('The main name, e.g., "Tomato", "Bean", "Zinnia".', 'erins-seed-catalog'); ?></p>
            </div>
            
            <div class="esc-form-field">
                <label for="esc_variety_name_manual"><?php esc_html_e('Variety', 'erins-seed-catalog'); ?></label>
                <input type="text" id="esc_variety_name_manual" name="variety_name">
                <p class="description"><?php esc_html_e('Specific variety, e.g., "Brandywine", "Kentucky Wonder", "California Giant".', 'erins-seed-catalog'); ?></p>
            </div>
        </div>
        
        <div class="esc-form-row">
            <div class="esc-form-field">
                <label for="esc_brand_manual"><?php esc_html_e('Seed Brand/Source', 'erins-seed-catalog'); ?></label>
                <input type="text" id="esc_brand_manual" name="brand">
                <p class="description"><?php esc_html_e('Company you bought it from, e.g., "Baker Creek", "Johnny\'s Seeds", "Local Swap".', 'erins-seed-catalog'); ?></p>
            </div>
            
            <div class="esc-form-field">
                <label for="esc_sku_upc_manual"><?php esc_html_e('Item / SKU / UPC', 'erins-seed-catalog'); ?></label>
                <input type="text" id="esc_sku_upc_manual" name="sku_upc">
                <p class="description"><?php esc_html_e('Optional: Product code for precise identification.', 'erins-seed-catalog'); ?></p>
            </div>
        </div>
        
        <div class="esc-form-row">
            <div class="esc-form-field esc-full-width">
                <label for="esc_image_url_manual"><?php esc_html_e('Image URL', 'erins-seed-catalog'); ?></label>
                <div class="esc-image-upload-container">
                    <input type="url" id="esc_image_url_manual" name="image_url" placeholder="https://...">
                    <button type="button" class="esc-button esc-button-secondary esc-upload-button">
                        <span class="dashicons dashicons-upload"></span>
                    </button>
                </div>
                <p class="description"><?php esc_html_e('Link to an image of the plant, fruit, or seeds. Upload to Media Library first for reliability.', 'erins-seed-catalog'); ?></p>
            </div>
        </div>
        
        <div class="esc-form-row">
            <div class="esc-form-field esc-full-width">
                <label for="esc_description_manual"><?php esc_html_e('Description', 'erins-seed-catalog'); ?></label>
                <textarea id="esc_description_manual" name="description"></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Plant Characteristics Card -->
<div class="esc-form-card">
    <div class="esc-card-header">
        <h3><?php esc_html_e('Plant Characteristics', 'erins-seed-catalog'); ?></h3>
    </div>
    
    <div class="esc-card-content">
        <div class="esc-form-row">
            <div class="esc-form-field">
                <label for="esc_plant_type_manual"><?php esc_html_e('Plant Type', 'erins-seed-catalog'); ?></label>
                <input type="text" id="esc_plant_type_manual" name="plant_type" placeholder="e.g., Determinate Tomato, Annual Flower">
            </div>
            
            <div class="esc-form-field">
                <label for="esc_growth_habit_manual"><?php esc_html_e('Growth Habit', 'erins-seed-catalog'); ?></label>
                <input type="text" id="esc_growth_habit_manual" name="growth_habit" placeholder="e.g., Bush, Vining, Upright">
            </div>
        </div>
        
        <div class="esc-form-row">
            <div class="esc-form-field">
                <label for="esc_plant_size_manual"><?php esc_html_e('Plant Size (H x W)', 'erins-seed-catalog'); ?></label>
                <input type="text" id="esc_plant_size_manual" name="plant_size" placeholder="e.g., 4-6 ft x 2-3 ft">
            </div>
            
            <div class="esc-form-field">
                <label for="esc_fruit_info_manual"><?php esc_html_e('Fruit/Flower/Harvest Info', 'erins-seed-catalog'); ?></label>
                <input type="text" id="esc_fruit_info_manual" name="fruit_info" placeholder="e.g., 6 oz red fruit; 3-inch yellow flower">
            </div>
        </div>
        
        <div class="esc-form-row">
            <div class="esc-form-field">
                <label for="esc_days_to_maturity_manual"><?php esc_html_e('Days to Maturity/Harvest', 'erins-seed-catalog'); ?></label>
                <div class="esc-range-slider">
                    <input type="range" id="esc_days_to_maturity_slider_manual" min="1" max="365" value="60" class="esc-slider">
                    <div class="esc-slider-value">
                        <input type="number" id="esc_days_to_maturity_manual" name="days_to_maturity" min="1" max="365" value="60">
                        <span class="esc-unit"><?php esc_html_e('days', 'erins-seed-catalog'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="esc-form-field">
                <label for="esc_special_characteristics_manual"><?php esc_html_e('Special Characteristics', 'erins-seed-catalog'); ?></label>
                <textarea id="esc_special_characteristics_manual" name="special_characteristics" placeholder="e.g., Disease resistant (VFN), Heat tolerant, Heirloom"></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Growing Instructions Card -->
<div class="esc-form-card">
    <div class="esc-card-header">
        <h3><?php esc_html_e('Growing Instructions', 'erins-seed-catalog'); ?></h3>
    </div>
    
    <div class="esc-card-content">
        <div class="esc-form-row">
            <div class="esc-form-field">
                <label for="esc_sowing_method_manual"><?php esc_html_e('Sowing Method', 'erins-seed-catalog'); ?></label>
                <select id="esc_sowing_method_manual" name="sowing_method" class="esc-select">
                    <option value=""><?php esc_html_e('-- Select --', 'erins-seed-catalog'); ?></option>
                    <option value="Direct Sow"><?php esc_html_e('Direct Sow', 'erins-seed-catalog'); ?></option>
                    <option value="Start Indoors"><?php esc_html_e('Start Indoors', 'erins-seed-catalog'); ?></option>
                    <option value="Both"><?php esc_html_e('Both', 'erins-seed-catalog'); ?></option>
                </select>
            </div>
            
            <div class="esc-form-field">
                <label for="esc_sowing_depth_manual"><?php esc_html_e('Sowing Depth', 'erins-seed-catalog'); ?></label>
                <input type="text" id="esc_sowing_depth_manual" name="sowing_depth" placeholder="e.g., 1/4 inch">
            </div>
        </div>
        
        <div class="esc-form-row">
            <div class="esc-form-field">
                <label for="esc_sowing_spacing_manual"><?php esc_html_e('Sowing/Plant Spacing', 'erins-seed-catalog'); ?></label>
                <input type="text" id="esc_sowing_spacing_manual" name="sowing_spacing" placeholder="e.g., Thin to 6 inches; Plants 18 inches apart">
            </div>
            
            <div class="esc-form-field">
                <label for="esc_germination_temp_manual"><?php esc_html_e('Germination Temp', 'erins-seed-catalog'); ?></label>
                <input type="text" id="esc_germination_temp_manual" name="germination_temp" placeholder="e.g., 70-85 F">
            </div>
        </div>
        
        <div class="esc-form-row">
            <div class="esc-form-field">
                <label for="esc_sunlight_manual"><?php esc_html_e('Sunlight Requirements', 'erins-seed-catalog'); ?></label>
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
                <label for="esc_watering_manual"><?php esc_html_e('Watering Needs', 'erins-seed-catalog'); ?></label>
                <textarea id="esc_watering_manual" name="watering" placeholder="e.g., Keep consistently moist"></textarea>
            </div>
        </div>
    </div>
</div>

<!-- Additional Information Card -->
<div class="esc-form-card">
    <div class="esc-card-header">
        <h3><?php esc_html_e('Additional Information', 'erins-seed-catalog'); ?></h3>
        <button type="button" class="esc-card-toggle">
            <span class="dashicons dashicons-arrow-down-alt2"></span>
        </button>
    </div>
    
    <div class="esc-card-content">
        <div class="esc-form-row">
            <div class="esc-form-field">
                <label for="esc_usda_zones_manual"><?php esc_html_e('USDA Hardiness Zones', 'erins-seed-catalog'); ?></label>
                <input type="text" id="esc_usda_zones_manual" name="usda_zones" placeholder="e.g., 3-9">
            </div>
            
            <div class="esc-form-field">
                <label for="esc_pollinator_info_manual"><?php esc_html_e('Pollinator Information', 'erins-seed-catalog'); ?></label>
                <textarea id="esc_pollinator_info_manual" name="pollinator_info" placeholder="e.g., Attracts bees and butterflies"></textarea>
            </div>
        </div>
        
        <div class="esc-form-row">
            <div class="esc-form-field">
                <label for="esc_companion_plants_manual"><?php esc_html_e('Companion Plants', 'erins-seed-catalog'); ?></label>
                <textarea id="esc_companion_plants_manual" name="companion_plants"></textarea>
            </div>
            
            <div class="esc-form-field">
                <label for="esc_seed_saving_info_manual"><?php esc_html_e('Seed Saving Info', 'erins-seed-catalog'); ?></label>
                <textarea id="esc_seed_saving_info_manual" name="seed_saving_info" placeholder="e.g., Open-pollinated. Isolate by X feet. Dry seeds fully..."></textarea>
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
                <label for="esc_seed_category_manual"><?php esc_html_e('Seed Categories', 'erins-seed-catalog'); ?></label>
                <select id="esc_seed_category_manual" name="esc_seed_category[]" multiple="multiple" class="esc-select-multiple">
                    <?php echo ESC_Taxonomy::get_category_dropdown_options(); // Get hierarchical options ?>
                </select>
                <p class="description"><?php esc_html_e('Select one or more relevant categories.', 'erins-seed-catalog'); ?></p>
            </div>
        </div>
        
        <div class="esc-form-row">
            <div class="esc-form-field esc-full-width">
                <label for="esc_notes_manual"><?php esc_html_e('Personal Notes', 'erins-seed-catalog'); ?></label>
                <textarea id="esc_notes_manual" name="notes"></textarea>
                <p class="description"><?php esc_html_e('Your own observations, planting dates, results, etc.', 'erins-seed-catalog'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="esc-form-actions">
    <button type="submit" class="esc-button esc-button-primary">
        <span class="dashicons dashicons-saved"></span>
        <?php esc_html_e('Save Seed', 'erins-seed-catalog'); ?>
    </button>
</div>

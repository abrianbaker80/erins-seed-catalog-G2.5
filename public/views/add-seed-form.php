<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="esc-add-seed-form-container" class="esc-container">
	<h2><?php esc_html_e( 'Add New Seed to Catalog', 'erins-seed-catalog' ); ?></h2>

    <div id="esc-form-messages" class="esc-message" style="display: none;"></div>

	<form id="esc-add-seed-form" class="esc-form" method="post">

        <?php // Initial Search Fields ?>
        <fieldset class="esc-field-group">
            <legend><?php esc_html_e('Seed Information', 'erins-seed-catalog'); ?></legend>
            <div>
                <label for="esc_seed_name"><?php esc_html_e( 'Seed Type', 'erins-seed-catalog' ); ?> <span style="color:red;">*</span></label>
                <input type="text" id="esc_seed_name" name="seed_name" required>
                <p class="description"><?php esc_html_e('The main name, e.g., "Tomato", "Bean", "Zinnia".', 'erins-seed-catalog'); ?></p>
            </div>

            <div id="esc-variety-container">
                <label for="esc_variety_name"><?php esc_html_e('Variety', 'erins-seed-catalog'); ?></label>
                <div class="esc-variety-field-container">
                    <input type="text" id="esc_variety_name" name="variety_name" placeholder="<?php esc_attr_e('Enter variety name', 'erins-seed-catalog'); ?>">
                    <div id="esc-variety-dropdown" style="display: none;" class="esc-variety-dropdown"></div>
                </div>
                <div class="esc-variety-loading" style="display: none;">
                    <span class="dashicons dashicons-update-alt esc-spin"></span> <?php esc_html_e('Loading varieties...', 'erins-seed-catalog'); ?>
                </div>
                <p class="description"><?php esc_html_e('Specific variety, e.g., "Brandywine", "Kentucky Wonder", "California Giant".', 'erins-seed-catalog'); ?></p>
            </div>
            <div class="esc-search-button-container">
                <button type="button" id="esc-ai-fetch-trigger" class="esc-button esc-button-primary">
                    <span class="dashicons dashicons-search" style="vertical-align: middle; margin-top: -2px;"></span> <?php esc_html_e( 'Search', 'erins-seed-catalog' ); ?>
                </button>
            </div>
        </fieldset>

        <div id="esc-ai-status"></div>

        <?php // Hidden until AI search is performed ?>
        <div id="esc-extended-form" style="display: none;">
            <?php // Optional identification fields ?>
            <fieldset class="esc-field-group">
                <legend><?php esc_html_e('Additional Identification', 'erins-seed-catalog'); ?></legend>
                <div>
                    <label for="esc_brand"><?php esc_html_e( 'Seed Brand/Source', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_brand" name="brand">
                    <p class="description"><?php esc_html_e('Company you bought it from, e.g., "Baker Creek", "Johnny\'s Seeds", "Local Swap".', 'erins-seed-catalog'); ?></p>
                </div>
                <div>
                    <label for="esc_sku_upc"><?php esc_html_e( 'Item / SKU / UPC', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_sku_upc" name="sku_upc">
                    <p class="description"><?php esc_html_e('Optional: Product code for precise identification.', 'erins-seed-catalog'); ?></p>
                </div>
            </fieldset>

            <?php // Seed Details Group ?>
            <fieldset class="esc-field-group">
                <legend><?php esc_html_e('Seed Details', 'erins-seed-catalog'); ?></legend>

                <div>
                    <label for="esc_image_url"><?php esc_html_e( 'Image URL', 'erins-seed-catalog' ); ?></label>
                    <input type="url" id="esc_image_url" name="image_url" placeholder="https://...">
                    <p class="description"><?php esc_html_e('Link to an image of the plant, fruit, or seeds. Upload to Media Library first for reliability.', 'erins-seed-catalog'); ?></p>
                </div>

                <div>
                    <label for="esc_description"><?php esc_html_e( 'Description', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_description" name="description"></textarea>
                </div>
            </fieldset>

            <?php // Plant Characteristics Group ?>
            <fieldset class="esc-field-group">
                <legend><?php esc_html_e('Plant Characteristics', 'erins-seed-catalog'); ?></legend>
                <div>
                    <label for="esc_plant_type"><?php esc_html_e( 'Plant Type', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_plant_type" name="plant_type" placeholder="e.g., Determinate Tomato, Annual Flower">
                </div>
                <div>
                    <label for="esc_growth_habit"><?php esc_html_e( 'Growth Habit', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_growth_habit" name="growth_habit" placeholder="e.g., Bush, Vining, Upright">
                </div>
                <div>
                    <label for="esc_plant_size"><?php esc_html_e( 'Plant Size (H x W)', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_plant_size" name="plant_size" placeholder="e.g., 4-6 ft x 2-3 ft">
                </div>
                <div>
                    <label for="esc_fruit_info"><?php esc_html_e( 'Fruit/Flower/Harvest Info', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_fruit_info" name="fruit_info" placeholder="e.g., 6 oz red fruit; 3-inch yellow flower">
                </div>
                <div>
                    <label for="esc_flavor_profile"><?php esc_html_e( 'Flavor Profile (Edibles)', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_flavor_profile" name="flavor_profile"></textarea>
                </div>
                <div>
                    <label for="esc_scent"><?php esc_html_e( 'Scent (Flowers/Herbs)', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_scent" name="scent">
                </div>
                <div>
                    <label for="esc_bloom_time"><?php esc_html_e( 'Bloom Time (Flowers)', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_bloom_time" name="bloom_time" placeholder="e.g., Early Summer to Fall">
                </div>
                <div>
                    <label for="esc_days_to_maturity"><?php esc_html_e( 'Days to Maturity/Harvest', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_days_to_maturity" name="days_to_maturity" placeholder="e.g., 65-75">
                </div>
                <div>
                    <label for="esc_special_characteristics"><?php esc_html_e( 'Special Characteristics', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_special_characteristics" name="special_characteristics" placeholder="e.g., Disease resistant (VFN), Heat tolerant, Heirloom"></textarea>
                </div>
                <div>
                    <label for="esc_edible_parts"><?php esc_html_e( 'Edible Parts', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_edible_parts" name="edible_parts" placeholder="e.g., Fruit, Leaves, Root">
                </div>
            </fieldset>

            <?php // Growing Instructions Group ?>
            <fieldset class="esc-field-group">
                <legend><?php esc_html_e('Growing Instructions', 'erins-seed-catalog'); ?></legend>
                <div>
                    <label for="esc_sowing_method"><?php esc_html_e( 'Sowing Method', 'erins-seed-catalog' ); ?></label>
                    <select id="esc_sowing_method" name="sowing_method">
                        <option value=""><?php esc_html_e( '-- Select --', 'erins-seed-catalog' ); ?></option>
                        <option value="Direct Sow"><?php esc_html_e( 'Direct Sow', 'erins-seed-catalog' ); ?></option>
                        <option value="Start Indoors"><?php esc_html_e( 'Start Indoors', 'erins-seed-catalog' ); ?></option>
                        <option value="Both"><?php esc_html_e( 'Both', 'erins-seed-catalog' ); ?></option>
                    </select>
                </div>
                <div>
                    <label for="esc_sowing_depth"><?php esc_html_e( 'Sowing Depth', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_sowing_depth" name="sowing_depth" placeholder="e.g., 1/4 inch">
                </div>
                <div>
                    <label for="esc_sowing_spacing"><?php esc_html_e( 'Sowing/Plant Spacing', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_sowing_spacing" name="sowing_spacing" placeholder="e.g., Thin to 6 inches; Plants 18 inches apart">
                </div>
                <div>
                    <label for="esc_germination_temp"><?php esc_html_e( 'Germination Temp', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_germination_temp" name="germination_temp" placeholder="e.g., 70-85 F">
                </div>
                <div>
                    <label for="esc_sunlight"><?php esc_html_e( 'Sunlight Requirements', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_sunlight" name="sunlight" placeholder="e.g., Full Sun (6+ hrs)">
                </div>
                <div>
                    <label for="esc_watering"><?php esc_html_e( 'Watering Needs', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_watering" name="watering" placeholder="e.g., Keep consistently moist"></textarea>
                </div>
                <div>
                    <label for="esc_fertilizer"><?php esc_html_e( 'Fertilizer Recommendations', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_fertilizer" name="fertilizer"></textarea>
                </div>
                <div>
                    <label for="esc_pest_disease_info"><?php esc_html_e( 'Pest & Disease Info', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_pest_disease_info" name="pest_disease_info"></textarea>
                </div>
                <div>
                    <label for="esc_harvesting_tips"><?php esc_html_e( 'Harvesting Tips', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_harvesting_tips" name="harvesting_tips"></textarea>
                </div>
            </fieldset>

            <?php // Seed Packet Info Group ?>
            <fieldset class="esc-field-group">
                <legend><?php esc_html_e('Seed Packet Information', 'erins-seed-catalog'); ?></legend>
                <div>
                    <label for="esc_seed_quantity"><?php esc_html_e( 'Seed Count/Weight', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_seed_quantity" name="seed_quantity" placeholder="e.g., Approx 25 seeds; 500mg">
                </div>
                <div>
                    <label for="esc_seed_treatment"><?php esc_html_e( 'Seed Type/Treatment', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_seed_treatment" name="seed_treatment" placeholder="e.g., Organic, Heirloom, Hybrid, Pelleted, Treated">
                    <p class="description"><?php esc_html_e('Comma-separated if multiple apply.', 'erins-seed-catalog'); ?></p>
                </div>
                <div>
                    <label for="esc_germination_rate"><?php esc_html_e( 'Germination Rate (%)', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_germination_rate" name="germination_rate" placeholder="e.g., 85%">
                </div>
                <div>
                    <label for="esc_purchase_date"><?php esc_html_e( 'Purchase Date', 'erins-seed-catalog' ); ?></label>
                    <input type="date" id="esc_purchase_date" name="purchase_date" class="regular-text">
                </div>
                <div>
                    <label for="esc_expiration_date"><?php esc_html_e( 'Expiration Date / Packed For', 'erins-seed-catalog' ); ?></label>
                    <input type="date" id="esc_expiration_date" name="expiration_date" class="regular-text">
                </div>
                <div>
                    <label for="esc_quantity_on_hand"><?php esc_html_e( 'Quantity On Hand (Packets)', 'erins-seed-catalog' ); ?></label>
                    <input type="number" id="esc_quantity_on_hand" name="quantity_on_hand" min="0" step="1" class="small-text">
                </div>
                <div>
                    <label for="esc_price"><?php esc_html_e( 'Price Paid', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_price" name="price" placeholder="e.g., $3.50">
                </div>
                <div>
                    <label for="esc_availability"><?php esc_html_e( 'Availability', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_availability" name="availability" placeholder="e.g., In stock, Discontinued">
                </div>
                <div>
                    <label for="esc_company_info"><?php esc_html_e( 'Company Info / Website', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_company_info" name="company_info"></textarea>
                </div>
            </fieldset>

            <?php // Additional Info Group ?>
            <fieldset class="esc-field-group">
                <legend><?php esc_html_e('Additional Information', 'erins-seed-catalog'); ?></legend>
                <div>
                    <label for="esc_usda_zones"><?php esc_html_e( 'USDA Hardiness Zones', 'erins-seed-catalog' ); ?></label>
                    <input type="text" id="esc_usda_zones" name="usda_zones" placeholder="e.g., 3-9">
                </div>
                <div>
                    <label for="esc_pollinator_info"><?php esc_html_e( 'Pollinator Information', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_pollinator_info" name="pollinator_info" placeholder="e.g., Attracts bees and butterflies"></textarea>
                </div>
                <div class="esc-checkbox-group">
                    <label for="esc_container_suitability">
                        <input type="checkbox" id="esc_container_suitability" name="container_suitability" value="1">
                        <?php esc_html_e( 'Suitable for Containers?', 'erins-seed-catalog' ); ?>
                    </label>
                    <label for="esc_cut_flower_potential">
                        <input type="checkbox" id="esc_cut_flower_potential" name="cut_flower_potential" value="1">
                        <?php esc_html_e( 'Good Cut Flower?', 'erins-seed-catalog' ); ?>
                    </label>
                </div>
                <div>
                    <label for="esc_storage_recommendations"><?php esc_html_e( 'Harvest Storage', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_storage_recommendations" name="storage_recommendations"></textarea>
                </div>
                <div>
                    <label for="esc_historical_background"><?php esc_html_e( 'Historical Background', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_historical_background" name="historical_background"></textarea>
                </div>
                <div>
                    <label for="esc_recipes"><?php esc_html_e( 'Recipes / Uses', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_recipes" name="recipes"></textarea>
                </div>
                <div>
                    <label for="esc_companion_plants"><?php esc_html_e( 'Companion Plants', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_companion_plants" name="companion_plants"></textarea>
                </div>
                <div>
                    <label for="esc_customer_reviews"><?php esc_html_e( 'Customer Reviews Summary/Link', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_customer_reviews" name="customer_reviews"></textarea>
                </div>
                <div>
                    <label for="esc_regional_tips"><?php esc_html_e( 'Regional Growing Tips', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_regional_tips" name="regional_tips"></textarea>
                </div>
                <div>
                    <label for="esc_producer_info"><?php esc_html_e( 'Seed Producer Info', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_producer_info" name="producer_info" placeholder="e.g., Grown on our farm; Sourced from certified organic grower"></textarea>
                </div>
                <div>
                    <label for="esc_seed_saving_info"><?php esc_html_e( 'Seed Saving Info', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_seed_saving_info" name="seed_saving_info" placeholder="e.g., Open-pollinated. Isolate by X feet. Dry seeds fully..."></textarea>
                </div>
            </fieldset>

            <?php // Category & Notes Group ?>
            <fieldset class="esc-field-group">
                <legend><?php esc_html_e('Organization & Notes', 'erins-seed-catalog'); ?></legend>
                <div>
                    <label for="esc_seed_category"><?php esc_html_e( 'Seed Categories', 'erins-seed-catalog' ); ?></label>
                    <select id="esc_seed_category" name="esc_seed_category[]" multiple="multiple" size="8" style="min-height: 150px;">
                        <?php echo ESC_Taxonomy::get_category_dropdown_options(); // Get hierarchical options ?>
                    </select>
                    <p class="description"><?php esc_html_e( 'Select one or more relevant categories (hold Ctrl/Cmd to select multiple).', 'erins-seed-catalog' ); ?> <a href="<?php echo esc_url( get_admin_url(null, 'edit-tags.php?taxonomy=' . ESC_Taxonomy::TAXONOMY_NAME) ); ?>" target="_blank"><?php esc_html_e('Manage Categories', 'erins-seed-catalog'); ?></a></p>
                </div>

                <div>
                    <label for="esc_notes"><?php esc_html_e( 'Personal Notes', 'erins-seed-catalog' ); ?></label>
                    <textarea id="esc_notes" name="notes"></textarea>
                    <p class="description"><?php esc_html_e('Your own observations, planting dates, results, etc.', 'erins-seed-catalog'); ?></p>
                </div>
            </fieldset>

            <button type="submit" class="esc-button esc-button-primary"><?php esc_html_e( 'Submit Seed', 'erins-seed-catalog' ); ?></button>
        </div>
	</form>
</div>
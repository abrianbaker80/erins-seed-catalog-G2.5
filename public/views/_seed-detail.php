<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// This template expects a $seed object with all seed details
if ( ! isset( $seed ) || ! is_object( $seed ) ) {
    return;
}
?>
<div class="esc-seed-detail">
    <div class="esc-seed-detail-header">
        <?php if ( ! empty( $seed->image_url ) ) : ?>
            <div class="esc-seed-detail-image">
                <img src="<?php echo esc_url( $seed->image_url ); ?>" 
                     alt="<?php echo esc_attr( $seed->seed_name ); ?><?php echo $seed->variety_name ? ' - ' . esc_attr( $seed->variety_name ) : ''; ?>" 
                     loading="lazy">
            </div>
        <?php endif; ?>
        <div class="esc-seed-detail-title">
            <h2><?php echo esc_html( $seed->seed_name ); ?>
                <?php if ( ! empty( $seed->variety_name ) ) : ?>
                    <span class="esc-variety-name">- <?php echo esc_html( $seed->variety_name ); ?></span>
                <?php endif; ?>
            </h2>
            <?php if ( ! empty( $seed->brand ) ) : ?>
                <div class="esc-brand"><?php echo esc_html( $seed->brand ); ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ( ! empty( $seed->description ) ) : ?>
        <div class="esc-seed-detail-section esc-description">
            <h3><?php esc_html_e( 'Description', 'erins-seed-catalog' ); ?></h3>
            <p><?php echo esc_html( $seed->description ); ?></p>
        </div>
    <?php endif; ?>

    <div class="esc-seed-detail-grid">
        <div class="esc-seed-detail-section">
            <h3><?php esc_html_e( 'Plant Characteristics', 'erins-seed-catalog' ); ?></h3>
            <?php ESC_Functions::display_seed_field( $seed, 'plant_type', __( 'Plant Type', 'erins-seed-catalog' ) ); ?>
            <?php ESC_Functions::display_seed_field( $seed, 'growth_habit', __( 'Growth Habit', 'erins-seed-catalog' ) ); ?>
            <?php ESC_Functions::display_seed_field( $seed, 'plant_size', __( 'Plant Size', 'erins-seed-catalog' ) ); ?>
            <?php ESC_Functions::display_seed_field( $seed, 'fruit_info', __( 'Fruit/Flower Info', 'erins-seed-catalog' ) ); ?>
            <?php ESC_Functions::display_seed_field( $seed, 'days_to_maturity', __( 'Days to Maturity', 'erins-seed-catalog' ) ); ?>
            <?php ESC_Functions::display_seed_field( $seed, 'special_characteristics', __( 'Special Characteristics', 'erins-seed-catalog' ) ); ?>
        </div>

        <div class="esc-seed-detail-section">
            <h3><?php esc_html_e( 'Growing Requirements', 'erins-seed-catalog' ); ?></h3>
            <?php ESC_Functions::display_seed_field( $seed, 'sowing_method', __( 'Sowing Method', 'erins-seed-catalog' ) ); ?>
            <?php ESC_Functions::display_seed_field( $seed, 'sowing_depth', __( 'Sowing Depth', 'erins-seed-catalog' ) ); ?>
            <?php ESC_Functions::display_seed_field( $seed, 'sowing_spacing', __( 'Plant Spacing', 'erins-seed-catalog' ) ); ?>
            <?php ESC_Functions::display_seed_field( $seed, 'germination_temp', __( 'Germination Temperature', 'erins-seed-catalog' ) ); ?>
            <?php ESC_Functions::display_seed_field( $seed, 'sunlight', __( 'Sunlight Needs', 'erins-seed-catalog' ) ); ?>
            <?php ESC_Functions::display_seed_field( $seed, 'watering', __( 'Watering Needs', 'erins-seed-catalog' ) ); ?>
        </div>

        <?php if ( ! empty( $seed->fertilizer ) || ! empty( $seed->pest_disease_info ) ) : ?>
            <div class="esc-seed-detail-section">
                <h3><?php esc_html_e( 'Care & Maintenance', 'erins-seed-catalog' ); ?></h3>
                <?php ESC_Functions::display_seed_field( $seed, 'fertilizer', __( 'Fertilizer', 'erins-seed-catalog' ) ); ?>
                <?php ESC_Functions::display_seed_field( $seed, 'pest_disease_info', __( 'Pest & Disease Info', 'erins-seed-catalog' ) ); ?>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $seed->harvesting_tips ) || ! empty( $seed->storage_recommendations ) ) : ?>
            <div class="esc-seed-detail-section">
                <h3><?php esc_html_e( 'Harvesting & Storage', 'erins-seed-catalog' ); ?></h3>
                <?php ESC_Functions::display_seed_field( $seed, 'harvesting_tips', __( 'Harvesting Tips', 'erins-seed-catalog' ) ); ?>
                <?php ESC_Functions::display_seed_field( $seed, 'storage_recommendations', __( 'Storage', 'erins-seed-catalog' ) ); ?>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $seed->companion_plants ) || ! empty( $seed->historical_background ) ) : ?>
            <div class="esc-seed-detail-section">
                <h3><?php esc_html_e( 'Additional Information', 'erins-seed-catalog' ); ?></h3>
                <?php ESC_Functions::display_seed_field( $seed, 'companion_plants', __( 'Companion Plants', 'erins-seed-catalog' ) ); ?>
                <?php ESC_Functions::display_seed_field( $seed, 'historical_background', __( 'History', 'erins-seed-catalog' ) ); ?>
                <?php ESC_Functions::display_seed_field( $seed, 'recipes', __( 'Uses & Recipes', 'erins-seed-catalog' ) ); ?>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $seed->seed_saving_info ) || ! empty( $seed->germination_rate ) ) : ?>
            <div class="esc-seed-detail-section">
                <h3><?php esc_html_e( 'Seed Information', 'erins-seed-catalog' ); ?></h3>
                <?php ESC_Functions::display_seed_field( $seed, 'seed_treatment', __( 'Seed Type/Treatment', 'erins-seed-catalog' ) ); ?>
                <?php ESC_Functions::display_seed_field( $seed, 'germination_rate', __( 'Germination Rate', 'erins-seed-catalog' ) ); ?>
                <?php ESC_Functions::display_seed_field( $seed, 'seed_saving_info', __( 'Seed Saving', 'erins-seed-catalog' ) ); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
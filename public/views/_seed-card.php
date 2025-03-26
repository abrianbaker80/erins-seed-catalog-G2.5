<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// This is a template part. Expects a $seed object to be available in the current scope.
if ( ! isset( $seed ) || ! is_object( $seed ) ) {
    return;
}
?>
<div class="esc-seed-card">
    <?php if ( ! empty( $seed->image_url ) && filter_var( $seed->image_url, FILTER_VALIDATE_URL ) ) : ?>
        <img src="<?php echo esc_url( $seed->image_url ); ?>" alt="<?php echo esc_attr( $seed->seed_name ); ?><?php echo $seed->variety_name ? ' - ' . esc_attr( $seed->variety_name ) : ''; ?>" class="esc-seed-image" loading="lazy">
    <?php endif; ?>

    <h3>
        <?php echo esc_html( $seed->seed_name ); ?>
        <?php if ( ! empty( $seed->variety_name ) ) : ?>
            <span class="esc-variety-name">- <?php echo esc_html( $seed->variety_name ); ?></span>
        <?php endif; ?>
    </h3>

    <?php if ( ! empty( $seed->brand ) ) : ?>
         <div class="esc-field esc-field-brand">
             <strong class="esc-field-label"><?php esc_html_e('Brand:', 'erins-seed-catalog'); ?></strong>
             <span class="esc-field-value"><?php echo esc_html($seed->brand); ?></span>
         </div>
    <?php endif; ?>

    <?php // Display key information - customize which fields appear on the card ?>
    <?php ESC_Functions::display_seed_field( $seed, 'plant_type', __( 'Type', 'erins-seed-catalog' ) ); ?>
    <?php ESC_Functions::display_seed_field( $seed, 'days_to_maturity', __( 'Matures In', 'erins-seed-catalog' ) ); ?>
    <?php ESC_Functions::display_seed_field( $seed, 'sunlight', __( 'Sunlight', 'erins-seed-catalog' ) ); ?>
    <?php ESC_Functions::display_seed_field( $seed, 'sowing_method', __( 'Sowing', 'erins-seed-catalog' ) ); ?>
    <?php // ESC_Functions::display_seed_field( $seed, 'description', __( 'Description', 'erins-seed-catalog' ), 'text' ); // Maybe too long for card ?>


    <?php // Display Categories ?>
     <div class="esc-categories">
        <?php ESC_Functions::display_seed_field( $seed, 'categories', __( 'Categories', 'erins-seed-catalog' ), 'category' ); ?>
    </div>


    <?php // --- Add More Fields as needed for the card view --- ?>
    <?php /* Example:
    ESC_Functions::display_seed_field( $seed, 'usda_zones', __( 'Zones', 'erins-seed-catalog' ) );
    ESC_Functions::display_seed_field( $seed, 'seed_treatment', __( 'Type', 'erins-seed-catalog' ) );
    */ ?>

    <?php // Optional: Add a "View Details" link/button if you create a single seed view page ?>
    <?php /*
    <a href="<?php echo esc_url( get_permalink() ); // Needs a way to link to a specific seed display ?>?seed_id=<?php echo $seed->id; ?>" class="esc-button esc-button-small">
        <?php esc_html_e('View Details', 'erins-seed-catalog'); ?>
    </a>
    */ ?>

</div>
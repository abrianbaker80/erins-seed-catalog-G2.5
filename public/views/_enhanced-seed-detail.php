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
        <div class="esc-seed-detail-banner">
            <?php
            // Ensure image URL is properly formatted - don't validate with filter_var
            $has_image = !empty($seed->image_url);
            $image_url = $has_image ? esc_url($seed->image_url) : '';
            ?>
            <?php if ($has_image) : ?>
                <img src="<?php echo $image_url; ?>"
                     alt="<?php echo esc_attr($seed->seed_name); ?><?php echo $seed->variety_name ? ' - ' . esc_attr($seed->variety_name) : ''; ?>"
                     loading="lazy">
            <?php else : ?>
                <div class="esc-no-image">
                    <span class="dashicons dashicons-format-image"></span>
                </div>
            <?php endif; ?>

            <div class="esc-seed-detail-overlay">
                <h2>
                    <?php echo esc_html( $seed->seed_name ); ?>
                    <?php if ( ! empty( $seed->variety_name ) ) : ?>
                        <span class="esc-variety-name">(<?php echo esc_html( $seed->variety_name ); ?>)</span>
                    <?php endif; ?>
                </h2>
                <?php if ( ! empty( $seed->brand ) ) : ?>
                    <div class="esc-seed-detail-brand"><?php echo esc_html( $seed->brand ); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="esc-seed-detail-content">
        <?php if ( ! empty( $seed->description ) ) : ?>
            <div class="esc-seed-detail-description">
                <?php echo wp_kses_post( $seed->description ); ?>
            </div>
        <?php endif; ?>

        <div class="esc-seed-detail-grid">
            <div class="esc-seed-detail-section">
                <h3><?php esc_html_e( 'Plant Characteristics', 'erins-seed-catalog' ); ?></h3>

                <?php if ( ! empty( $seed->plant_type ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Plant Type', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->plant_type ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $seed->growth_habit ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Growth Habit', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->growth_habit ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $seed->plant_size ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Plant Size', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->plant_size ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $seed->fruit_info ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Fruit/Flower Info', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->fruit_info ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $seed->days_to_maturity ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Days to Maturity', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->days_to_maturity ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $seed->bloom_time ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Bloom Time', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->bloom_time ); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="esc-seed-detail-section">
                <h3><?php esc_html_e( 'Growing Requirements', 'erins-seed-catalog' ); ?></h3>

                <?php if ( ! empty( $seed->sowing_depth ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Sowing Depth', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->sowing_depth ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $seed->sowing_spacing ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Plant Spacing', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->sowing_spacing ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $seed->germination_temp ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Germination Temperature', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->germination_temp ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $seed->sunlight ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Sunlight', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->sunlight ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $seed->watering ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Watering', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->watering ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $seed->fertilizer ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'Fertilizer', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->fertilizer ); ?></div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $seed->usda_zones ) ) : ?>
                    <div class="esc-seed-detail-field">
                        <div class="esc-seed-detail-label"><?php esc_html_e( 'USDA Zones', 'erins-seed-catalog' ); ?></div>
                        <div class="esc-seed-detail-value"><?php echo esc_html( $seed->usda_zones ); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ( ! empty( $seed->pest_disease_info ) || ! empty( $seed->companion_plants ) ) : ?>
                <div class="esc-seed-detail-section">
                    <h3><?php esc_html_e( 'Garden Management', 'erins-seed-catalog' ); ?></h3>

                    <?php if ( ! empty( $seed->pest_disease_info ) ) : ?>
                        <div class="esc-seed-detail-field">
                            <div class="esc-seed-detail-label"><?php esc_html_e( 'Pest & Disease Info', 'erins-seed-catalog' ); ?></div>
                            <div class="esc-seed-detail-value"><?php echo wp_kses_post( $seed->pest_disease_info ); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $seed->companion_plants ) ) : ?>
                        <div class="esc-seed-detail-field">
                            <div class="esc-seed-detail-label"><?php esc_html_e( 'Companion Plants', 'erins-seed-catalog' ); ?></div>
                            <div class="esc-seed-detail-value"><?php echo wp_kses_post( $seed->companion_plants ); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $seed->pollinator_info ) ) : ?>
                        <div class="esc-seed-detail-field">
                            <div class="esc-seed-detail-label"><?php esc_html_e( 'Pollinator Info', 'erins-seed-catalog' ); ?></div>
                            <div class="esc-seed-detail-value"><?php echo wp_kses_post( $seed->pollinator_info ); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $seed->harvesting_tips ) || ! empty( $seed->flavor_profile ) || ! empty( $seed->scent ) ) : ?>
                <div class="esc-seed-detail-section">
                    <h3><?php esc_html_e( 'Harvest & Use', 'erins-seed-catalog' ); ?></h3>

                    <?php if ( ! empty( $seed->harvesting_tips ) ) : ?>
                        <div class="esc-seed-detail-field">
                            <div class="esc-seed-detail-label"><?php esc_html_e( 'Harvesting Tips', 'erins-seed-catalog' ); ?></div>
                            <div class="esc-seed-detail-value"><?php echo wp_kses_post( $seed->harvesting_tips ); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $seed->flavor_profile ) ) : ?>
                        <div class="esc-seed-detail-field">
                            <div class="esc-seed-detail-label"><?php esc_html_e( 'Flavor Profile', 'erins-seed-catalog' ); ?></div>
                            <div class="esc-seed-detail-value"><?php echo wp_kses_post( $seed->flavor_profile ); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $seed->scent ) ) : ?>
                        <div class="esc-seed-detail-field">
                            <div class="esc-seed-detail-label"><?php esc_html_e( 'Scent', 'erins-seed-catalog' ); ?></div>
                            <div class="esc-seed-detail-value"><?php echo esc_html( $seed->scent ); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $seed->edible_parts ) ) : ?>
                        <div class="esc-seed-detail-field">
                            <div class="esc-seed-detail-label"><?php esc_html_e( 'Edible Parts', 'erins-seed-catalog' ); ?></div>
                            <div class="esc-seed-detail-value"><?php echo esc_html( $seed->edible_parts ); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $seed->cut_flower_potential ) && $seed->cut_flower_potential ) : ?>
                        <div class="esc-seed-detail-field">
                            <div class="esc-seed-detail-label"><?php esc_html_e( 'Cut Flower', 'erins-seed-catalog' ); ?></div>
                            <div class="esc-seed-detail-value"><?php esc_html_e( 'Yes', 'erins-seed-catalog' ); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $seed->seed_saving_info ) || ! empty( $seed->storage_recommendations ) ) : ?>
                <div class="esc-seed-detail-section">
                    <h3><?php esc_html_e( 'Seed Information', 'erins-seed-catalog' ); ?></h3>

                    <?php if ( ! empty( $seed->seed_saving_info ) ) : ?>
                        <div class="esc-seed-detail-field">
                            <div class="esc-seed-detail-label"><?php esc_html_e( 'Seed Saving', 'erins-seed-catalog' ); ?></div>
                            <div class="esc-seed-detail-value"><?php echo wp_kses_post( $seed->seed_saving_info ); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $seed->storage_recommendations ) ) : ?>
                        <div class="esc-seed-detail-field">
                            <div class="esc-seed-detail-label"><?php esc_html_e( 'Storage', 'erins-seed-catalog' ); ?></div>
                            <div class="esc-seed-detail-value"><?php echo wp_kses_post( $seed->storage_recommendations ); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( ! empty( $seed->categories ) ) : ?>
            <div class="esc-seed-detail-categories">
                <?php foreach ( $seed->categories as $term ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'category', $term->term_id ) ); ?>" class="esc-seed-detail-category">
                        <?php echo esc_html( $term->name ); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

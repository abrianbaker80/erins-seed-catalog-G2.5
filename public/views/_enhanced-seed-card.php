<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// This is a template part. Expects a $seed object to be available in the current scope.
if ( ! isset( $seed ) || ! is_object( $seed ) ) {
    return;
}

// Determine if seed is new (added in the last 7 days)
$is_new = false;
if (!empty($seed->date_added)) {
    $added_date = strtotime($seed->date_added);
    $is_new = (time() - $added_date) < (7 * 24 * 60 * 60); // 7 days in seconds
}
?>
<div class="esc-seed-card esc-enhanced-card" data-seed-id="<?php echo esc_attr( $seed->id ); ?>">
    <?php if ($is_new) : ?>
        <div class="esc-card-badge new"><?php esc_html_e('New', 'erins-seed-catalog'); ?></div>
    <?php endif; ?>

    <div class="esc-card-actions">
        <div class="esc-card-action-btn esc-view-details" title="<?php esc_attr_e('View Details', 'erins-seed-catalog'); ?>">
            <span class="dashicons dashicons-visibility"></span>
        </div>
    </div>

    <div class="esc-seed-image-container">
        <?php if ( ! empty( $seed->image_url ) && filter_var( $seed->image_url, FILTER_VALIDATE_URL ) ) : ?>
            <img src="<?php echo esc_url( $seed->image_url ); ?>" alt="<?php echo esc_attr( $seed->seed_name ); ?><?php echo $seed->variety_name ? ' - ' . esc_attr( $seed->variety_name ) : ''; ?>" class="esc-seed-image" loading="lazy">
        <?php else : ?>
            <div class="esc-no-image">
                <span class="dashicons dashicons-format-image"></span>
            </div>
        <?php endif; ?>
    </div>

    <div class="esc-card-content">
        <h3>
            <?php echo esc_html( $seed->seed_name ); ?>
            <?php if ( ! empty( $seed->variety_name ) ) : ?>
                <span class="esc-variety-name"><?php echo esc_html( $seed->variety_name ); ?></span>
            <?php endif; ?>
        </h3>

        <?php if (!empty($seed->description)) : ?>
            <div class="esc-card-description">
                <?php
                $excerpt = wp_trim_words(strip_tags($seed->description), 15, '...');
                echo esc_html($excerpt);
                ?>
            </div>
        <?php endif; ?>

        <?php // Display key information - customize which fields appear on the card ?>
        <?php if (!empty($seed->plant_type)) : ?>
            <div class="esc-field">
                <div class="esc-field-label"><?php esc_html_e('Type', 'erins-seed-catalog'); ?></div>
                <div class="esc-field-value"><?php echo esc_html($seed->plant_type); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($seed->days_to_maturity)) : ?>
            <div class="esc-field">
                <div class="esc-field-label"><?php esc_html_e('Matures', 'erins-seed-catalog'); ?></div>
                <div class="esc-field-value"><?php echo esc_html($seed->days_to_maturity); ?> <?php esc_html_e('days', 'erins-seed-catalog'); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($seed->sunlight)) : ?>
            <div class="esc-field">
                <div class="esc-field-label"><?php esc_html_e('Sunlight', 'erins-seed-catalog'); ?></div>
                <div class="esc-field-value"><?php echo esc_html($seed->sunlight); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($seed->growth_habit)) : ?>
            <div class="esc-field">
                <div class="esc-field-label"><?php esc_html_e('Growth', 'erins-seed-catalog'); ?></div>
                <div class="esc-field-value"><?php echo esc_html($seed->growth_habit); ?></div>
            </div>
        <?php endif; ?>

        <?php // Display categories as tags ?>
        <?php if (!empty($seed->categories)) : ?>
            <div class="esc-categories">
                <?php foreach ($seed->categories as $term) : ?>
                    <a href="<?php echo esc_url(add_query_arg('category', $term->term_id)); ?>" class="esc-category-tag">
                        <?php echo esc_html($term->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

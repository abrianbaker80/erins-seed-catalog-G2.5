# Erin's Seed Catalog Enhancement Guide

This guide provides instructions for implementing the enhanced design and functionality for the Erin's Seed Catalog plugin.

## Overview of Improvements

The enhancements focus on three key areas:

1. **Visual Design**: Modern, clean interface with better spacing, colors, and visual hierarchy
2. **User Experience**: Collapsible sections, improved navigation, and better feedback
3. **Catalog Display**: Grid-based layout with attractive seed cards

## Implementation Steps

### 1. Add Enhanced Styles

Add the custom CSS file to your theme:

1. Upload `esc-enhanced-styles.css` to your theme directory
2. Enqueue the stylesheet by adding this code to your theme's `functions.php`:

```php
function enqueue_esc_enhanced_styles() {
    wp_enqueue_style(
        'esc-enhanced-styles',
        get_stylesheet_directory_uri() . '/esc-enhanced-styles.css',
        array('esc-modern-form', 'esc-public-styles'),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_esc_enhanced_styles', 20);
```

### 2. Add Enhanced Scripts

Add the custom JavaScript file to your theme:

1. Upload `esc-enhanced-scripts.js` to your theme directory
2. Enqueue the script by adding this code to your theme's `functions.php`:

```php
function enqueue_esc_enhanced_scripts() {
    wp_enqueue_script(
        'esc-enhanced-scripts',
        get_stylesheet_directory_uri() . '/esc-enhanced-scripts.js',
        array('jquery', 'esc-public-scripts'),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_esc_enhanced_scripts', 20);
```

### 3. Recommended Template Modifications

For optimal results, consider making these changes to the plugin templates:

#### Modify `add-seed-form-modern.php`:

1. Add a class to each card header to make it collapsible:
```php
<div class="esc-card-header esc-collapsible">
```

2. Add a wrapper div around the seed cards in the catalog:
```php
<div class="esc-seed-grid">
    <!-- Existing seed cards here -->
</div>
```

#### Modify `_seed-card.php`:

1. Structure the card content for better styling:
```php
<div class="esc-seed-card" data-seed-id="<?php echo esc_attr($seed->id); ?>">
    <?php if (!empty($seed->image_url)): ?>
        <img src="<?php echo esc_url($seed->image_url); ?>" alt="<?php echo esc_attr($seed->seed_name); ?>" class="esc-seed-image">
    <?php endif; ?>
    
    <div class="esc-seed-card-content">
        <h3><?php echo esc_html($seed->seed_name); ?>
            <?php if (!empty($seed->variety_name)): ?>
                <span class="esc-variety-name">- <?php echo esc_html($seed->variety_name); ?></span>
            <?php endif; ?>
        </h3>
        
        <!-- Display key information -->
        <div class="esc-seed-details">
            <?php ESC_Functions::display_seed_field($seed, 'plant_type', __('Type', 'erins-seed-catalog')); ?>
            <?php ESC_Functions::display_seed_field($seed, 'days_to_maturity', __('Matures In', 'erins-seed-catalog')); ?>
            <?php ESC_Functions::display_seed_field($seed, 'sunlight', __('Sunlight', 'erins-seed-catalog')); ?>
            <?php ESC_Functions::display_seed_field($seed, 'sowing_method', __('Sowing', 'erins-seed-catalog')); ?>
        </div>
        
        <!-- Categories -->
        <?php if (!empty($seed->categories)): ?>
            <div class="esc-categories">
                <?php foreach ($seed->categories as $category): ?>
                    <span class="esc-category"><?php echo esc_html($category->name); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
```

## Additional Customization Options

### Color Scheme

You can customize the color scheme by modifying the CSS variables at the top of `esc-enhanced-styles.css`:

```css
.esc-container {
  --esc-primary: #3a7bd5; /* Main theme color */
  --esc-primary-light: #d4e4f7;
  --esc-primary-dark: #2c5e9e;
  /* Other color variables */
}
```

### Card Layout

To adjust the number of cards per row in the catalog, modify this section in `esc-enhanced-styles.css`:

```css
.esc-seed-list {
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Adjust the 280px value */
}
```

## Troubleshooting

### Styles Not Applying

If the enhanced styles aren't applying correctly:

1. Check browser console for errors
2. Verify the stylesheet is being loaded (check Network tab in browser dev tools)
3. Try increasing the priority in the `wp_enqueue_scripts` action (use a higher number than 20)

### JavaScript Functionality Issues

If the enhanced scripts aren't working:

1. Check browser console for JavaScript errors
2. Verify jQuery is loaded before your script
3. Make sure the script is being loaded (check Network tab in browser dev tools)

## Support

For questions or customization help, please contact the developer.

# Fixing Floating Label Issues in Erin's Seed Catalog

This guide addresses the specific issue where form labels for "Seed Type" and "Variety (Optional)" are displaying incorrectly when the fields already contain values.

## The Issue

When input fields already contain values (like "tomato" and "Early Girl"), the floating labels are still displaying at their default position instead of moving up above the input, creating a confusing interface where:

1. The label text overlaps with the input value
2. It's unclear what the field represents
3. The visual hierarchy is broken

## Solution

The solution involves two parts:

1. CSS fixes to ensure labels properly position themselves when fields have values
2. JavaScript enhancements to detect pre-filled fields and apply the correct classes

### Implementation Steps

#### 1. Add the CSS Fix

Add the `esc-label-fix.css` file to your theme or to the plugin's assets folder.

If adding to your theme:
```php
function enqueue_esc_label_fix() {
    wp_enqueue_style(
        'esc-label-fix',
        get_stylesheet_directory_uri() . '/esc-label-fix.css',
        array('esc-modern-form'),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_esc_label_fix', 25);
```

If adding to the plugin enhancer:
```php
wp_enqueue_style(
    'esc-label-fix',
    ESC_ENHANCER_URL . 'assets/css/esc-label-fix.css',
    array('esc-modern-form', 'esc-enhanced-styles'),
    ESC_ENHANCER_VERSION
);
```

#### 2. Add the JavaScript Fix

Add the `esc-label-fix.js` file to your theme or to the plugin's assets folder.

If adding to your theme:
```php
function enqueue_esc_label_fix_js() {
    wp_enqueue_script(
        'esc-label-fix',
        get_stylesheet_directory_uri() . '/esc-label-fix.js',
        array('jquery'),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_esc_label_fix_js', 25);
```

If adding to the plugin enhancer:
```php
wp_enqueue_script(
    'esc-label-fix',
    ESC_ENHANCER_URL . 'assets/js/esc-label-fix.js',
    array('jquery'),
    ESC_ENHANCER_VERSION,
    true
);
```

#### 3. Alternative: Modify the Form Template (If Possible)

If you have access to modify the plugin's template files, you can also fix this by adjusting the HTML structure in `add-seed-form-modern.php`:

Change the input fields from:
```html
<div class="esc-floating-label">
    <input type="text" id="esc-seed-type" name="seed_type" required>
    <label for="esc-seed-type">Seed Type *</label>
</div>
```

To:
```html
<div class="esc-floating-label">
    <input type="text" id="esc-seed-type" name="seed_type" placeholder=" " required>
    <label for="esc-seed-type">Seed Type *</label>
</div>
```

The key change is adding a placeholder attribute (even an empty one with a space), which helps CSS selectors like `:not(:placeholder-shown)` work properly.

## Testing the Fix

After implementing the fix:

1. Go to a page with the `[erins_seed_catalog_add_form]` shortcode
2. Check if the labels for pre-filled fields appear above the input text
3. Try entering text in empty fields to verify the labels move up correctly
4. Try clearing fields to verify the labels move back to their original position

## Troubleshooting

If the fix doesn't work:

1. Check browser console for JavaScript errors
2. Verify the CSS and JS files are loading (check Network tab in browser dev tools)
3. Try increasing the priority in the `wp_enqueue_scripts` action (use a higher number than 25)
4. Make sure jQuery is properly loaded before your script

## Additional Customization

You can adjust the appearance of the floating labels by modifying these CSS variables in `esc-label-fix.css`:

```css
.esc-floating-label label {
  /* Adjust these values to change label positioning */
  top: 0;
  left: 15px;
  padding: 15px 0 0;
  
  /* Adjust these values to change label appearance when active */
  transform-origin: 0 0;
}

.esc-floating-label input:not(:placeholder-shown) + label {
  /* Adjust these values to change active label position */
  transform: translateY(-10px) scale(0.75);
  
  /* Adjust this to change active label color */
  color: #3a7bd5;
}
```

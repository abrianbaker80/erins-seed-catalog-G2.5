# Erin's Seed Catalog Enhancer - Installation Guide

This guide will help you install and configure the Erin's Seed Catalog Enhancer to improve the visual design and user experience of the plugin.

## Installation Options

You have two options for installing the enhancements:

### Option 1: Install the Enhancer Plugin (Recommended)

This is the simplest approach and doesn't require modifying any existing files.

1. **Download the plugin files**:
   - Download `esc-enhancer.php`
   - Create a folder called `assets` with two subfolders: `css` and `js`
   - Place `esc-enhanced-styles.css` in the `assets/css` folder
   - Place `esc-enhanced-scripts.js` in the `assets/js` folder

2. **Create a ZIP file** with this structure:
   ```
   esc-enhancer/
   ├── esc-enhancer.php
   ├── assets/
   │   ├── css/
   │   │   └── esc-enhanced-styles.css
   │   └── js/
   │       └── esc-enhanced-scripts.js
   ```

3. **Install the plugin**:
   - Go to WordPress Admin → Plugins → Add New
   - Click "Upload Plugin" and select your ZIP file
   - Click "Install Now" and then "Activate"

4. **That's it!** The enhancements will automatically apply to all pages using the Erin's Seed Catalog shortcodes.

### Option 2: Add Files to Your Theme

If you prefer to add the files directly to your theme:

1. **Upload the CSS file**:
   - Upload `esc-enhanced-styles.css` to your theme directory

2. **Upload the JS file**:
   - Upload `esc-enhanced-scripts.js` to your theme directory

3. **Add code to your theme's functions.php**:
   ```php
   function enqueue_esc_enhancements() {
       // Only load if we're on a page with the plugin's shortcodes
       global $post;
       if (!is_a($post, 'WP_Post')) {
           return;
       }

       $shortcodes = array(
           'erins_seed_catalog_add_form',
           'erins_seed_catalog_view',
           'erins_seed_catalog_search',
           'erins_seed_catalog_categories'
       );

       $has_shortcode = false;
       foreach ($shortcodes as $shortcode) {
           if (has_shortcode($post->post_content, $shortcode)) {
               $has_shortcode = true;
               break;
           }
       }

       if (!$has_shortcode) {
           return;
       }

       // Enqueue CSS
       wp_enqueue_style(
           'esc-enhanced-styles',
           get_stylesheet_directory_uri() . '/esc-enhanced-styles.css',
           array('esc-modern-form', 'esc-public-styles'),
           '1.0.0'
       );

       // Enqueue JS
       wp_enqueue_script(
           'esc-enhanced-scripts',
           get_stylesheet_directory_uri() . '/esc-enhanced-scripts.js',
           array('jquery', 'esc-public-scripts'),
           '1.0.0',
           true
       );
   }
   add_action('wp_enqueue_scripts', 'enqueue_esc_enhancements', 20);
   ```

## Verification

After installation, visit a page with any of the Erin's Seed Catalog shortcodes to verify the enhancements are working:

1. The form sections should be collapsible (only one section expanded at a time)
2. The seed catalog should display in a grid layout with enhanced cards
3. The overall styling should be more modern and visually appealing

## Customization

### Adjusting Colors

To match your site's color scheme, edit the CSS variables at the top of `esc-enhanced-styles.css`:

```css
.esc-container {
  --esc-primary: #3a7bd5; /* Change to your primary color */
  --esc-primary-light: #d4e4f7; /* Lighter shade of primary */
  --esc-primary-dark: #2c5e9e; /* Darker shade of primary */
  /* Other color variables */
}
```

### Adjusting the Grid Layout

To change the number of seed cards per row:

```css
.esc-seed-list {
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Adjust 280px value */
}
```

## Troubleshooting

### Styles Not Applying

If the enhanced styles aren't visible:

1. Check your browser's developer tools (F12) → Network tab to verify the CSS file is loading
2. Check for any JavaScript errors in the Console tab
3. Try increasing the priority in the `wp_enqueue_scripts` action (use a higher number than 20)

### Conflicts With Theme

If there are conflicts with your theme's styling:

1. Try adding `!important` to specific CSS rules that aren't being applied
2. Consider adjusting the CSS selectors to be more specific

## Support

If you need assistance with installation or customization, please contact us for support.

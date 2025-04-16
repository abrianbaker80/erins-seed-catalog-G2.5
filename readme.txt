=== Erin's Seed Catalog ===
Contributors: Your Name
Tags: seed catalog, garden, vegetables, gemini api, ai, mobile first
Requires at least: 6.0
Tested up to: 6.7.2
Requires PHP: 8.2
Stable tag: 1.2.71
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Catalog and track your vegetable garden seeds with AI-assisted information retrieval via the Gemini API. Mobile-first design.

== Description ==

Erin's Seed Catalog allows WordPress users to easily catalog their garden seeds. It features:

*   **AI-Assisted Information:** Uses the Google Gemini API to automatically fetch detailed information about seeds based on name, variety, brand, or SKU.
*   **Custom Database:** Stores all seed data in a dedicated database table, not using WordPress posts.
*   **Mobile-First Design:** Fully responsive and optimized for viewing and managing your catalog on smartphones and tablets.
*   **Frontend Display:** Use shortcodes to display an "Add New Seed" form, the full seed catalog, a search form, and seed categories on your public website.
*   **Categorization:** Organize seeds using a hierarchical category system.
*   **Admin Management:** Manage your seed catalog and plugin settings from the WordPress admin area.
*   **Excel Export:** Easily export your seed catalog to Excel with customizable column selection.

Requires a Google Gemini API key.

== Installation ==

1.  Upload the `erins-seed-catalog` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Go to the "Seed Catalog" -> "Settings" page in the WordPress admin menu.
4.  Enter your Google Gemini API Key and save the settings.
5.  Create pages for your seed catalog features using the shortcodes below.

=== Recommended Page Setup ===

For the best user experience, we recommend creating the following pages:

1. **Seed Catalog Page** - Main page to display your seed collection
   * Use the enhanced view: `[erins_seed_catalog_enhanced_view]`
   * Add the search form at the top: `[erins_seed_catalog_search]`

2. **Add Seed Page** - Page for adding new seeds
   * Use: `[erins_seed_catalog_add_form]`

3. **Categories Page** - Page to browse seeds by category
   * Use: `[erins_seed_catalog_categories show_count="true"]`

4. **Export Page** - Page for exporting the catalog (optional, admin-only)
   * Use: `[erins_seed_catalog_export]`
   * Consider restricting this page to administrators

See the Shortcodes section below for detailed information about each shortcode and its parameters.

== Frequently Asked Questions ==

= Where do I get a Gemini API Key? =

You can obtain a free API key from Google AI Studio: https://aistudio.google.com/app/apikey

= How does the AI search work? =

When adding a seed, enter the Seed Name (and optionally Variety, Brand, SKU). Click the "Fetch AI Info" button. The plugin sends this information to the Gemini API, which attempts to find relevant details and pre-fills the form fields. Review and edit the information before saving.

== Shortcodes ==

=== Main Shortcodes ===

**1. Add Seed Form**

`[erins_seed_catalog_add_form]`

Displays a form for adding new seeds to the catalog. This form includes AI-assisted information retrieval via the Gemini API.

*Parameters:* None

*Example:*
```
[erins_seed_catalog_add_form]
```

**2. Basic Seed Catalog View**

`[erins_seed_catalog_view]`

Displays the seed catalog in a basic list format with pagination.

*Parameters:*
* `per_page` - Number of seeds to display per page (default: 12)
* `category` - Filter by category slug or ID (default: empty, shows all)

*Examples:*
```
[erins_seed_catalog_view]
[erins_seed_catalog_view per_page="20"]
[erins_seed_catalog_view category="vegetables"]
```

**3. Enhanced Seed Catalog View**

`[erins_seed_catalog_enhanced_view]`

Displays the seed catalog with enhanced visual cards, improved layout, and interactive features.

*Parameters:*
* `per_page` - Number of seeds to display per page (default: 12)
* `category` - Filter by category slug or ID (default: empty, shows all)

*Examples:*
```
[erins_seed_catalog_enhanced_view]
[erins_seed_catalog_enhanced_view per_page="16"]
[erins_seed_catalog_enhanced_view category="flowers"]
```

**4. Seed Search Form**

`[erins_seed_catalog_search]`

Displays a search form that allows users to search the seed catalog.

*Parameters:* None

*Example:*
```
[erins_seed_catalog_search]
```

**5. Seed Categories List**

`[erins_seed_catalog_categories]`

Displays a list of seed categories, optionally showing the count of seeds in each category.

*Parameters:*
* `show_count` - Show number of seeds per category (default: false)
* `hierarchical` - Display categories hierarchically (default: true)
* `orderby` - Order categories by this field (default: 'name')
* `order` - Sort order, ASC or DESC (default: 'ASC')
* `title_li` - Title for the list item (default: empty)

*Examples:*
```
[erins_seed_catalog_categories]
[erins_seed_catalog_categories show_count="true"]
[erins_seed_catalog_categories hierarchical="false" orderby="count" order="DESC"]
```

**6. Seed Catalog Export Form**

`[erins_seed_catalog_export]`

Displays a form to export the seed catalog to Excel/CSV with customizable column selection.

*Parameters:* None

*Example:*
```
[erins_seed_catalog_export]
```

=== Development/Testing Shortcodes ===

These shortcodes are primarily for development and testing purposes:

**7. Modern Add Seed Form**

`[erins_seed_catalog_add_form_modern]`

Displays the modern version of the add seed form. This is a development shortcode.

**8. Refactored Add Seed Form**

`[erins_seed_catalog_add_form_refactored]`

Displays the refactored version of the add seed form with improved UI. This is now used by the main add_form shortcode.

**9. Test AI Results**

`[erins_seed_catalog_test_ai_results]`

Test shortcode for the enhanced AI results page. Used for development purposes.

**10. Test Integration**

`[erins_seed_catalog_test_integration]`

Test shortcode for integration testing. Used for development purposes.

== Changelog ==

= 1.2.71 =
* **Changes**
* image work

= 1.2.70 =
* **Changes**
* still trying to get image to upload

= 1.2.69 =
* **Changes**
* continued image fixes

= 1.2.68 =
* **Changes**
* image testing

= 1.2.67 =
* **Changes**
* image fix

= 1.2.66 =
* **Changes**
* image fix

= 1.2.65 =
* **Changes**
* image testing

= 1.2.64 =
* **Changes**
* image fixes

= 1.2.63 =
* **Changes**
* image work

= 1.2.62 =
* **Changes**
* image work

= 1.2.61 =
* **Changes**
* image work continued

= 1.2.60 =
* **Changes**
* image fix for catalog

= 1.2.59 =
* **Changes**
* image work continued

= 1.2.58 =
* **Changes**
* image

= 1.2.57 =
* **Changes**
* image

= 1.2.56 =
* **Changes**
* image upload

= 1.2.55 =
* **Changes**
* image fixes

= 1.2.54 =
* **Changes**
* image viewer

= 1.2.53 =
* **Changes**
* manual image download

= 1.2.52 =
* **Changes**
* image work continued

= 1.2.51 =
* **Changes**
* more image updates

= 1.2.50 =
* **Changes**
* continued ai work

= 1.2.49 =
* **Changes**
* continued image work

= 1.2.48 =
* **Changes**
* image url fixes

= 1.2.47 =
* **Changes**
* image dl fixes

= 1.2.46 =
* **Changes**
* fixed js for image download

= 1.2.45 =
* **Changes**
* added image download function

= 1.2.44 =
* **Changes**
* fixed js issue in add form

= 1.2.43 =
* **Changes**
* release

= 1.2.42 =
* **Changes**
* Fixed JavaScript error in the add seed form: "ESC namespace is not defined"
* Properly attached ESC namespace to the window object in esc-core.js
* Improved JavaScript module loading and initialization

= 1.2.41 =
* **Changes**
* release

= 1.2.40 =
* **Changes**
* Fixed shortcode conflict that was preventing the enhanced seed catalog view from displaying
* Removed duplicate shortcode class that was causing conflicts
* Improved plugin stability by eliminating class name conflicts

= 1.2.38 =
* **Changes**
* release

= 1.2.37 =
* **Changes**
* Fixed enhanced seed catalog view to properly display modern card styling
* Added cache-busting for enhanced card styles to ensure latest styles are loaded
* Improved CSS class structure for better styling consistency

= 1.2.36 =
* **Changes**
* test

= 1.2.35 =
* **Changes**
* update checker work

= 1.2.34 =
* **Changes**
* Fixed undefined constant `DAY_IN_SECONDS` in model updater class

= 1.2.33 =
* **Changes**
* Fixed GitHub update checker to properly detect and display available updates
* Added improved error logging for update checking
* Enhanced the "Check for Updates" functionality to provide better feedback

= 1.2.32 =
* **Changes**
* Fixed textdomain loading to use the 'init' hook instead of 'plugins_loaded' to prevent WordPress notices

= 1.2.31 =
* **Changes**
* test

= 1.2.30 =
* **Changes**
* Implemented new update checker

= 1.2.29 =
* **Changes**
* Implemented a new lightweight GitHub-based update checker
* Removed the old plugin update checker and all dependencies
* Fixed update checking functionality to properly detect new versions
* Added "Check for Updates" link in the plugin actions

= 1.2.28 =
* **Changes**
* test

= 1.2.27 =
* **Changes**
* Update checker removed due to errors

= 1.2.26 =
* **Changes**
* plugin update test

= 1.2.25 =
* **Changes**
* test

= 1.2.24 =
* **Changes**
* updater fix

= 1.2.23 =
* **Changes**
* Removed duplicate plugin info filter

= 1.2.22 =
* **Changes**
* Fixed plugin update checker by completely disabling readme parsing and providing plugin info directly

= 1.2.21 =
* **Changes**
* Reverted unnecessary vendor files inclusion

= 1.2.20 =
* **Changes**
* Fixed plugin update checker by including vendor files in the repository

= 1.2.20 =
* **Changes**
* parse issues

= 1.2.19 =
* **Changes**
* Fixed plugin update checker by completely disabling readme parsing and providing plugin info directly

= 1.2.18 =
* **Changes**
* readme.md fix

= 1.2.17 =
* **Changes**
* Fixed README.md duplication issues and improved changelog generation

= 1.2.16 =
* **Documentation**
* Updated documentation in README.md
* Updated documentation in readme.txt
* **Other Changes**
* Modified erins-seed-catalog.php

= 1.2.15 =
* **Documentation**
* Updated documentation in README.md
* Updated documentation in readme.txt
* **Other Changes**
* Modified erins-seed-catalog.php

= 1.2.14 =
* **Changes**
* Version bump test

= 1.2.16 =
* **Changes**
* Version bump to 1.2.16

= 1.2.15 =
* Updated to version 1.2.15

= 1.1.6 =
*   Fixed plugin update detection
*   Added improved GitHub update checker
*   Enhanced add seed form with modern UI
*   Added AI-powered variety suggestions
*   Improved user experience with floating labels and card-based layout

= 1.1.5 =
*   Updated version numbers and documentation

= 1.1.2 =
*   Added ability to select different Gemini models
*   Added model testing functionality
*   Added usage statistics tracking
*   Added detailed model capabilities display
*   Fixed WordPress class and function recognition in development environment
*   Added release automation scripts

= 1.0.0 =
*   Initial release.

== Upgrade Notice ==

= 1.2.71 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.70 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.69 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.68 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.67 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.66 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.65 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.64 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.63 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.62 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.61 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.60 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.59 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.58 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.57 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.56 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.55 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.54 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.53 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.52 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.51 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.50 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.49 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.48 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.47 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.46 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.45 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.44 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.43 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.42 =
This update fixes a JavaScript error in the add seed form and improves JavaScript module loading.

= 1.2.41 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.40 =
This update fixes a critical issue with the enhanced seed catalog view shortcode not displaying properly.

= 1.2.38 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.37 =
This update fixes the enhanced seed catalog view to properly display modern card styling.

= 1.2.36 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.35 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.34 =
This update fixes an undefined constant issue in the model updater class.

= 1.2.33 =
This update fixes the GitHub update checker to properly detect and display available updates.

= 1.2.32 =
This update fixes textdomain loading to prevent WordPress notices.

= 1.2.31 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.30 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.29 =
This update implements a new lightweight GitHub-based update checker and fixes update checking functionality.

= 1.2.28 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.27 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.26 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.25 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.24 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.23 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.22 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.21 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.20 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.20 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.19 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.18 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.17 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.16 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.15 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.14 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.16 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.15 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.1.6 =
This update fixes plugin update detection and adds an improved add seed form with modern UI and AI-powered variety suggestions.

= 1.1.5 =
This update includes version number updates and documentation improvements.

= 1.1.2 =
This update adds the ability to select different Gemini models, model testing, usage statistics, and more.

= 1.0.0 =
Initial release.
















































































































































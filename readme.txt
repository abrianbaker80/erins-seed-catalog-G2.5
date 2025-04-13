=== Erin's Seed Catalog ===
Contributors: Your Name
Tags: seed catalog, garden, vegetables, gemini api, ai, mobile first
Requires at least: 6.0
Tested up to: 6.7.2
Requires PHP: 8.2
Stable tag: 1.2.26
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

= 1.2.26 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.25 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.24 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.23 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.22 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.21 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.20 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.20 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.19 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.18 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.17 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.16 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.15 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.14 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.16 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.2.15 =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n= 1.1.6 =
This update fixes plugin update detection and adds an improved add seed form with modern UI and AI-powered variety suggestions.

= 1.1.5 =
This update includes version number updates and documentation improvements.

= 1.1.2 =
This update adds the ability to select different Gemini models, model testing, usage statistics, and more.

= 1.0.0 =
Initial release.











































































































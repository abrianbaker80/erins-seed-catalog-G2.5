=== Erin's Seed Catalog ===
Contributors: Your Name
Tags: seed catalog, garden, vegetables, gemini api, ai, mobile first
Requires at least: 6.0
Tested up to: 6.7.2
Requires PHP: 8.2
Stable tag: 1.1.42
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
5.  Use the shortcodes on your pages/posts:
    *   `[erins_seed_catalog_add_form]` - Displays the form to add a new seed.
    *   `[erins_seed_catalog_view]` - Displays the seed catalog.
    *   `[erins_seed_catalog_enhanced_view]` - Displays the seed catalog with enhanced visual cards.
    *   `[erins_seed_catalog_search]` - Displays the search form for the catalog.
    *   `[erins_seed_catalog_categories]` - Displays the list of seed categories.
    *   `[erins_seed_catalog_export]` - Displays a form to export the catalog to Excel with column selection.

== Frequently Asked Questions ==

= Where do I get a Gemini API Key? =

You can obtain a free API key from Google AI Studio: https://aistudio.google.com/app/apikey

= How does the AI search work? =

When adding a seed, enter the Seed Name (and optionally Variety, Brand, SKU). Click the "Fetch AI Info" button. The plugin sends this information to the Gemini API, which attempts to find relevant details and pre-fills the form fields. Review and edit the information before saving.

== Shortcodes ==

*   `[erins_seed_catalog_add_form]`
*   `[erins_seed_catalog_view]`
*   `[erins_seed_catalog_enhanced_view]`
*   `[erins_seed_catalog_search]`
*   `[erins_seed_catalog_categories]`
*   `[erins_seed_catalog_export]`

== Screenshots ==

(Coming Soon)

== Changelog ==

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

= 1.1.6 =
This update fixes plugin update detection and adds an improved add seed form with modern UI and AI-powered variety suggestions.

= 1.1.5 =
This update includes version number updates and documentation improvements.

= 1.1.2 =
This update adds the ability to select different Gemini models, model testing, usage statistics, and more.

= 1.0.0 =
Initial release.










































# Erin's Seed Catalog

A WordPress plugin designed to help gardeners catalog and track their vegetable garden seeds.

## Version History

## Version 1.2.59 - 2025-04-13 20:18:46

### Changes
- image work continued


## Version 1.2.58 - 2025-04-13 19:17:42

#

## Version 1.2.57 - 2025-04-13 18:55:55

#

## Version 1.2.56 - 2025-04-13 18:50:16

#

## Version 1.2.55 - 2025-04-13 18:40:20

#

## Version 1.2.54 - 2025-04-13 18:30:01

#

## Version 1.2.53 - 2025-04-13 18:23:06

#

## Version 1.2.52 - 2025-04-13 17:46:46

#

## Version 1.2.51 - 2025-04-13 17:27:20

#

## Version 1.2.50 - 2025-04-13 16:53:58

#

## Version 1.2.49 - 2025-04-13 16:33:32

#

## Version 1.2.48 - 2025-04-13 16:27:50

#

## Version 1.2.47 - 2025-04-13 15:53:08

#

## Version 1.2.46 - 2025-04-13 14:54:01

#

## Version 1.2.45 - 2025-04-13 14:48:12

#

## Version 1.2.44 - 2025-04-13 14:23:44

#

## Version 1.2.43 - 2025-04-13 14:20:18

#

## Version 1.2.42 - 2025-04-13 23:30:00

#

## Version 1.2.41 - 2025-04-13 14:05:41

#

## Version 1.2.40 - 2025-04-13 22:30:00

#

## Version 1.2.38 - 2025-04-13 13:48:17

#

## Version 1.2.37 - 2025-04-13 21:30:00

#

## Version 1.2.36 - 2025-04-13 13:31:30

#

## Version 1.2.35 - 2025-04-13 13:26:27

#

## Version 1.2.34 - 2025-04-13 20:30:00

#

## Version 1.2.33 - 2025-04-13 19:30:00

#

## Version 1.2.32 - 2025-04-13 18:30:00

#

## Version 1.2.31 - 2025-04-13 13:16:19

#

## Version 1.2.30 - 2025-04-13 13:12:53

#

## Version 1.2.29 - 2025-04-13 14:30:00

#

## Version 1.2.28 - 2025-04-13 13:01:08

#

## Version 1.2.27 - 2025-04-13 12:54:16

#

## Version 1.2.26 - 2025-04-13 12:34:31

#

## Version 1.2.25 - 2025-04-12 23:06:49

#

## Version 1.2.24 - 2025-04-12 22:57:05

#

## Version 1.2.23 - 2025-04-12 22:42:11

#

## Version 1.2.22 - 2025-04-12 22:39:06

#

## Version 1.2.21 - 2025-04-12 22:31:17

#

## Version 1.2.20 - 2025-04-12 22:26:11

#

## Version 1.2.19 - 2025-04-12 21:28:24

#

## Version 1.2.18 - 2025-04-12 20:33:52

#

### Version 1.2.17 - 2025-04-12 20:31:27
##

### Version 1.2.16 - 2025-04-12 20:12:21
##

### Version 1.2.15 - 2025-04-12 20:09:42
##

### Version 1.2.14 - 2025-04-12 20:04:45
##

### Version 1.2.7 - 1.2.13
- Version bump only

### Version 1.2.6 - 2025-04-12 17:20:48
##

### Version 1.2.5 - 2025-04-12 17:08:25
##

### Version 1.2.4 - 2025-04-12 16:56:15
##

### Version 1.2.3 - 2025-04-12 16:51:28
##

### Version 1.2.2 - 2025-04-12 16:48:21
##

### Version 1.2.1 - 2025-04-12 16:44:12
##

### Version 1.2.0
##

## Features

### Core Features
- AI-Assisted Information: Uses Google's Gemini API to automatically fetch detailed information about seeds
- Custom Database: Stores seed data in a dedicated database table
- Mobile-First Design: Optimized for viewing and managing the catalog on smartphones and tablets
- Frontend Display: Provides shortcodes to display seed catalog, add forms, search functionality, and categories
- Categorization: Organizes seeds using a hierarchical category system
- Admin Management: Includes admin interfaces to manage the seed catalog and plugin settings
- Excel Export: Allows exporting the seed catalog to CSV

### UI Components
- Modern Design System: Consistent styling across all plugin components
- Interactive Form Cards: Collapsible sections for better organization of form fields
- Loading Animations: Visual feedback during AI data fetching
- Confidence Indicators: Shows confidence level of AI-generated information
- Success Confirmation: Modal dialog with action buttons after successful submission

### Technical Features
- Modular JavaScript Architecture: Organized into core, UI, form, AI, and variety modules
- BEM CSS Methodology: Consistent and maintainable CSS naming conventions
- Select2 Integration: Enhanced dropdowns for better user experience
- Debug Mode: Configurable logging for easier troubleshooting
- Responsive Design: Works on all device sizes from mobile to desktop

## Shortcodes

### Main Shortcodes

#### 1. Add Seed Form

`
[erins_seed_catalog_add_form]
`

Displays a form for adding new seeds to the catalog. This form includes AI-assisted information retrieval via the Gemini API.

#### 2. Basic Seed Catalog View

`
[erins_seed_catalog_view]
`

Displays the seed catalog in a basic list format with pagination.

**Parameters:**
- per_page - Number of seeds to display per page (default: 12)
- category - Filter by category slug or ID (default: empty, shows all)

**Examples:**
`
[erins_seed_catalog_view]
[erins_seed_catalog_view per_page="20"]
[erins_seed_catalog_view category="vegetables"]
`

#### 3. Enhanced Seed Catalog View

`
[erins_seed_catalog_enhanced_view]
`

Displays the seed catalog with enhanced visual cards, improved layout, and interactive features.

**Parameters:**
- per_page - Number of seeds to display per page (default: 12)
- category - Filter by category slug or ID (default: empty, shows all)

**Examples:**
`
[erins_seed_catalog_enhanced_view]
[erins_seed_catalog_enhanced_view per_page="16"]
[erins_seed_catalog_enhanced_view category="flowers"]
`

#### 4. Seed Search Form

`
[erins_seed_catalog_search]
`

Displays a search form that allows users to search the seed catalog.

#### 5. Seed Categories List

`
[erins_seed_catalog_categories]
`

Displays a list of seed categories, optionally showing the count of seeds in each category.

**Parameters:**
- show_count - Show number of seeds per category (default: false)
- hierarchical - Display categories hierarchically (default: true)
- orderby - Order categories by this field (default: 'name')
- order - Sort order, ASC or DESC (default: 'ASC')
- 	itle_li - Title for the list item (default: empty)

**Examples:**
`
[erins_seed_catalog_categories]
[erins_seed_catalog_categories show_count="true"]
[erins_seed_catalog_categories hierarchical="false" orderby="count" order="DESC"]
`

#### 6. Seed Catalog Export Form

`
[erins_seed_catalog_export]
`

Displays a form to export the seed catalog to Excel/CSV with customizable column selection.

### Development/Testing Shortcodes

These shortcodes are primarily for development and testing purposes:

- [erins_seed_catalog_add_form_modern] - Modern version of the add seed form
- [erins_seed_catalog_add_form_refactored] - Refactored version with improved UI
- [erins_seed_catalog_test_ai_results] - Test shortcode for the enhanced AI results page
- [erins_seed_catalog_test_integration] - Test shortcode for integration testing

## Development Setup

This project is set up for development with VS Code and Laragon/XAMPP.

### Prerequisites

- [VS Code](https://code.visualstudio.com/)
- [Laragon](https://laragon.org/) or [XAMPP](https://www.apachefriends.org/)
- [Composer](https://getcomposer.org/)
- [Git](https://git-scm.com/)

### VS Code Extensions

The following extensions are recommended for development:

- PHP Intelephense
- PHP Debug (Xdebug)
- WordPress Snippets
- WordPress VS Code Extension
- PHP CS Fixer

### Development Environment Setup

1. Clone this repository into your WordPress plugins directory:
   `
   cd C:/laragon/www/your-wordpress-site/wp-content/plugins
   git clone https://github.com/abrianbaker80/erins-seed-catalog-G2.5.git
   `

2. Install dependencies:
   `
   cd erins-seed-catalog-G2.5
   composer install
   `

3. Open the project in VS Code:
   `
   code .
   `

4. Activate the plugin in WordPress admin.

### Development Workflow

1. Make changes to the code in VS Code
2. Test your changes in the browser
3. Use the debugging tools in VS Code to troubleshoot issues
4. Commit your changes to Git

### Deployment

To deploy the plugin to a production WordPress site:

1. Create a deployment package:
   `
   Compress-Archive -Path * -DestinationPath 'erins-seed-catalog-v1.2.0.zip' -Force
   `

2. Use the included deployment script:
   `
   # Edit deploy-to-wordpress.ps1 to update server details
   ./deploy-to-wordpress.ps1
   `

3. Alternatively, manually upload the zip file to your WordPress site and install it through the WordPress admin interface.

### Debugging

This project is configured for debugging with Xdebug. To use it:

1. Make sure Xdebug is installed and configured in your PHP installation
2. Start the debugging session in VS Code by clicking the "Run and Debug" button
3. Set breakpoints in your code
4. Trigger the code by refreshing the page in your browser

## License

This project is licensed under the GPL v2 or later.

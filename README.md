# Erin's Seed Catalog

A WordPress plugin designed to help gardeners catalog and track their vegetable garden seeds.

## Version 1.2.4 - 2025-04-12 16:56:15

### Bug Fixes
- Fix duplicate version entries in README.md updates

## Version 1.2.3 - 2025-04-12 16:51:28

### New Features
- Add debug output to README.md update process in simple-release.ps1

## Version 1.2.4 - 2025-04-12 16:56:15

### Bug Fixes
- Fix duplicate version entries in README.md updates

## Version 1.2.2 - 2025-04-12 16:48:21

### Other Changes
- Make README.md updates automatic by default in simple-release.ps1

## Version 1.2.4 - 2025-04-12 16:56:15

### Bug Fixes
- Fix duplicate version entries in README.md updates

## Version 1.2.1 - 2025-04-12 16:44:12

### New Features
- Add debug output for timestamp in README.md updates
- Add timestamp to README.md version headers
- Add README.md changelog update functionality to simple-release.ps1

### Other Changes
- Update simple-release.ps1 with major and minor version options
- Update README.md with version 1.2.0 changes and new features
- Bump version to 1.2.0 for production release
- Complete UI refactoring with fixed form functionality

## Version 1.2.4 - 2025-04-12 16:56:15

### Bug Fixes
- Fix duplicate version entries in README.md updates

## Version 1.2.0 Updates

### UI Refactoring
- Completely refactored the user interface with a modern design system
- Implemented BEM methodology for consistent CSS naming conventions
- Added responsive design improvements for better mobile experience
- Fixed form field styling inconsistencies

### Form Functionality Improvements
- Fixed AI-assisted seed information population
- Improved field validation and error handling
- Enhanced user feedback during form submission
- Added proper handling for boolean values (Yes/No fields)
- Fixed category dropdown with Select2 integration

### Code Quality Improvements
- Reorganized JavaScript into modular components
- Added debug mode for easier troubleshooting
- Fixed circular references in logging functions
- Improved error handling and user feedback

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
   ```
   cd C:/laragon/www/your-wordpress-site/wp-content/plugins
   git clone https://github.com/abrianbaker80/erins-seed-catalog-G2.5.git
   ```

2. Install dependencies:
   ```
   cd erins-seed-catalog-G2.5
   composer install
   ```

3. Open the project in VS Code:
   ```
   code .
   ```

4. Activate the plugin in WordPress admin.

### Development Workflow

1. Make changes to the code in VS Code
2. Test your changes in the browser
3. Use the debugging tools in VS Code to troubleshoot issues
4. Commit your changes to Git

### Deployment

To deploy the plugin to a production WordPress site:

1. Create a deployment package:
   ```
   Compress-Archive -Path * -DestinationPath 'erins-seed-catalog-v1.2.0.zip' -Force
   ```

2. Use the included deployment script:
   ```
   # Edit deploy-to-wordpress.ps1 to update server details
   ./deploy-to-wordpress.ps1
   ```

3. Alternatively, manually upload the zip file to your WordPress site and install it through the WordPress admin interface.

### Debugging

This project is configured for debugging with Xdebug. To use it:

1. Make sure Xdebug is installed and configured in your PHP installation
2. Start the debugging session in VS Code by clicking the "Run and Debug" button
3. Set breakpoints in your code
4. Trigger the code by refreshing the page in your browser

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

## License

This project is licensed under the GPL v2 or later.





# Erin's Seed Catalog

A WordPress plugin designed to help gardeners catalog and track their vegetable garden seeds.

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

### Debugging

This project is configured for debugging with Xdebug. To use it:

1. Make sure Xdebug is installed and configured in your PHP installation
2. Start the debugging session in VS Code by clicking the "Run and Debug" button
3. Set breakpoints in your code
4. Trigger the code by refreshing the page in your browser

## Features

- AI-Assisted Information: Uses Google's Gemini API to automatically fetch detailed information about seeds
- Custom Database: Stores seed data in a dedicated database table
- Mobile-First Design: Optimized for viewing and managing the catalog on smartphones and tablets
- Frontend Display: Provides shortcodes to display seed catalog, add forms, search functionality, and categories
- Categorization: Organizes seeds using a hierarchical category system
- Admin Management: Includes admin interfaces to manage the seed catalog and plugin settings
- Excel Export: Allows exporting the seed catalog to CSV

## License

This project is licensed under the GPL v2 or later.

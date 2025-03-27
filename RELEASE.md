# Release Automation for Erin's Seed Catalog

This document explains how to use the automated release process for the Erin's Seed Catalog WordPress plugin.

## Prerequisites

Before using the release automation script, ensure you have the following installed:

1. **Git** - For version control operations
2. **PowerShell** - For running the release script
3. **GitHub CLI** - For creating GitHub releases

You also need to be authenticated with GitHub. You can either:
- Run `gh auth login` to authenticate the GitHub CLI
- Set the `GITHUB_TOKEN` environment variable with a personal access token

## Using the Release Script

### Basic Usage

To create a new release with default settings (patch version increment):

```
.\release.bat
```

This will:
1. Increment the patch version (e.g., 1.0.0 → 1.0.1)
2. Update version numbers in the plugin files
3. Generate a commit message based on changes
4. Commit and push changes to GitHub
5. Create a new GitHub release

### Command Line Options

The release script supports several command line options:

```
release.bat [options]
```

Options:
- `--major` - Increment major version (x.0.0)
- `--minor` - Increment minor version (0.x.0)
- `--patch` - Increment patch version (0.0.x) [default]
- `--title "TITLE"` - Set custom release title
- `--description "DESC"` - Set custom release description
- `--dry-run` - Run without making actual changes
- `--help` - Show help message

### Examples

#### Create a minor version release:

```
.\release.bat --minor
```

#### Create a major version release with custom title:

```
.\release.bat --major --title "Major Release with New Features"
```

#### Test the release process without making changes:

```
.\release.bat --dry-run
```

## How It Works

The release script performs the following steps:

1. **Version Increment**: Automatically increments the version number based on SemVer (major.minor.patch)
2. **File Updates**: Updates version numbers in the main plugin file and readme.txt
3. **Commit Message Generation**: Analyzes changes and generates a structured commit message
4. **Git Operations**: Commits changes, creates a version tag, and pushes to GitHub
5. **GitHub Release**: Creates a new release on GitHub with release notes

## Troubleshooting

If you encounter issues with the release script:

1. **Authentication Issues**: Ensure you're authenticated with GitHub by running `gh auth status`
2. **Permission Issues**: Make sure you have write access to the repository
3. **Execution Policy**: If PowerShell blocks script execution, run `Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass`

## Simple Release Script

If you encounter issues with the main release script, you can use the simplified version:

```
.\simple-release.bat [--major|--minor|--patch]
```

This script performs the basic release tasks:

1. Updates version numbers in plugin files
2. Commits changes with a standard release message
3. Creates and pushes a version tag
4. Creates a GitHub release (if GitHub CLI is installed)

The simple script has fewer features but is more reliable and easier to use.

## Manual Release

If you need to perform a manual release:

1. Update version numbers in:
   - `erins-seed-catalog.php` (Version header and ESC_VERSION constant)
   - `readme.txt` (Stable tag)
2. Commit changes with a descriptive message
3. Create and push a tag: `git tag -a "v1.0.0" -m "Version 1.0.0"` and `git push origin v1.0.0`
4. Create a release on GitHub manually

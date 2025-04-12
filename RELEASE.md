# Release Automation for Erin's Seed Catalog

This document explains how to use the automated release process for the Erin's Seed Catalog WordPress plugin.

## Prerequisites

Before using the release automation script, ensure you have the following installed:

1. **Git** - For version control operations
2. **PowerShell** - For running the release script
3. **GitHub CLI** (optional) - For creating GitHub releases

You also need to be authenticated with GitHub. You can either:
- Run `gh auth login` to authenticate the GitHub CLI
- Set the `GITHUB_TOKEN` environment variable with a personal access token

## Using the Simple Release Script

### Basic Usage

To create a new release with default settings (patch version increment):

```
.\simple-release.ps1
```

This will:
1. Increment the patch version (e.g., 1.0.0 → 1.0.1)
2. Update version numbers in the plugin files
3. Update README.md with changelog information
4. Generate a commit message based on changes
5. Commit and push changes to GitHub (with confirmation)
6. Create a new GitHub release (with confirmation)

### Command Line Options

The release script supports several command line options:

```
.\simple-release.ps1 [options]
```

Options:
- `-Major` - Increment major version (x.0.0)
- `-Minor` - Increment minor version (0.x.0)
- `-DryRun` - Run without making actual changes
- `-ReleaseTitle "TITLE"` - Set custom release title
- `-ReleaseDescription "DESC"` - Set custom release description
- `-SkipReadmeUpdate` - Don't update README.md with changelog

### Examples

#### Create a minor version release:

```
.\simple-release.ps1 -Minor
```

#### Create a major version release with custom title:

```
.\simple-release.ps1 -Major -ReleaseTitle "Major Release with New Features"
```

#### Test the release process without making changes:

```
.\simple-release.ps1 -DryRun
```

## How It Works

The release script performs the following steps:

1. **Version Increment**: Automatically increments the version number based on SemVer (major.minor.patch)
2. **File Updates**: Updates version numbers in the main plugin file and readme.txt
3. **README Updates**: Updates README.md with changelog information including timestamp
4. **Commit Message Generation**: Analyzes changes and generates a structured commit message
5. **Git Operations**: Commits changes, creates a version tag, and pushes to GitHub (with confirmation)
6. **GitHub Release**: Creates a new release on GitHub with release notes (with confirmation)

## Troubleshooting

If you encounter issues with the release script:

1. **Authentication Issues**: Ensure you're authenticated with GitHub by running `gh auth status`
2. **Permission Issues**: Make sure you have write access to the repository
3. **Execution Policy**: If PowerShell blocks script execution, run `Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass`

## Manual Release

If you need to perform a manual release:

1. Update version numbers in:
   - `erins-seed-catalog.php` (Version header and ESC_VERSION constant)
   - `readme.txt` (Stable tag)
2. Commit changes with a descriptive message
3. Create and push a tag: `git tag -a "v1.0.0" -m "Version 1.0.0"` and `git push origin v1.0.0`
4. Create a release on GitHub manually

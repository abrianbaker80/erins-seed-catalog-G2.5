# Simple Release Script for Erin's Seed Catalog
#
# Usage:
#   ./simple-release.ps1                  - Increment patch version (0.0.x)
#   ./simple-release.ps1 -Major           - Increment major version (x.0.0)
#   ./simple-release.ps1 -Minor           - Increment minor version (0.x.0)
#   ./simple-release.ps1 -DryRun          - Show what would happen without making changes
#   ./simple-release.ps1 -ReleaseTitle "Title" -ReleaseDescription "Description" - Custom release info
#   ./simple-release.ps1                  - README.md is updated with changelog by default
#   ./simple-release.ps1 -SkipReadmeUpdate - Don't update README.md

param (
    [string]$VersionType = "patch",
    [switch]$DryRun = $false,
    [string]$ReleaseTitle = "",
    [string]$ReleaseDescription = "",
    [switch]$Major = $false,
    [switch]$Minor = $false,
    [switch]$SkipReadmeUpdate = $false
)

# Handle version type from switches
if ($Major) {
    $VersionType = "major"
}
elseif ($Minor) {
    $VersionType = "minor"
}

# Determine if README should be updated - default is to update unless explicitly skipped
$shouldUpdateReadme = -not $SkipReadmeUpdate

if ($shouldUpdateReadme) {
    Write-Host "README.md will be updated with changelog" -ForegroundColor Cyan
} else {
    Write-Host "README.md will not be updated" -ForegroundColor Yellow
}

# Configuration
$pluginFile = "erins-seed-catalog.php"

# Get current version
function Get-Version {
    $content = Get-Content $pluginFile -Raw
    if ($content -match "Version:\s*(\d+\.\d+\.\d+)") {
        return $matches[1]
    }
    else {
        Write-Host "Could not find version in plugin file" -ForegroundColor Red
        exit 1
    }
}

# Update version
function Update-Version {
    param (
        [string]$version,
        [string]$type
    )

    $parts = $version -split "\."
    $major = [int]$parts[0]
    $minor = [int]$parts[1]
    $patch = [int]$parts[2]

    switch ($type) {
        "major" { $major++; $minor = 0; $patch = 0 }
        "minor" { $minor++; $patch = 0 }
        "patch" { $patch++ }
    }

    return "$major.$minor.$patch"
}

# Convert Markdown changelog to WordPress readme.txt format
function Convert-MarkdownToWordPressChangelog {
    param (
        [string]$markdownChangelog,
        [string]$version
    )

    # Extract content from the markdown changelog
    $lines = $markdownChangelog -split "`n"

    # Initialize WordPress format changelog
    $wpChangelog = "= $version =`n"

    # Track if we've added any content
    $hasContent = $false

    # Process each line
    foreach ($line in $lines) {
        # Skip empty lines
        if (-not $line.Trim()) { continue }

        # Skip the version header line
        if ($line -match "^## Version") {
            continue
        }

        # Handle section headers - add as comments in WordPress format
        if ($line -match "^### (.+)") {
            $sectionName = $Matches[1]
            $wpChangelog += "* **$sectionName**`n"
            $hasContent = $true
            continue
        }

        # Handle bullet points
        if ($line -match "^- (.+)") {
            $bulletContent = $Matches[1]
            $wpChangelog += "* $bulletContent`n"
            $hasContent = $true
        }
    }

    # If no content was added, add a generic message
    if (-not $hasContent) {
        $wpChangelog += "* Updated to version $version`n"
    }

    # Add a newline at the end
    $wpChangelog += "`n"

    return $wpChangelog
}

# Get changes since last tag
function Get-ChangesSinceLastTag {
    param (
        [string]$newVersion,
        [string]$releaseDescription = ""
    )

    Write-Host "Getting changes since last tag..." -ForegroundColor Cyan

    # Get the last tag
    $lastTag = git describe --tags --abbrev=0 2>$null

    if (-not $lastTag) {
        Write-Host "No previous tags found. Using first commit." -ForegroundColor Yellow
        $lastTag = git rev-list --max-parents=0 HEAD
    }

    # Build changelog with timestamp
    $currentDate = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Write-Host "Adding timestamp: $currentDate" -ForegroundColor Cyan
    $changelog = "## Version $newVersion - $currentDate`n`n"

    # If a release description was provided, use it instead of generating from commits
    if ($releaseDescription) {
        Write-Host "Using provided release description" -ForegroundColor Green

        # Split the description into lines and format as Markdown
        $descLines = $releaseDescription -split "`n"
        $inSection = $false

        foreach ($line in $descLines) {
            $line = $line.Trim()
            if (-not $line) { continue }

            # Check if this is a section header
            if ($line -match "^(Features|Fixes|Documentation|Other Changes):") {
                if ($inSection) { $changelog += "`n" }
                $sectionName = $Matches[1]
                $changelog += "### $sectionName`n"
                $inSection = $true
            }
            # Check if this is a bullet point
            elseif ($line -match "^[-*]\s+(.+)") {
                $bulletContent = $Matches[1]
                $changelog += "- $bulletContent`n"
            }
            # Otherwise, treat as a regular line
            elseif ($inSection) {
                $changelog += "- $line`n"
            }
            else {
                # If we haven't started a section yet, create a default one
                $changelog += "### Changes`n"
                $changelog += "- $line`n"
                $inSection = $true
            }
        }

        $changelog += "`n"
        return $changelog
    }

    # Get all commits since last tag (including merges)
    $commits = git log "$lastTag..HEAD" --pretty=format:"%s"
    Write-Host "Found $(($commits | Measure-Object).Count) commits since last tag" -ForegroundColor Cyan

    # Get changed files since last tag
    $changedFiles = git diff --name-only "$lastTag..HEAD"
    Write-Host "Found $(($changedFiles | Measure-Object).Count) changed files since last tag" -ForegroundColor Cyan

    # Categorize commits
    $features = @()
    $fixes = @()
    $docs = @()
    $other = @()

    foreach ($commit in $commits) {
        # Skip release commits
        if ($commit -match "^Release v\d+\.\d+\.\d+") {
            continue
        }

        # Categorize based on more flexible patterns
        if ($commit -match "add|new|implement|feature|feat|enhance|improve") {
            $features += $commit
        }
        elseif ($commit -match "fix|bug|issue|error|warning|notice|resolve|correct") {
            $fixes += $commit
        }
        elseif ($commit -match "doc|docs|readme|documentation") {
            $docs += $commit
        }
        else {
            $other += $commit
        }
    }

    # If no commits were categorized, try to categorize based on changed files
    if ($features.Count -eq 0 -and $fixes.Count -eq 0 -and $docs.Count -eq 0 -and $other.Count -eq 0) {
        Write-Host "No categorized commits found. Analyzing changed files..." -ForegroundColor Yellow

        foreach ($file in $changedFiles) {
            $extension = [System.IO.Path]::GetExtension($file)
            $fileName = [System.IO.Path]::GetFileName($file)
            $directory = [System.IO.Path]::GetDirectoryName($file)

            # Skip version bump files
            if ($fileName -eq "simple-release.ps1") { continue }

            # Categorize based on file patterns
            if ($fileName -match "README|readme") {
                $docs += "Updated documentation in $fileName"
            }
            elseif ($directory -match "admin|includes" -and $extension -match "\.php$") {
                # Try to determine if it's a feature or fix based on git diff
                $diff = git diff "$lastTag..HEAD" -- $file
                if ($diff -match "fix|bug|issue|error|warning|notice") {
                    $fixes += "Fixed issues in $fileName"
                } else {
                    $features += "Updated functionality in $fileName"
                }
            }
            elseif ($extension -match "\.css$|\.js$") {
                $features += "Updated UI/UX in $fileName"
            }
            else {
                $other += "Modified $fileName"
            }
        }
    }

    # Build the changelog sections
    if ($features.Count -gt 0) {
        $changelog += "### New Features`n"
        foreach ($feature in $features) {
            # Clean up commit message
            $feature = $feature -replace "^feat\(.*\):\s*", "" -replace "^feature:\s*", ""
            # Ensure first letter is uppercase
            if ($feature.Length -gt 0) {
                $feature = $feature.Substring(0, 1).ToUpper() + $feature.Substring(1)
            }
            $changelog += "- $feature`n"
        }
        $changelog += "`n"
    }

    if ($fixes.Count -gt 0) {
        $changelog += "### Bug Fixes`n"
        foreach ($fix in $fixes) {
            # Clean up commit message
            $fix = $fix -replace "^fix\(.*\):\s*", "" -replace "^bug:\s*", ""
            # Ensure first letter is uppercase
            if ($fix.Length -gt 0) {
                $fix = $fix.Substring(0, 1).ToUpper() + $fix.Substring(1)
            }
            $changelog += "- $fix`n"
        }
        $changelog += "`n"
    }

    if ($docs.Count -gt 0) {
        $changelog += "### Documentation`n"
        foreach ($doc in $docs) {
            # Clean up commit message
            $doc = $doc -replace "^docs\(.*\):\s*", "" -replace "^doc:\s*", ""
            # Ensure first letter is uppercase
            if ($doc.Length -gt 0) {
                $doc = $doc.Substring(0, 1).ToUpper() + $doc.Substring(1)
            }
            $changelog += "- $doc`n"
        }
        $changelog += "`n"
    }

    if ($other.Count -gt 0) {
        $changelog += "### Other Changes`n"
        foreach ($change in $other) {
            # Clean up commit message
            # Ensure first letter is uppercase
            if ($change.Length -gt 0) {
                $change = $change.Substring(0, 1).ToUpper() + $change.Substring(1)
            }
            $changelog += "- $change`n"
        }
        $changelog += "`n"
    }

    # If still no changes were categorized, prompt the user for a description
    if ($features.Count -eq 0 -and $fixes.Count -eq 0 -and $docs.Count -eq 0 -and $other.Count -eq 0) {
        Write-Host "No changes detected. Please enter a brief description of this release:" -ForegroundColor Yellow
        $userDescription = Read-Host "Description (or press Enter for generic version bump)"

        if ($userDescription) {
            $changelog += "### Changes`n"
            $changelog += "- $userDescription`n`n"
        } else {
            $changelog += "### Changes`n"
            $changelog += "- Version bump to $newVersion`n`n"
        }
    }

    return $changelog
}

# Update README.md with changelog
function Update-ReadmeChangelog {
    param (
        [string]$newVersion,
        [string]$changelog
    )

    if ($DryRun) {
        Write-Host "DRY RUN: Would update README.md with changelog for version $newVersion" -ForegroundColor Yellow
        Write-Host "DRY RUN: Changelog content:" -ForegroundColor Yellow
        Write-Host $changelog -ForegroundColor Gray
        return
    }

    Write-Host "Updating README.md with changelog..." -ForegroundColor Cyan

    # Check if README.md exists
    if (-not (Test-Path "README.md")) {
        Write-Host "README.md not found. Creating new file." -ForegroundColor Yellow
        $readmeContent = "# Erin's Seed Catalog`n`nA WordPress plugin designed to help gardeners catalog and track their vegetable garden seeds.`n`n$changelog"
        Set-Content -Path "README.md" -Value $readmeContent
        return
    }

    # Read current README.md
    $readmeContent = Get-Content "README.md" -Raw
    Write-Host "README.md content length: $(($readmeContent | Measure-Object -Character).Characters) characters" -ForegroundColor Cyan

    # IMPROVED APPROACH: Complete rewrite of the README.md handling
    # This approach will properly handle the file structure and avoid duplications

    # Step 1: Extract the header (everything before version history)
    $headerPattern = "(?s)^(.*?)(?:## Version History|## Version \d|### Version \d)"
    $headerMatch = [regex]::Match($readmeContent, $headerPattern)
    $headerContent = ""

    if ($headerMatch.Success) {
        $headerContent = $headerMatch.Groups[1].Value.Trim()
        Write-Host "Extracted header content ($(($headerContent | Measure-Object -Character).Characters) characters)" -ForegroundColor Green
    } else {
        # If no header found, use a default header
        $headerContent = "# Erin's Seed Catalog`n`nA WordPress plugin designed to help gardeners catalog and track their vegetable garden seeds."
        Write-Host "No header found, using default header" -ForegroundColor Yellow
    }

    # Step 2: Extract all version entries (both ## and ### formats)
    $versionPattern = "(?:## |### )Version [\d\.]+(?:\s*-\s*(?:\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}|[^\n]*))?[\s\S]*?(?=(?:## |### )Version|## [^V]|$)"
    $versionMatches = [regex]::Matches($readmeContent, $versionPattern)

    Write-Host "Found $($versionMatches.Count) version entries" -ForegroundColor Green

    # Step 3: Extract unique versions and their content
    $uniqueVersions = @{}
    $versionNumbers = @{}

    foreach ($match in $versionMatches) {
        $versionText = $match.Value
        $versionNumberMatch = [regex]::Match($versionText, "Version ([\d\.]+)")

        if ($versionNumberMatch.Success) {
            $versionNumber = $versionNumberMatch.Groups[1].Value

            # Store the version number and its content
            if (-not $uniqueVersions.ContainsKey($versionNumber)) {
                $uniqueVersions[$versionNumber] = $versionText.Trim()
                $versionNumbers[$versionNumber] = [version]$versionNumber
                Write-Host "Added version $versionNumber to unique versions" -ForegroundColor Green
            }
        }
    }

    # Step 4: Extract the development section (everything after version history)
    $devSectionPattern = "(?s)(?:## Features|## Development|## License|## Shortcodes)[\s\S]*$"
    $devSectionMatch = [regex]::Match($readmeContent, $devSectionPattern)
    $devSection = ""

    if ($devSectionMatch.Success) {
        $devSection = $devSectionMatch.Value.Trim()
        Write-Host "Found development section ($(($devSection | Measure-Object -Character).Characters) characters)" -ForegroundColor Green
    }

    # Step 5: Check for version bump entries that can be consolidated
    $versionBumpEntries = @{}
    $versionBumpPattern = "(?i)version bump|bump version|version bump only"

    foreach ($version in $uniqueVersions.Keys) {
        if ($uniqueVersions[$version] -match $versionBumpPattern) {
            $versionBumpEntries[$version] = $true
        }
    }

    # Step 6: Build the new README.md content
    $newReadmeContent = $headerContent + "`n`n"

    # Add Version History header if it doesn't exist
    if (-not ($headerContent -match "## Version History")) {
        $newReadmeContent += "## Version History`n`n"
    }

    # Add the new changelog
    $newReadmeContent += $changelog + "`n"

    # Consolidate version bump entries if there are consecutive ones
    $sortedVersions = $versionNumbers.Keys | Sort-Object {$versionNumbers[$_]} -Descending

    # Find consecutive version bump entries
    $consolidatedBumps = @{}
    $currentRange = @()

    for ($i = 0; $i -lt $sortedVersions.Count; $i++) {
        $version = $sortedVersions[$i]

        if ($versionBumpEntries.ContainsKey($version)) {
            $currentRange += $version
        } else {
            if ($currentRange.Count -gt 1) {
                $rangeStart = $currentRange[0]
                $rangeEnd = $currentRange[$currentRange.Count - 1]
                $consolidatedBumps["$rangeStart-$rangeEnd"] = $currentRange

                foreach ($v in $currentRange) {
                    $uniqueVersions.Remove($v)
                }
            }
            $currentRange = @()
        }
    }

    # Handle any remaining range
    if ($currentRange.Count -gt 1) {
        $rangeStart = $currentRange[0]
        $rangeEnd = $currentRange[$currentRange.Count - 1]
        $consolidatedBumps["$rangeStart-$rangeEnd"] = $currentRange

        foreach ($v in $currentRange) {
            $uniqueVersions.Remove($v)
        }
    }

    # Add consolidated version bump entries
    foreach ($range in $consolidatedBumps.Keys) {
        # No need to split the range, we can use it directly
        $newReadmeContent += "### Version $range`n- Version bump only`n`n"
    }

    # Add remaining unique version entries in descending order
    foreach ($version in $sortedVersions) {
        if ($uniqueVersions.ContainsKey($version)) {
            $newReadmeContent += $uniqueVersions[$version] + "`n`n"
        }
    }

    # Add the development section
    if ($devSection) {
        $newReadmeContent += $devSection
    }

    # Update the README.md file
    Set-Content -Path "README.md" -Value $newReadmeContent
    Write-Host "README.md updated with cleaned up version sections and new changelog" -ForegroundColor Green
}

# Update version in files
function Update-Files {
    param (
        [string]$oldVersion,
        [string]$newVersion
    )

    if ($DryRun) {
        Write-Host "DRY RUN: Would update version from $oldVersion to $newVersion in $pluginFile" -ForegroundColor Yellow
        if (Test-Path "readme.txt") {
            Write-Host "DRY RUN: Would update version in readme.txt" -ForegroundColor Yellow
        }
        return
    }

    # Update plugin file
    $content = Get-Content $pluginFile -Raw
    $content = $content -replace "Version:\s*$oldVersion", "Version:           $newVersion"
    $content = $content -replace "define\(\s*'ESC_VERSION',\s*'$oldVersion'\s*\)", "define('ESC_VERSION', '$newVersion')"
    Set-Content -Path $pluginFile -Value $content

    # Update readme.txt if it exists
    if (Test-Path "readme.txt") {
        $readmeContent = Get-Content "readme.txt" -Raw
        # Update the stable tag regardless of its current value
        $readmeContent = $readmeContent -replace "Stable tag:\s*[0-9\.]+", "Stable tag: $newVersion"

        # Also update the changelog in readme.txt
        $wpChangelog = Convert-MarkdownToWordPressChangelog -markdownChangelog $changelog -version $newVersion

        # Check if there's already a changelog section
        if ($readmeContent -match "== Changelog ==[\r\n]+") {
            # Insert the new changelog entry after the "== Changelog ==" heading
            $readmeContent = $readmeContent -replace "(== Changelog ==[\r\n]+)", "`$1$wpChangelog"
        } else {
            # If no changelog section exists, add one before the upgrade notice or at the end
            if ($readmeContent -match "== Upgrade Notice ==") {
                $readmeContent = $readmeContent -replace "(== Upgrade Notice ==)", "== Changelog ==\n\n$wpChangelog\n\n`$1"
            } else {
                # Add at the end
                $readmeContent += "\n\n== Changelog ==\n\n$wpChangelog"
            }
        }

        # Also update the upgrade notice if it exists
        if ($readmeContent -match "== Upgrade Notice ==[\r\n]+") {
            # Create a simple upgrade notice
            $upgradeNotice = "= $newVersion =\nThis update includes the latest improvements and bug fixes. See the changelog for details.\n\n"

            # Insert the new upgrade notice after the "== Upgrade Notice ==" heading
            $readmeContent = $readmeContent -replace "(== Upgrade Notice ==[\r\n]+)", "`$1$upgradeNotice"
        }

        Set-Content -Path "readme.txt" -Value $readmeContent
    }

    # Always update README.md with changelog
    # We're using the pre-generated changelog
    $shouldUpdateReadme = $true
    if ($shouldUpdateReadme) {
        # Use the pre-generated changelog
        Write-Host "Updating README.md with pre-generated changelog..." -ForegroundColor Cyan
        Update-ReadmeChangelog -newVersion $newVersion -changelog $changelog
    }
}

# Main execution
$currentVersion = Get-Version
Write-Host "Current version: $currentVersion" -ForegroundColor Cyan

$newVersion = Update-Version -version $currentVersion -type $VersionType
Write-Host "New version: $newVersion" -ForegroundColor Green

# Generate the changelog first, before updating any files
# This ensures we capture the actual changes, not just the version bump
Write-Host "Generating changelog before updating files..." -ForegroundColor Cyan
$changelog = Get-ChangesSinceLastTag -newVersion $newVersion -releaseDescription $ReleaseDescription

# Debug output to show the generated changelog
Write-Host "Generated changelog:" -ForegroundColor Cyan
Write-Host $changelog -ForegroundColor Gray

# Store a plain text version of the changelog for commit messages and release notes
$plainChangelog = $changelog

# Process the changelog line by line to convert to plain text
$lines = $plainChangelog -split "`n"
$plainTextLines = @()

foreach ($line in $lines) {
    # Skip version header
    if ($line -match "^## Version") {
        continue
    }
    # Convert section headers
    elseif ($line -match "^### (.+)") {
        $sectionName = $Matches[1]
        $plainTextLines += "${sectionName}:"
    }
    # Convert bullet points
    elseif ($line -match "^- (.+)") {
        $bulletContent = $Matches[1]
        $plainTextLines += "* $bulletContent"
    }
    # Keep other lines as is
    elseif ($line.Trim()) {
        $plainTextLines += $line
    }
}

# Join the lines back together
$plainChangelog = $plainTextLines -join "`n"

if (-not $DryRun) {
    Write-Host "Changelog preview:" -ForegroundColor Cyan
    Write-Host $plainChangelog -ForegroundColor White
    $confirmation = Read-Host "Do you want to update to version $newVersion with these changes? (y/n)"
    if ($confirmation -ne "y") {
        Write-Host "Update cancelled" -ForegroundColor Yellow
        exit 0
    }
} else {
    Write-Host "DRY RUN: Would prompt for confirmation to update to version $newVersion" -ForegroundColor Yellow
}

# Now update the files with the pre-generated changelog
Update-Files -oldVersion $currentVersion -newVersion $newVersion
Write-Host "Version updated successfully!" -ForegroundColor Green

# Create detailed commit message
function New-CommitMessage {
    param (
        [string]$version
    )

    Write-Host "Generating detailed commit message..." -ForegroundColor Cyan

    # Get list of changed files
    $changedFiles = git diff --name-only HEAD

    # Categorize changes
    $features = @()
    $fixes = @()
    $docs = @()
    $other = @()

    foreach ($file in $changedFiles) {
        $extension = [System.IO.Path]::GetExtension($file)
        $directory = [System.IO.Path]::GetDirectoryName($file)

        if ($file -match "README|readme|RELEASE|\.md$|\.txt$") {
            $docs += $file
        }
        elseif ($directory -match "admin|includes" -and $extension -match "\.php$") {
            # Try to determine if it's a feature or fix based on git diff
            $diff = git diff HEAD $file
            if ($diff -match "fix|bug|issue|error|warning|notice") {
                $fixes += $file
            } else {
                $features += $file
            }
        }
        else {
            $other += $file
        }
    }

    # Build commit message
    $message = "Release v$version`n`n"

    if ($features.Count -gt 0) {
        $message += "Features:`n"
        foreach ($feature in $features) {
            $message += "- Updated $feature`n"
        }
        $message += "`n"
    }

    if ($fixes.Count -gt 0) {
        $message += "Fixes:`n"
        foreach ($fix in $fixes) {
            $message += "- Fixed $fix`n"
        }
        $message += "`n"
    }

    if ($docs.Count -gt 0) {
        $message += "Documentation:`n"
        foreach ($doc in $docs) {
            $message += "- Updated $doc`n"
        }
        $message += "`n"
    }

    if ($other.Count -gt 0) {
        $message += "Other Changes:`n"
        foreach ($change in $other) {
            $message += "- Modified $change`n"
        }
    }

    # If no changes were categorized, add a generic message
    if ($features.Count -eq 0 -and $fixes.Count -eq 0 -and $docs.Count -eq 0 -and $other.Count -eq 0) {
        $message += "Version bump only`n"
    }

    return $message
}

# Commit changes
# Use the pre-generated changelog for the commit message instead of generating a new one
$commitMessage = "Release v$newVersion`n`n$plainChangelog"
Write-Host "Commit message:" -ForegroundColor Cyan
Write-Host $commitMessage -ForegroundColor White

if ($DryRun) {
    Write-Host "DRY RUN: Would commit changes with the above message" -ForegroundColor Yellow
    Write-Host "DRY RUN: Would create tag v$newVersion" -ForegroundColor Yellow
} else {
    git add .
    git commit -m "$commitMessage"
    git tag -a "v$newVersion" -m "Version $newVersion - $plainChangelog"
}

# Push changes
if (-not $DryRun) {
    $pushConfirmation = Read-Host "Do you want to push changes to GitHub? (y/n)"
    if ($pushConfirmation -eq "y") {
        git push origin master
        git push origin "v$newVersion"
        Write-Host "Changes pushed to GitHub" -ForegroundColor Green
    }
} else {
    Write-Host "DRY RUN: Would push changes to GitHub if confirmed" -ForegroundColor Yellow
}

# Create GitHub release
if (-not $DryRun) {
    $releaseConfirmation = Read-Host "Do you want to create a GitHub release? (y/n)"
    if ($releaseConfirmation -eq "y") {
        # Check if GitHub CLI is installed
        $ghInstalled = $null
        try {
            $ghInstalled = Get-Command gh -ErrorAction SilentlyContinue
        } catch {}

        # Prepare release title and notes
        $title = if ($ReleaseTitle) { $ReleaseTitle } else { "Version $newVersion" }
        # Use the pre-generated changelog for release notes if no custom description is provided
        $notes = if ($ReleaseDescription) { $ReleaseDescription } else { $plainChangelog }

        if ($ghInstalled) {
            # Use GitHub CLI
            Write-Host "Creating GitHub release with title: $title" -ForegroundColor Cyan
            gh release create "v$newVersion" --title "$title" --notes "$notes"
            Write-Host "GitHub release created using GitHub CLI" -ForegroundColor Green
        } else {
            # Provide instructions for manual release
            Write-Host "GitHub CLI (gh) is not installed or not in PATH." -ForegroundColor Yellow
            Write-Host "To create a release manually, go to:" -ForegroundColor Yellow
            Write-Host "https://github.com/abrianbaker80/erins-seed-catalog-G2.5/releases/new?tag=v$newVersion" -ForegroundColor Cyan
            Write-Host "Title: $title" -ForegroundColor Yellow
            Write-Host "Description: Use the commit message or your custom description" -ForegroundColor Yellow
        }
    }
} else {
    $title = if ($ReleaseTitle) { $ReleaseTitle } else { "Version $newVersion" }
    Write-Host "DRY RUN: Would create GitHub release for v$newVersion with title '$title' if confirmed" -ForegroundColor Yellow
    if ($ReleaseDescription) {
        Write-Host "DRY RUN: Would use custom release description" -ForegroundColor Yellow
    }
}

Write-Host "Release process completed!" -ForegroundColor Green

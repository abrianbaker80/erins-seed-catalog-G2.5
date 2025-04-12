# Simple Release Script for Erin's Seed Catalog
#
# Usage:
#   ./simple-release.ps1                  - Increment patch version (0.0.x)
#   ./simple-release.ps1 -Major           - Increment major version (x.0.0)
#   ./simple-release.ps1 -Minor           - Increment minor version (0.x.0)
#   ./simple-release.ps1 -DryRun          - Show what would happen without making changes
#   ./simple-release.ps1 -ReleaseTitle "Title" -ReleaseDescription "Description" - Custom release info
#   ./simple-release.ps1 -UpdateReadme    - Update README.md with changelog
#   ./simple-release.ps1 -SkipReadmeUpdate - Don't update README.md (default behavior)

param (
    [string]$VersionType = "patch",
    [switch]$DryRun = $false,
    [string]$ReleaseTitle = "",
    [string]$ReleaseDescription = "",
    [switch]$Major = $false,
    [switch]$Minor = $false,
    [switch]$UpdateReadme = $false,
    [switch]$SkipReadmeUpdate = $false
)

# Handle version type from switches
if ($Major) {
    $VersionType = "major"
}
elseif ($Minor) {
    $VersionType = "minor"
}

# Determine if README should be updated
$shouldUpdateReadme = $UpdateReadme -or (-not $SkipReadmeUpdate)

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

# Get changes since last tag
function Get-ChangesSinceLastTag {
    param (
        [string]$newVersion
    )

    Write-Host "Getting changes since last tag..." -ForegroundColor Cyan

    # Get the last tag
    $lastTag = git describe --tags --abbrev=0 2>$null

    if (-not $lastTag) {
        Write-Host "No previous tags found. Using first commit." -ForegroundColor Yellow
        $lastTag = git rev-list --max-parents=0 HEAD
    }

    # Get commits since last tag
    $commits = git log "$lastTag..HEAD" --pretty=format:"%s" --no-merges

    # Categorize commits
    $features = @()
    $fixes = @()
    $docs = @()
    $other = @()

    foreach ($commit in $commits) {
        if ($commit -match "^feat|^feature|^add|^new|^implement") {
            $features += $commit
        }
        elseif ($commit -match "^fix|^bug|^issue|^error|^warning|^notice") {
            $fixes += $commit
        }
        elseif ($commit -match "^doc|^docs|^readme|^documentation") {
            $docs += $commit
        }
        else {
            $other += $commit
        }
    }

    # Build changelog
    $changelog = "## Version $newVersion`n`n"

    if ($features.Count -gt 0) {
        $changelog += "### New Features`n"
        foreach ($feature in $features) {
            # Clean up commit message
            $feature = $feature -replace "^feat\(.*\):\s*", "" -replace "^feature:\s*", ""
            $feature = $feature.Substring(0, 1).ToUpper() + $feature.Substring(1)
            $changelog += "- $feature`n"
        }
        $changelog += "`n"
    }

    if ($fixes.Count -gt 0) {
        $changelog += "### Bug Fixes`n"
        foreach ($fix in $fixes) {
            # Clean up commit message
            $fix = $fix -replace "^fix\(.*\):\s*", "" -replace "^bug:\s*", ""
            $fix = $fix.Substring(0, 1).ToUpper() + $fix.Substring(1)
            $changelog += "- $fix`n"
        }
        $changelog += "`n"
    }

    if ($docs.Count -gt 0) {
        $changelog += "### Documentation`n"
        foreach ($doc in $docs) {
            # Clean up commit message
            $doc = $doc -replace "^docs\(.*\):\s*", "" -replace "^doc:\s*", ""
            $doc = $doc.Substring(0, 1).ToUpper() + $doc.Substring(1)
            $changelog += "- $doc`n"
        }
        $changelog += "`n"
    }

    if ($other.Count -gt 0) {
        $changelog += "### Other Changes`n"
        foreach ($change in $other) {
            # Clean up commit message
            $change = $change.Substring(0, 1).ToUpper() + $change.Substring(1)
            $changelog += "- $change`n"
        }
        $changelog += "`n"
    }

    # If no changes were categorized, add a generic message
    if ($features.Count -eq 0 -and $fixes.Count -eq 0 -and $docs.Count -eq 0 -and $other.Count -eq 0) {
        $changelog += "### Changes`n"
        $changelog += "- Version bump to $newVersion`n`n"
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

    # Check if there's already a version section
    if ($readmeContent -match "## Version \d+\.\d+\.\d+") {
        # Insert the new changelog before the first version section
        $readmeContent = $readmeContent -replace "(## Version \d+\.\d+\.\d+)", "$changelog`$1"
    } else {
        # Find the first ## heading and insert before it
        if ($readmeContent -match "## [^#]") {
            $readmeContent = $readmeContent -replace "(## [^#])", "$changelog`$1"
        } else {
            # Append to the end if no ## heading found
            $readmeContent += "`n`n$changelog"
        }
    }

    # Write updated content back to README.md
    Set-Content -Path "README.md" -Value $readmeContent
    Write-Host "README.md updated with changelog for version $newVersion" -ForegroundColor Green
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
        $readmeContent = $readmeContent -replace "Stable tag:\s*$oldVersion", "Stable tag: $newVersion"
        Set-Content -Path "readme.txt" -Value $readmeContent
    }

    # Update README.md if requested
    if ($shouldUpdateReadme) {
        $changelog = Get-ChangesSinceLastTag -newVersion $newVersion
        Update-ReadmeChangelog -newVersion $newVersion -changelog $changelog
    }
}

# Main execution
$currentVersion = Get-Version
Write-Host "Current version: $currentVersion" -ForegroundColor Cyan

$newVersion = Update-Version -version $currentVersion -type $VersionType
Write-Host "New version: $newVersion" -ForegroundColor Green

if (-not $DryRun) {
    $confirmation = Read-Host "Do you want to update to version $newVersion? (y/n)"
    if ($confirmation -ne "y") {
        Write-Host "Update cancelled" -ForegroundColor Yellow
        exit 0
    }
} else {
    Write-Host "DRY RUN: Would prompt for confirmation to update to version $newVersion" -ForegroundColor Yellow
}

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
$commitMessage = New-CommitMessage -version $newVersion
Write-Host "Commit message:" -ForegroundColor Cyan
Write-Host $commitMessage -ForegroundColor White

if ($DryRun) {
    Write-Host "DRY RUN: Would commit changes with the above message" -ForegroundColor Yellow
    Write-Host "DRY RUN: Would create tag v$newVersion" -ForegroundColor Yellow
} else {
    git add .
    git commit -m "$commitMessage"
    git tag -a "v$newVersion" -m "Version $newVersion"
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
        $notes = if ($ReleaseDescription) { $ReleaseDescription } else { $commitMessage }

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

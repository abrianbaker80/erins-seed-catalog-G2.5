# Simple Release Script for Erin's Seed Catalog

param (
    [string]$VersionType = "patch"
)

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

# Increment version
function Increment-Version {
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

# Update version in files
function Update-Files {
    param (
        [string]$oldVersion,
        [string]$newVersion
    )

    # Update plugin file
    $content = Get-Content $pluginFile -Raw
    $content = $content -replace "Version:\s*$oldVersion", "Version: $newVersion"
    $content = $content -replace "define\(\s*'ESC_VERSION',\s*'$oldVersion'\s*\)", "define('ESC_VERSION', '$newVersion')"
    Set-Content -Path $pluginFile -Value $content

    # Update readme.txt if it exists
    if (Test-Path "readme.txt") {
        $readmeContent = Get-Content "readme.txt" -Raw
        $readmeContent = $readmeContent -replace "Stable tag:\s*$oldVersion", "Stable tag: $newVersion"
        Set-Content -Path "readme.txt" -Value $readmeContent
    }
}

# Main execution
$currentVersion = Get-Version
Write-Host "Current version: $currentVersion" -ForegroundColor Cyan

$newVersion = Increment-Version -version $currentVersion -type $VersionType
Write-Host "New version: $newVersion" -ForegroundColor Green

$confirmation = Read-Host "Do you want to update to version $newVersion? (y/n)"
if ($confirmation -ne "y") {
    Write-Host "Update cancelled" -ForegroundColor Yellow
    exit 0
}

Update-Files -oldVersion $currentVersion -newVersion $newVersion
Write-Host "Version updated successfully!" -ForegroundColor Green

# Generate detailed commit message
function Generate-CommitMessage {
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
$commitMessage = Generate-CommitMessage -version $newVersion
Write-Host "Commit message:" -ForegroundColor Cyan
Write-Host $commitMessage -ForegroundColor White

git add .
git commit -m "$commitMessage"
git tag -a "v$newVersion" -m "Version $newVersion"

# Push changes
$pushConfirmation = Read-Host "Do you want to push changes to GitHub? (y/n)"
if ($pushConfirmation -eq "y") {
    git push origin master
    git push origin "v$newVersion"
    Write-Host "Changes pushed to GitHub" -ForegroundColor Green

    # Create GitHub release
    $releaseConfirmation = Read-Host "Do you want to create a GitHub release? (y/n)"
    if ($releaseConfirmation -eq "y") {
        # Check if GitHub CLI is installed
        $ghInstalled = $null
        try {
            $ghInstalled = Get-Command gh -ErrorAction SilentlyContinue
        } catch {}

        if ($ghInstalled) {
            # Use GitHub CLI
            gh release create "v$newVersion" --title "Version $newVersion" --notes "Release version $newVersion"
            Write-Host "GitHub release created using GitHub CLI" -ForegroundColor Green
        } else {
            # Provide instructions for manual release
            Write-Host "GitHub CLI (gh) is not installed or not in PATH." -ForegroundColor Yellow
            Write-Host "To create a release manually, go to:" -ForegroundColor Yellow
            Write-Host "https://github.com/abrianbaker80/erins-seed-catalog-G2.5/releases/new?tag=v$newVersion" -ForegroundColor Cyan
            Write-Host "Title: Version $newVersion" -ForegroundColor Yellow
            Write-Host "Description: Release version $newVersion" -ForegroundColor Yellow
        }
    }
}

Write-Host "Release process completed!" -ForegroundColor Green

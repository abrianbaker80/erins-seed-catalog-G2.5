# Erin's Seed Catalog - Release Automation Script
# This script automates the release process for the plugin
# It handles version incrementation, commit message generation, Git operations, and GitHub release creation

# Parameters
param (
    [Parameter(Mandatory=$false)]
    [ValidateSet("patch", "minor", "major")]
    [string]$VersionIncrement = "patch",
    
    [Parameter(Mandatory=$false)]
    [string]$ReleaseTitle = "",
    
    [Parameter(Mandatory=$false)]
    [string]$ReleaseDescription = "",
    
    [Parameter(Mandatory=$false)]
    [switch]$DryRun = $false
)

# Configuration
$pluginMainFile = "erins-seed-catalog.php"
$readmeFile = "readme.txt"
$githubRepo = "abrianbaker80/erins-seed-catalog-G2.5"
$githubToken = $env:GITHUB_TOKEN

# Function to check if required tools are installed
function Check-Requirements {
    Write-Host "Checking requirements..." -ForegroundColor Cyan
    
    # Check Git
    try {
        $gitVersion = git --version
        Write-Host "✓ Git is installed: $gitVersion" -ForegroundColor Green
    } catch {
        Write-Host "✗ Git is not installed or not in PATH" -ForegroundColor Red
        exit 1
    }
    
    # Check GitHub CLI
    try {
        $ghVersion = gh --version | Select-Object -First 1
        Write-Host "✓ GitHub CLI is installed: $ghVersion" -ForegroundColor Green
    } catch {
        Write-Host "✗ GitHub CLI is not installed or not in PATH" -ForegroundColor Red
        Write-Host "Please install GitHub CLI from https://cli.github.com/" -ForegroundColor Yellow
        exit 1
    }
    
    # Check GitHub authentication
    if (-not $githubToken) {
        try {
            $ghAuth = gh auth status
            Write-Host "✓ GitHub CLI is authenticated" -ForegroundColor Green
        } catch {
            Write-Host "✗ GitHub CLI is not authenticated" -ForegroundColor Red
            Write-Host "Please run 'gh auth login' to authenticate with GitHub" -ForegroundColor Yellow
            exit 1
        }
    } else {
        Write-Host "✓ GitHub token is set" -ForegroundColor Green
    }
}

# Function to get the current version from the plugin file
function Get-CurrentVersion {
    $pluginContent = Get-Content $pluginMainFile -Raw
    if ($pluginContent -match "Version:\s*(\d+\.\d+\.\d+)") {
        return $matches[1]
    } else {
        Write-Host "Could not find version in plugin file" -ForegroundColor Red
        exit 1
    }
}

# Function to increment version based on SemVer
function Increment-Version {
    param (
        [string]$currentVersion,
        [string]$increment
    )
    
    $versionParts = $currentVersion -split "\."
    $major = [int]$versionParts[0]
    $minor = [int]$versionParts[1]
    $patch = [int]$versionParts[2]
    
    switch ($increment) {
        "major" {
            $major++
            $minor = 0
            $patch = 0
        }
        "minor" {
            $minor++
            $patch = 0
        }
        "patch" {
            $patch++
        }
    }
    
    return "$major.$minor.$patch"
}

# Function to update version in files
function Update-VersionInFiles {
    param (
        [string]$oldVersion,
        [string]$newVersion
    )
    
    Write-Host "Updating version from $oldVersion to $newVersion..." -ForegroundColor Cyan
    
    if ($DryRun) {
        Write-Host "DRY RUN: Would update version in $pluginMainFile" -ForegroundColor Yellow
        Write-Host "DRY RUN: Would update version in $readmeFile" -ForegroundColor Yellow
        return
    }
    
    # Update plugin main file
    $pluginContent = Get-Content $pluginMainFile -Raw
    $pluginContent = $pluginContent -replace "Version:\s*$oldVersion", "Version: $newVersion"
    $pluginContent = $pluginContent -replace "define\(\s*'ESC_VERSION',\s*'$oldVersion'\s*\)", "define('ESC_VERSION', '$newVersion')"
    Set-Content -Path $pluginMainFile -Value $pluginContent
    
    # Update readme.txt if it exists
    if (Test-Path $readmeFile) {
        $readmeContent = Get-Content $readmeFile -Raw
        $readmeContent = $readmeContent -replace "Stable tag:\s*$oldVersion", "Stable tag: $newVersion"
        Set-Content -Path $readmeFile -Value $readmeContent
    }
    
    Write-Host "✓ Version updated in files" -ForegroundColor Green
}

# Function to generate commit message based on changes
function Generate-CommitMessage {
    param (
        [string]$newVersion
    )
    
    Write-Host "Generating commit message..." -ForegroundColor Cyan
    
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
        
        if ($file -match "README|readme|\.md$|\.txt$") {
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
    $message = "Release v$newVersion`n`n"
    
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
    
    return $message
}

# Function to commit and push changes
function Commit-AndPush {
    param (
        [string]$commitMessage,
        [string]$version
    )
    
    Write-Host "Committing and pushing changes..." -ForegroundColor Cyan
    
    if ($DryRun) {
        Write-Host "DRY RUN: Would commit with message:" -ForegroundColor Yellow
        Write-Host $commitMessage -ForegroundColor Yellow
        Write-Host "DRY RUN: Would create tag v$version" -ForegroundColor Yellow
        Write-Host "DRY RUN: Would push to origin" -ForegroundColor Yellow
        return
    }
    
    # Stage changes
    git add .
    
    # Commit
    git commit -m "$commitMessage"
    
    # Create tag
    git tag -a "v$version" -m "Version $version"
    
    # Push to origin
    git push origin master
    git push origin "v$version"
    
    Write-Host "✓ Changes committed and pushed" -ForegroundColor Green
}

# Function to create GitHub release
function Create-GitHubRelease {
    param (
        [string]$version,
        [string]$releaseNotes,
        [string]$releaseTitle
    )
    
    Write-Host "Creating GitHub release..." -ForegroundColor Cyan
    
    if ($DryRun) {
        Write-Host "DRY RUN: Would create GitHub release v$version" -ForegroundColor Yellow
        return
    }
    
    $title = if ($releaseTitle) { $releaseTitle } else { "Version $version" }
    
    if ($githubToken) {
        $env:GH_TOKEN = $githubToken
    }
    
    # Create release using GitHub CLI
    $releaseCommand = "gh release create v$version --title `"$title`" --notes `"$releaseNotes`""
    
    try {
        Invoke-Expression $releaseCommand
        Write-Host "✓ GitHub release created" -ForegroundColor Green
    } catch {
        Write-Host "✗ Failed to create GitHub release: $($_.Exception.Message)" -ForegroundColor Red
        exit 1
    }
}

# Main execution
try {
    # Check requirements
    Check-Requirements
    
    # Get current version
    $currentVersion = Get-CurrentVersion
    Write-Host "Current version: $currentVersion" -ForegroundColor Cyan
    
    # Increment version
    $newVersion = Increment-Version -currentVersion $currentVersion -increment $VersionIncrement
    Write-Host "New version: $newVersion" -ForegroundColor Cyan
    
    # Update version in files
    Update-VersionInFiles -oldVersion $currentVersion -newVersion $newVersion
    
    # Generate commit message
    $commitMessage = Generate-CommitMessage -newVersion $newVersion
    Write-Host "Commit message:" -ForegroundColor Cyan
    Write-Host $commitMessage -ForegroundColor White
    
    # Confirm with user
    if (-not $DryRun) {
        $confirmation = Read-Host "Do you want to proceed with the release? (y/n)"
        if ($confirmation -ne "y") {
            Write-Host "Release cancelled" -ForegroundColor Yellow
            exit 0
        }
    }
    
    # Commit and push
    Commit-AndPush -commitMessage $commitMessage -version $newVersion
    
    # Create GitHub release
    $releaseNotes = if ($ReleaseDescription) { $ReleaseDescription } else { $commitMessage }
    Create-GitHubRelease -version $newVersion -releaseNotes $releaseNotes -releaseTitle $ReleaseTitle
    
    Write-Host "Release v$newVersion completed successfully!" -ForegroundColor Green
} catch {
    Write-Host "An error occurred: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

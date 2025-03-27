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

# Commit changes
$commitMessage = "Release v$newVersion"
git add .
git commit -m $commitMessage
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
        gh release create "v$newVersion" --title "Version $newVersion" --notes "Release version $newVersion"
        Write-Host "GitHub release created" -ForegroundColor Green
    }
}

Write-Host "Release process completed!" -ForegroundColor Green

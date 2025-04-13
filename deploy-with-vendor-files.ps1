# Deploy with Vendor Files
# This script ensures that the necessary vendor files are included in the plugin package

# Define paths
$pluginDir = Get-Location
$pluginUpdateCheckerDir = Join-Path $pluginDir "plugin-update-checker"
$vendorDir = Join-Path $pluginUpdateCheckerDir "vendor"

# Create vendor directory if it doesn't exist
if (-not (Test-Path $vendorDir)) {
    Write-Host "Creating vendor directory: $vendorDir" -ForegroundColor Cyan
    New-Item -Path $vendorDir -ItemType Directory -Force | Out-Null
}

# Define the vendor files to include
$vendorFiles = @(
    "PucReadmeParser.php",
    "Parsedown.php",
    "ParsedownLegacy.php",
    "ParsedownModern.php"
)

# Check if vendor files exist in includes/vendor
$includesVendorDir = Join-Path $pluginDir "includes/vendor"
$sourceDir = $null

if (Test-Path $includesVendorDir) {
    $sourceDir = $includesVendorDir
    Write-Host "Found vendor files in includes/vendor directory" -ForegroundColor Green
} else {
    # Try to find the files in the plugin-update-checker directory
    $sourceDir = Join-Path $pluginDir "plugin-update-checker"
    Write-Host "Looking for vendor files in plugin-update-checker directory" -ForegroundColor Yellow
}

# Copy vendor files
foreach ($file in $vendorFiles) {
    $sourcePath = Join-Path $sourceDir $file
    $destPath = Join-Path $vendorDir $file

    if (Test-Path $sourcePath) {
        Write-Host "Copying $file to vendor directory" -ForegroundColor Green
        Copy-Item -Path $sourcePath -Destination $destPath -Force
    } else {
        # Try to download the file from the GitHub repository
        $url = "https://raw.githubusercontent.com/YahnisElsts/plugin-update-checker/master/vendor/$file"
        Write-Host "Downloading $file from GitHub" -ForegroundColor Yellow
        try {
            Invoke-WebRequest -Uri $url -OutFile $destPath
            Write-Host "Downloaded $file successfully" -ForegroundColor Green
        } catch {
            Write-Host "Failed to download $file" -ForegroundColor Red
        }
    }
}

# Verify that the files exist in the vendor directory
$missingFiles = @()
foreach ($file in $vendorFiles) {
    $filePath = Join-Path $vendorDir $file
    if (-not (Test-Path $filePath)) {
        $missingFiles += $file
    }
}

if ($missingFiles.Count -gt 0) {
    Write-Host "Warning: The following vendor files are still missing:" -ForegroundColor Red
    foreach ($file in $missingFiles) {
        Write-Host "  - $file" -ForegroundColor Red
    }
    Write-Host "The plugin update checker may not work correctly without these files." -ForegroundColor Red
} else {
    Write-Host "All vendor files are in place. The plugin update checker should work correctly." -ForegroundColor Green
}

# Now run the regular deployment script if it exists
$deployScript = Join-Path $pluginDir "deploy-to-wordpress.ps1"
if (Test-Path $deployScript) {
    Write-Host "Running deploy-to-wordpress.ps1..." -ForegroundColor Cyan
    & $deployScript
} else {
    Write-Host "No deploy-to-wordpress.ps1 script found. You'll need to deploy the plugin manually." -ForegroundColor Yellow
}

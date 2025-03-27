@echo off
REM Simple Release Script for Erin's Seed Catalog

echo Erin's Seed Catalog - Simple Release
echo ===================================
echo.

if "%1"=="--major" (
    powershell -ExecutionPolicy Bypass -File "%~dp0simple-release.ps1" -VersionType major
) else if "%1"=="--minor" (
    powershell -ExecutionPolicy Bypass -File "%~dp0simple-release.ps1" -VersionType minor
) else (
    powershell -ExecutionPolicy Bypass -File "%~dp0simple-release.ps1" -VersionType patch
)

if %ERRORLEVEL% neq 0 (
    echo.
    echo Release process failed with error code %ERRORLEVEL%
    exit /b %ERRORLEVEL%
)

echo.
echo Release process completed successfully!
exit /b 0

@echo off
REM Erin's Seed Catalog - Release Automation Batch Wrapper
REM This batch file provides an easy way to run the release.ps1 PowerShell script

echo Erin's Seed Catalog - Release Automation
echo =======================================
echo.

REM Check if PowerShell is available
where powershell >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo Error: PowerShell is not installed or not in PATH
    exit /b 1
)

REM Parse command line arguments
set VERSION_INCREMENT=patch
set RELEASE_TITLE=
set RELEASE_DESCRIPTION=
set DRY_RUN=

:parse_args
if "%~1"=="" goto run_script
if /i "%~1"=="--major" set VERSION_INCREMENT=major& goto next_arg
if /i "%~1"=="--minor" set VERSION_INCREMENT=minor& goto next_arg
if /i "%~1"=="--patch" set VERSION_INCREMENT=patch& goto next_arg
if /i "%~1"=="--title" set RELEASE_TITLE=%~2& shift& goto next_arg
if /i "%~1"=="--description" set RELEASE_DESCRIPTION=%~2& shift& goto next_arg
if /i "%~1"=="--dry-run" set DRY_RUN=-DryRun& goto next_arg
if /i "%~1"=="--help" goto show_help

echo Unknown argument: %~1
goto show_help

:next_arg
shift
goto parse_args

:show_help
echo Usage: release.bat [options]
echo.
echo Options:
echo   --major             Increment major version (x.0.0)
echo   --minor             Increment minor version (0.x.0)
echo   --patch             Increment patch version (0.0.x) [default]
echo   --title "TITLE"     Set custom release title
echo   --description "DESC" Set custom release description
echo   --dry-run           Run without making actual changes
echo   --help              Show this help message
echo.
exit /b 0

:run_script
echo Running release script with version increment: %VERSION_INCREMENT%
if defined DRY_RUN echo DRY RUN MODE: No actual changes will be made

REM Run the PowerShell script with the parsed arguments
powershell -ExecutionPolicy Bypass -File "%~dp0release.ps1" -VersionIncrement %VERSION_INCREMENT% -ReleaseTitle "%RELEASE_TITLE%" -ReleaseDescription "%RELEASE_DESCRIPTION%" %DRY_RUN%

if %ERRORLEVEL% neq 0 (
    echo.
    echo Release process failed with error code %ERRORLEVEL%
    exit /b %ERRORLEVEL%
)

echo.
echo Release process completed successfully!
exit /b 0

@echo off
REM Simple Release Script for Erin's Seed Catalog

echo Erin's Seed Catalog - Simple Release
echo ===================================
echo.

REM Parse command line arguments
set VERSION_TYPE=patch
set DRY_RUN=
set RELEASE_TITLE=
set RELEASE_DESCRIPTION=

:parse_args
if "%~1"=="" goto run_script
if /i "%~1"=="--major" set VERSION_TYPE=major& goto next_arg
if /i "%~1"=="--minor" set VERSION_TYPE=minor& goto next_arg
if /i "%~1"=="--patch" set VERSION_TYPE=patch& goto next_arg
if /i "%~1"=="--dry-run" set DRY_RUN=-DryRun& goto next_arg
if /i "%~1"=="--title" set RELEASE_TITLE=%~2& shift& goto next_arg
if /i "%~1"=="--description" set RELEASE_DESCRIPTION=%~2& shift& goto next_arg
if /i "%~1"=="--help" goto show_help

echo Unknown argument: %~1
goto show_help

:next_arg
shift
goto parse_args

:show_help
echo Usage: simple-release.bat [options]
echo.
echo Options:
echo   --major             Increment major version (x.0.0)
echo   --minor             Increment minor version (0.x.0)
echo   --patch             Increment patch version (0.0.x) [default]
echo   --dry-run           Run without making actual changes
echo   --title "TITLE"     Set custom release title
echo   --description "DESC" Set custom release description
echo   --help              Show this help message
echo.
exit /b 0

:run_script
echo Running release script with version increment: %VERSION_TYPE%
if defined DRY_RUN echo DRY RUN MODE: No actual changes will be made
if defined RELEASE_TITLE echo Custom release title: %RELEASE_TITLE%
if defined RELEASE_DESCRIPTION echo Custom release description provided

powershell -ExecutionPolicy Bypass -File "%~dp0simple-release.ps1" -VersionType %VERSION_TYPE% %DRY_RUN% -ReleaseTitle "%RELEASE_TITLE%" -ReleaseDescription "%RELEASE_DESCRIPTION%"

if %ERRORLEVEL% neq 0 (
    echo.
    echo Release process failed with error code %ERRORLEVEL%
    exit /b %ERRORLEVEL%
)

echo.
echo Release process completed successfully!
exit /b 0

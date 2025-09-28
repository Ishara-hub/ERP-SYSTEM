@echo off
REM ERP System Deployment Script for Windows
REM Usage: deploy.bat [environment]
REM Example: deploy.bat production

setlocal enabledelayedexpansion

REM Configuration
set APP_NAME=ERP System
set ENVIRONMENT=%1
if "%ENVIRONMENT%"=="" set ENVIRONMENT=production

echo.
echo 🚀 Starting deployment of %APP_NAME% to %ENVIRONMENT%
echo ==================================================

REM Check if we're in the right directory
if not exist "artisan" (
    echo ❌ Please run this script from the Laravel project root directory.
    pause
    exit /b 1
)

REM Create backup directory
if not exist "backups" mkdir backups

REM Create backup
echo ✅ Creating backup...
set BACKUP_FILE=backups\backup_%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2%.zip
powershell -command "Compress-Archive -Path . -DestinationPath %BACKUP_FILE% -Exclude node_modules,vendor,.git"
echo ✅ Backup created: %BACKUP_FILE%

REM Install PHP dependencies
echo ✅ Installing PHP dependencies...
composer install --optimize-autoloader --no-dev --no-interaction

REM Install Node.js dependencies if package.json exists
if exist "package.json" (
    echo ✅ Installing Node.js dependencies...
    npm install --production --silent
    npm run build --silent
)

REM Clear and cache configuration
echo ✅ Clearing and caching configuration...
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

REM Run migrations
echo ✅ Running database migrations...
php artisan migrate --force

REM Cache configuration for production
if "%ENVIRONMENT%"=="production" (
    echo ✅ Caching configuration for production...
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
)

REM Run health check
echo ✅ Running health check...
php artisan serve --port=8000 > nul 2>&1 &
timeout /t 3 > nul
curl -f -s http://localhost:8000/api/test > nul
if %errorlevel% equ 0 (
    echo ✅ Health check passed!
) else (
    echo ⚠️  Health check failed. Please check the application logs.
)

REM Display deployment summary
echo.
echo ==================================================
echo 🎉 Deployment completed successfully!
echo ==================================================
echo Environment: %ENVIRONMENT%
echo Backup Location: %BACKUP_FILE%
echo Timestamp: %date% %time%
echo.

echo Useful commands:
echo • Start server: php artisan serve
echo • View logs: type storage\logs\laravel.log
echo • Run maintenance: php artisan cache:clear
echo • Create backup: powershell -command "Compress-Archive -Path . -DestinationPath backup.zip"
echo.

echo ✅ Deployment script completed!
pause







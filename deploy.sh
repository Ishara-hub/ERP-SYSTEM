#!/bin/bash

# ERP System Deployment Script
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="ERP System"
APP_DIR="/var/www/erp-system"
BACKUP_DIR="/var/backups/erp-system"
ENVIRONMENT=${1:-production}

echo -e "${BLUE}🚀 Starting deployment of $APP_NAME to $ENVIRONMENT${NC}"
echo "=================================================="

# Function to print colored output
print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    print_error "Please do not run this script as root. Use a regular user with sudo privileges."
    exit 1
fi

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "Please run this script from the Laravel project root directory."
    exit 1
fi

# Create backup before deployment
print_status "Creating backup..."
mkdir -p $BACKUP_DIR
BACKUP_FILE="$BACKUP_DIR/backup_$(date +%Y%m%d_%H%M%S).tar.gz"
tar -czf $BACKUP_FILE . --exclude=node_modules --exclude=vendor --exclude=.git
print_status "Backup created: $BACKUP_FILE"

# Install/Update dependencies
print_status "Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

# Install Node.js dependencies if package.json exists
if [ -f "package.json" ]; then
    print_status "Installing Node.js dependencies..."
    npm install --production --silent
    npm run build --silent
fi

# Set proper permissions
print_status "Setting permissions..."
sudo chown -R www-data:www-data $APP_DIR
sudo chmod -R 755 $APP_DIR
sudo chmod -R 775 $APP_DIR/storage
sudo chmod -R 775 $APP_DIR/bootstrap/cache

# Clear and cache configuration
print_status "Clearing and caching configuration..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Run migrations
print_status "Running database migrations..."
php artisan migrate --force

# Cache configuration for production
if [ "$ENVIRONMENT" = "production" ]; then
    print_status "Caching configuration for production..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Restart services
print_status "Restarting services..."
sudo systemctl reload nginx
sudo systemctl reload php8.1-fpm

# Run health check
print_status "Running health check..."
if curl -f -s http://localhost/api/test > /dev/null; then
    print_status "Health check passed!"
else
    print_warning "Health check failed. Please check the application logs."
fi

# Display deployment summary
echo ""
echo "=================================================="
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo "=================================================="
echo "Environment: $ENVIRONMENT"
echo "Application Directory: $APP_DIR"
echo "Backup Location: $BACKUP_FILE"
echo "Timestamp: $(date)"
echo ""

# Display useful commands
echo -e "${BLUE}Useful commands:${NC}"
echo "• Check application status: sudo systemctl status nginx php8.1-fpm"
echo "• View application logs: tail -f $APP_DIR/storage/logs/laravel.log"
echo "• View Nginx logs: sudo tail -f /var/log/nginx/error.log"
echo "• Run maintenance: $APP_DIR/maintenance.sh"
echo "• Create backup: $APP_DIR/backup.sh"
echo ""

print_status "Deployment script completed!"







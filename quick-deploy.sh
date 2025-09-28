#!/bin/bash

# Quick ERP System Deployment Script
# Usage: ./quick-deploy.sh [server-ip] [server-user]

SERVER_IP=${1:-64.23.150.233}
SERVER_USER=${2:-root}
APP_NAME="ERP-SYSTEM"

echo "🚀 Deploying ERP System to $SERVER_IP"
echo "====================================="

# Create deployment package
echo "📦 Creating deployment package..."
tar -czf erp-deployment.tar.gz \
    --exclude=node_modules \
    --exclude=vendor \
    --exclude=.git \
    --exclude=storage/logs \
    --exclude=storage/framework/cache \
    --exclude=storage/framework/sessions \
    --exclude=storage/framework/views \
    .

# Upload to server
echo "📤 Uploading to server..."
scp erp-deployment.tar.gz $SERVER_USER@$SERVER_IP:/tmp/

# Deploy on server
echo "🔧 Deploying on server..."
ssh $SERVER_USER@$SERVER_IP << 'EOF'
    # Create application directory
    mkdir -p /var/www/$APP_NAME
    cd /var/www/$APP_NAME
    
    # Extract files
    tar -xzf /tmp/erp-deployment.tar.gz
    
    # Set permissions
    chown -R www-data:www-data /var/www/$APP_NAME
    chmod -R 755 /var/www/$APP_NAME
    chmod -R 775 /var/www/$APP_NAME/storage
    chmod -R 775 /var/www/$APP_NAME/bootstrap/cache
    
    # Install dependencies
    composer install --optimize-autoloader --no-dev --no-interaction
    
    # Run migrations
    php artisan migrate --force
    
    # Cache configuration
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Restart web server
    systemctl reload nginx
    systemctl reload apache2
    
    echo "✅ Deployment completed!"
EOF

# Clean up
rm erp-deployment.tar.gz

echo "🎉 Deployment finished!"
echo "Your ERP system should now be available at:"
echo "http://$SERVER_IP/$APP_NAME"
echo "http://$SERVER_IP/$APP_NAME/api"







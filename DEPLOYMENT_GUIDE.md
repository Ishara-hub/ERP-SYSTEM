# 🚀 ERP System Deployment Guide

## Table of Contents
1. [Server Requirements](#server-requirements)
2. [Server Setup](#server-setup)
3. [Domain & SSL Configuration](#domain--ssl-configuration)
4. [Database Setup](#database-setup)
5. [Application Deployment](#application-deployment)
6. [Environment Configuration](#environment-configuration)
7. [Security Configuration](#security-configuration)
8. [Monitoring & Maintenance](#monitoring--maintenance)
9. [Backup Strategy](#backup-strategy)
10. [Troubleshooting](#troubleshooting)

---

## 🖥️ Server Requirements

### Minimum Requirements
- **CPU**: 2 cores
- **RAM**: 4GB
- **Storage**: 20GB SSD
- **OS**: Ubuntu 20.04 LTS or CentOS 8+

### Recommended Requirements
- **CPU**: 4+ cores
- **RAM**: 8GB+
- **Storage**: 50GB+ SSD
- **OS**: Ubuntu 22.04 LTS

### Software Requirements
- PHP 8.1+
- MySQL 8.0+ or PostgreSQL 13+
- Nginx or Apache
- Composer
- Node.js 18+ (for build tools)
- Git
- SSL Certificate

---

## 🛠️ Server Setup

### 1. Initial Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install essential packages
sudo apt install -y curl wget git unzip software-properties-common

# Add user for deployment
sudo adduser erpuser
sudo usermod -aG sudo erpuser
```

### 2. Install PHP 8.1+

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and extensions
sudo apt install -y php8.1-fpm php8.1-cli php8.1-mysql php8.1-xml php8.1-mbstring \
    php8.1-curl php8.1-zip php8.1-bcmath php8.1-gd php8.1-intl php8.1-redis

# Verify installation
php -v
```

### 3. Install Composer

```bash
# Download and install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Verify installation
composer --version
```

### 4. Install MySQL

```bash
# Install MySQL
sudo apt install -y mysql-server

# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
-- In MySQL console
CREATE DATABASE erp_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'erpuser'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON erp_system.* TO 'erpuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Install Nginx

```bash
# Install Nginx
sudo apt install -y nginx

# Start and enable Nginx
sudo systemctl start nginx
sudo systemctl enable nginx

# Check status
sudo systemctl status nginx
```

---

## 🌐 Domain & SSL Configuration

### 1. Domain Setup

```bash
# Point your domain to server IP
# A record: yourdomain.com -> SERVER_IP
# A record: www.yourdomain.com -> SERVER_IP
```

### 2. Install Certbot for SSL

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test auto-renewal
sudo certbot renew --dry-run
```

### 3. Nginx Configuration

Create `/etc/nginx/sites-available/erp-system`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/erp-system/public;
    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;

    # Handle Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Handle PHP files
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~ /(storage|bootstrap/cache) {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Enable the site:

```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/erp-system /etc/nginx/sites-enabled/

# Remove default site
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## 🗄️ Database Setup

### 1. Database Configuration

```bash
# Create database backup directory
sudo mkdir -p /var/backups/mysql
sudo chown mysql:mysql /var/backups/mysql
```

### 2. Database Optimization

Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:

```ini
[mysqld]
# Performance settings
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Connection settings
max_connections = 200
max_connect_errors = 1000

# Query cache
query_cache_type = 1
query_cache_size = 64M
query_cache_limit = 2M

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

Restart MySQL:

```bash
sudo systemctl restart mysql
```

---

## 📦 Application Deployment

### 1. Clone Repository

```bash
# Switch to deployment user
sudo su - erpuser

# Create application directory
sudo mkdir -p /var/www/erp-system
sudo chown erpuser:erpuser /var/www/erp-system

# Clone repository
cd /var/www/erp-system
git clone https://github.com/yourusername/erp-system.git .

# Or upload your code via SCP/SFTP
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js dependencies (if needed)
npm install --production
npm run build
```

### 3. Set Permissions

```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/erp-system
sudo chmod -R 755 /var/www/erp-system
sudo chmod -R 775 /var/www/erp-system/storage
sudo chmod -R 775 /var/www/erp-system/bootstrap/cache
```

### 4. Run Migrations

```bash
# Run database migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed --force

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## ⚙️ Environment Configuration

### 1. Environment File

Create `.env` file:

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit `.env`:

```env
APP_NAME="ERP System"
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp_system
DB_USERNAME=erpuser
DB_PASSWORD=strong_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

### 2. Install Redis (Optional but Recommended)

```bash
# Install Redis
sudo apt install -y redis-server

# Configure Redis
sudo nano /etc/redis/redis.conf
```

Edit Redis configuration:

```ini
# Set memory limit
maxmemory 256mb
maxmemory-policy allkeys-lru

# Enable persistence
save 900 1
save 300 10
save 60 10000
```

Start Redis:

```bash
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

---

## 🔒 Security Configuration

### 1. Firewall Setup

```bash
# Install UFW
sudo apt install -y ufw

# Configure firewall
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 2. Fail2Ban Setup

```bash
# Install Fail2Ban
sudo apt install -y fail2ban

# Configure Fail2Ban
sudo nano /etc/fail2ban/jail.local
```

Add configuration:

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3

[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log

[nginx-http-auth]
enabled = true
port = http,https
logpath = /var/log/nginx/error.log

[nginx-limit-req]
enabled = true
port = http,https
logpath = /var/log/nginx/error.log
maxretry = 10
```

Start Fail2Ban:

```bash
sudo systemctl start fail2ban
sudo systemctl enable fail2ban
```

### 3. PHP Security

Edit `/etc/php/8.1/fpm/php.ini`:

```ini
# Security settings
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

# File upload limits
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 30
max_input_time = 30
memory_limit = 256M

# Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

Restart PHP-FPM:

```bash
sudo systemctl restart php8.1-fpm
```

---

## 📊 Monitoring & Maintenance

### 1. Install Monitoring Tools

```bash
# Install htop for process monitoring
sudo apt install -y htop iotop nethogs

# Install log monitoring
sudo apt install -y logwatch
```

### 2. Set Up Log Rotation

Create `/etc/logrotate.d/erp-system`:

```
/var/www/erp-system/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        /usr/bin/systemctl reload php8.1-fpm > /dev/null 2>&1 || true
    endscript
}
```

### 3. Create Maintenance Script

Create `/var/www/erp-system/maintenance.sh`:

```bash
#!/bin/bash

# ERP System Maintenance Script
echo "Starting ERP System maintenance..."

# Clear Laravel caches
cd /var/www/erp-system
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear old logs
find /var/www/erp-system/storage/logs -name "*.log" -mtime +7 -delete

# Update Composer dependencies
composer install --optimize-autoloader --no-dev

# Run database maintenance
php artisan migrate --force

echo "Maintenance completed successfully!"
```

Make it executable:

```bash
chmod +x /var/www/erp-system/maintenance.sh
```

### 4. Set Up Cron Jobs

```bash
# Edit crontab
crontab -e
```

Add these entries:

```cron
# Laravel scheduler
* * * * * cd /var/www/erp-system && php artisan schedule:run >> /dev/null 2>&1

# Daily maintenance
0 2 * * * /var/www/erp-system/maintenance.sh >> /var/log/erp-maintenance.log 2>&1

# Database backup
0 3 * * * /var/www/erp-system/backup.sh >> /var/log/erp-backup.log 2>&1
```

---

## 💾 Backup Strategy

### 1. Database Backup Script

Create `/var/www/erp-system/backup.sh`:

```bash
#!/bin/bash

# Database backup script
BACKUP_DIR="/var/backups/erp-system"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="erp_system"
DB_USER="erpuser"
DB_PASS="strong_password_here"

# Create backup directory
mkdir -p $BACKUP_DIR

# Create database backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/database_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/database_$DATE.sql

# Keep only last 7 days of backups
find $BACKUP_DIR -name "database_*.sql.gz" -mtime +7 -delete

# Backup application files
tar -czf $BACKUP_DIR/application_$DATE.tar.gz /var/www/erp-system --exclude=node_modules --exclude=vendor

echo "Backup completed: $DATE"
```

Make it executable:

```bash
chmod +x /var/www/erp-system/backup.sh
```

### 2. Automated Backups

```bash
# Add to crontab for daily backups
0 3 * * * /var/www/erp-system/backup.sh
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. Permission Errors
```bash
# Fix permissions
sudo chown -R www-data:www-data /var/www/erp-system
sudo chmod -R 755 /var/www/erp-system
sudo chmod -R 775 /var/www/erp-system/storage
sudo chmod -R 775 /var/www/erp-system/bootstrap/cache
```

#### 2. Database Connection Issues
```bash
# Check MySQL status
sudo systemctl status mysql

# Check database connection
mysql -u erpuser -p erp_system
```

#### 3. PHP-FPM Issues
```bash
# Check PHP-FPM status
sudo systemctl status php8.1-fpm

# Check PHP-FPM logs
sudo tail -f /var/log/php8.1-fpm.log
```

#### 4. Nginx Issues
```bash
# Check Nginx configuration
sudo nginx -t

# Check Nginx logs
sudo tail -f /var/log/nginx/error.log
```

### Performance Optimization

#### 1. Enable OPcache
Edit `/etc/php/8.1/fpm/conf.d/10-opcache.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

#### 2. Database Optimization
```sql
-- Optimize tables
OPTIMIZE TABLE users, customers, suppliers, invoices, items, payments;

-- Check table status
SHOW TABLE STATUS;
```

---

## 🚀 Deployment Checklist

- [ ] Server setup completed
- [ ] Domain configured and SSL installed
- [ ] Database created and configured
- [ ] Application deployed
- [ ] Environment variables configured
- [ ] Permissions set correctly
- [ ] Security measures implemented
- [ ] Monitoring tools installed
- [ ] Backup strategy implemented
- [ ] Cron jobs configured
- [ ] Performance optimization applied
- [ ] Testing completed

---

## 📞 Support

For additional support:
- Check Laravel documentation: https://laravel.com/docs
- Check Nginx documentation: https://nginx.org/en/docs/
- Check MySQL documentation: https://dev.mysql.com/doc/

---

**Note**: This guide assumes you have root access to your server. Always test in a staging environment before deploying to production.







# Production Deployment Guide

Complete guide for deploying the Hotel Meeting Room Booking System to production.

## Table of Contents

1. [Server Requirements](#server-requirements)
2. [Server Setup](#server-setup)
3. [Application Deployment](#application-deployment)
4. [Web Server Configuration](#web-server-configuration)
5. [Queue Workers Setup](#queue-workers-setup)
6. [SSL/HTTPS Configuration](#sslhttps-configuration)
7. [Performance Optimization](#performance-optimization)
8. [Monitoring & Maintenance](#monitoring--maintenance)
9. [Backup Strategy](#backup-strategy)
10. [Troubleshooting](#troubleshooting)

## Server Requirements

### Minimum Specifications

**Production Server:**
- CPU: 2+ cores
- RAM: 4GB+ (8GB recommended)
- Storage: 20GB+ SSD
- Bandwidth: 100Mbps+

**Database Server** (can be same server for small deployments):
- CPU: 2+ cores
- RAM: 4GB+ (dedicated to MySQL)
- Storage: 20GB+ SSD with RAID

### Software Requirements

- **Operating System:** Ubuntu 22.04 LTS or Debian 12
- **PHP:** 8.2 or higher
- **Web Server:** Nginx 1.18+ or Apache 2.4+
- **Database:** MySQL 8.0+ or MariaDB 10.6+
- **Redis:** 7.0+
- **Node.js:** 18.x LTS (for asset building)
- **Composer:** 2.x
- **Supervisor:** Latest (for queue workers)

### PHP Extensions

Required extensions:
```bash
sudo apt install php8.2-cli php8.2-fpm php8.2-mysql php8.2-redis \
  php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd \
  php8.2-intl php8.2-bcmath
```

## Server Setup

### 1. Update System

```bash
sudo apt update
sudo apt upgrade -y
```

### 2. Install Nginx

```bash
sudo apt install nginx -y
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 3. Install PHP 8.2

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and extensions
sudo apt install php8.2-fpm php8.2-cli php8.2-common php8.2-mysql \
  php8.2-redis php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip \
  php8.2-gd php8.2-intl php8.2-bcmath -y

# Verify installation
php -v
```

### 4. Install MySQL 8.0

```bash
sudo apt install mysql-server -y

# Secure installation
sudo mysql_secure_installation

# Create database
sudo mysql -u root -p
```

```sql
CREATE DATABASE hotel_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'booking_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON hotel_booking.* TO 'booking_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Install Redis

```bash
sudo apt install redis-server -y

# Configure Redis
sudo nano /etc/redis/redis.conf
# Set: maxmemory 256mb
# Set: maxmemory-policy allkeys-lru

sudo systemctl enable redis-server
sudo systemctl restart redis-server

# Test Redis
redis-cli ping
# Should return: PONG
```

### 6. Install Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### 7. Install Node.js & NPM

```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

node -v
npm -v
```

### 8. Install Supervisor

```bash
sudo apt install supervisor -y
sudo systemctl enable supervisor
```

## Application Deployment

### 1. Create Deployment User

```bash
sudo adduser deployer
sudo usermod -aG www-data deployer
```

### 2. Clone Repository

```bash
sudo mkdir -p /var/www
sudo chown deployer:www-data /var/www

# Switch to deployer user
sudo su - deployer

cd /var/www
git clone https://github.com/yourusername/hotel-booking.git
cd hotel-booking
```

### 3. Install Dependencies

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# JavaScript dependencies
npm ci --production
```

### 4. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Edit configuration
nano .env
```

**Production .env Configuration:**

```env
APP_NAME="Hotel Booking System"
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://booking.yourhotel.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel_booking
DB_USERNAME=booking_user
DB_PASSWORD=strong_password_here

BROADCAST_DRIVER=log
CACHE_STORE=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Line Messaging API
LINE_CHANNEL_ACCESS_TOKEN=your_line_channel_token
LINE_CHANNEL_SECRET=your_line_channel_secret

# Session Security
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@yourhotel.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Run Migrations

```bash
php artisan migrate --force
```

### 7. Seed Database (First Deployment Only)

```bash
php artisan db:seed --force
```

**Important:** Change default passwords immediately after seeding!

### 8. Build Production Assets

```bash
npm run build
```

### 9. Create Storage Link

```bash
php artisan storage:link
```

### 10. Optimize Application

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### 11. Set Permissions

```bash
# Exit deployer user
exit

# Set ownership
sudo chown -R deployer:www-data /var/www/hotel-booking

# Set directory permissions
sudo find /var/www/hotel-booking -type d -exec chmod 755 {} \;
sudo find /var/www/hotel-booking -type f -exec chmod 644 {} \;

# Set write permissions for storage and cache
sudo chmod -R 775 /var/www/hotel-booking/storage
sudo chmod -R 775 /var/www/hotel-booking/bootstrap/cache
```

## Web Server Configuration

### Nginx Configuration

Create site configuration:

```bash
sudo nano /etc/nginx/sites-available/hotel-booking
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name booking.yourhotel.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name booking.yourhotel.com;
    root /var/www/hotel-booking/public;

    # SSL Configuration (Let's Encrypt)
    ssl_certificate /etc/letsencrypt/live/booking.yourhotel.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/booking.yourhotel.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' 'unsafe-inline' 'unsafe-eval' https: data:;" always;

    # Logging
    access_log /var/log/nginx/hotel-booking-access.log;
    error_log /var/log/nginx/hotel-booking-error.log;

    index index.php;
    charset utf-8;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Deny access to sensitive files
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
    location ~ /\.(?!well-known).* { deny all; }

    # Error pages
    error_page 404 /index.php;

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Asset caching
    location ~* \.(jpg|jpeg|gif|png|css|js|ico|xml|svg|woff|woff2|ttf)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Deny access to .php files in /storage
    location ~ /storage/.*\.php$ {
        deny all;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/hotel-booking /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Apache Configuration (Alternative)

```bash
sudo nano /etc/apache2/sites-available/hotel-booking.conf
```

```apache
<VirtualHost *:80>
    ServerName booking.yourhotel.com
    Redirect permanent / https://booking.yourhotel.com/
</VirtualHost>

<VirtualHost *:443>
    ServerName booking.yourhotel.com
    DocumentRoot /var/www/hotel-booking/public

    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/booking.yourhotel.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/booking.yourhotel.com/privkey.pem

    <Directory /var/www/hotel-booking/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    # Security Headers
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"

    # Logging
    ErrorLog ${APACHE_LOG_DIR}/hotel-booking-error.log
    CustomLog ${APACHE_LOG_DIR}/hotel-booking-access.log combined
</VirtualHost>
```

Enable modules and site:

```bash
sudo a2enmod rewrite ssl headers
sudo a2ensite hotel-booking
sudo apache2ctl configtest
sudo systemctl reload apache2
```

## Queue Workers Setup

See [QUEUE_WORKER_SETUP.md](QUEUE_WORKER_SETUP.md) for detailed instructions.

### Quick Setup with Supervisor

```bash
# Copy configuration
sudo cp /var/www/hotel-booking/deployment/supervisor/laravel-worker.conf \
  /etc/supervisor/conf.d/

# Edit paths if needed
sudo nano /etc/supervisor/conf.d/laravel-worker.conf

# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Start workers
sudo supervisorctl start laravel-worker:*

# Check status
sudo supervisorctl status
```

## SSL/HTTPS Configuration

### Using Let's Encrypt (Free)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtain certificate
sudo certbot --nginx -d booking.yourhotel.com

# Test automatic renewal
sudo certbot renew --dry-run
```

Certificate will auto-renew. Verify with:

```bash
sudo systemctl status certbot.timer
```

## Performance Optimization

### 1. PHP-FPM Optimization

```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

```bash
sudo systemctl restart php8.2-fpm
```

### 2. MySQL Optimization

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
[mysqld]
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
innodb_flush_method = O_DIRECT
max_connections = 200
query_cache_size = 0
query_cache_type = 0
```

```bash
sudo systemctl restart mysql
```

### 3. Redis Optimization

```bash
sudo nano /etc/redis/redis.conf
```

```ini
maxmemory 512mb
maxmemory-policy allkeys-lru
save ""
```

```bash
sudo systemctl restart redis-server
```

### 4. Nginx Optimization

```bash
sudo nano /etc/nginx/nginx.conf
```

```nginx
worker_processes auto;
worker_connections 1024;

gzip on;
gzip_vary on;
gzip_comp_level 6;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

client_max_body_size 10M;
```

## Monitoring & Maintenance

### Log Monitoring

```bash
# Application logs
tail -f /var/www/hotel-booking/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/hotel-booking-error.log

# PHP-FPM logs
tail -f /var/log/php8.2-fpm.log

# Queue worker logs
tail -f /var/www/hotel-booking/storage/logs/worker.log
```

### Health Checks

```bash
# Application health
curl https://booking.yourhotel.com/up

# Database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Redis connection
redis-cli ping

# Queue status
php artisan queue:monitor database
```

### Scheduled Tasks (Cron)

```bash
sudo crontab -e -u deployer
```

```cron
* * * * * cd /var/www/hotel-booking && php artisan schedule:run >> /dev/null 2>&1
```

## Backup Strategy

### Database Backup Script

```bash
sudo nano /usr/local/bin/backup-db.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/hotel-booking/database"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="hotel_booking"
DB_USER="booking_user"
DB_PASS="your_password"

mkdir -p $BACKUP_DIR

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep only last 30 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete

echo "Backup completed: backup_$DATE.sql.gz"
```

```bash
sudo chmod +x /usr/local/bin/backup-db.sh
```

Schedule daily backups:

```bash
sudo crontab -e
```

```cron
0 2 * * * /usr/local/bin/backup-db.sh
```

### Application Backup

```bash
# Backup application files
cd /var/www
tar -czf hotel-booking-backup-$(date +%Y%m%d).tar.gz hotel-booking

# Backup storage (uploaded files)
tar -czf hotel-booking-storage-$(date +%Y%m%d).tar.gz hotel-booking/storage/app
```

## Troubleshooting

### Application Issues

**500 Internal Server Error:**
```bash
# Check error logs
tail -f storage/logs/laravel.log

# Rebuild caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Check permissions
sudo chmod -R 775 storage bootstrap/cache
```

**Queue Not Processing:**
```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart laravel-worker:*

# Check failed jobs
php artisan queue:failed
```

**Database Connection Failed:**
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check credentials in .env
# Verify MySQL is running
sudo systemctl status mysql
```

### Performance Issues

**Slow Response Times:**
```bash
# Enable query log temporarily
DB_LOG_QUERIES=true

# Check slow queries
sudo mysql -e "SELECT * FROM mysql.slow_log\G"

# Optimize tables
php artisan optimize
```

**High Memory Usage:**
```bash
# Check PHP-FPM processes
sudo ps aux | grep php-fpm

# Adjust pm.max_children in /etc/php/8.2/fpm/pool.d/www.conf
# Monitor with htop
sudo apt install htop
htop
```

## Security Checklist

- [ ] Firewall configured (UFW)
- [ ] SSH key-based authentication only
- [ ] Fail2ban installed and configured
- [ ] SSL certificate installed and auto-renewing
- [ ] Database credentials are strong
- [ ] APP_DEBUG=false in production
- [ ] File permissions correctly set
- [ ] Security headers configured in Nginx/Apache
- [ ] Regular security updates scheduled
- [ ] Backups tested and verified
- [ ] Queue workers running as non-root user
- [ ] Redis password set (if exposed)

## Update Procedure

```bash
# 1. Enable maintenance mode
php artisan down

# 2. Pull latest code
git pull origin main

# 3. Update dependencies
composer install --no-dev --optimize-autoloader
npm ci --production

# 4. Run migrations
php artisan migrate --force

# 5. Rebuild assets
npm run build

# 6. Clear and rebuild caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart queue workers
sudo supervisorctl restart laravel-worker:*

# 8. Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# 9. Disable maintenance mode
php artisan up

# 10. Verify deployment
curl https://booking.yourhotel.com/up
```

## Support

For deployment issues:
- Check [README.md](../README.md)
- Review [docs/](../)
- Create GitHub issue

---

**Last Updated:** November 12, 2025

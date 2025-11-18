# Deployment Guide - Islamic Society Website

This guide covers the deployment and performance optimization setup for the Islamic Society Website (SIKS).

## Table of Contents

1. [System Requirements](#system-requirements)
2. [Environment Setup](#environment-setup)
3. [Database Optimization](#database-optimization)
4. [Caching Configuration](#caching-configuration)
5. [File Storage Optimization](#file-storage-optimization)
6. [Performance Monitoring](#performance-monitoring)
7. [Deployment Steps](#deployment-steps)
8. [Post-Deployment Checklist](#post-deployment-checklist)

## System Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher
- **Redis**: 6.0 or higher (for caching)
- **Node.js**: 18.0 or higher (for asset compilation)
- **Memory**: 2GB RAM minimum, 4GB recommended
- **Storage**: 20GB minimum, SSD recommended

### Recommended Extensions
```bash
# PHP Extensions
php-gd
php-imagick
php-redis
php-opcache
php-zip
php-curl
php-mbstring
php-xml
php-bcmath
```

## Environment Setup

### 1. Production Environment Variables

Create a `.env` file with the following optimized settings:

```env
# Application
APP_NAME="Islamic Society Website"
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siks_production
DB_USERNAME=siks_user
DB_PASSWORD=secure_password_here

# Cache Configuration
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# File Storage
FILESYSTEM_DISK=public
AWS_BUCKET=your-s3-bucket (if using S3)

# Performance Settings
OPCACHE_ENABLE=true
OPCACHE_MEMORY_CONSUMPTION=256
OPCACHE_MAX_ACCELERATED_FILES=20000

# Image Optimization
IMAGE_OPTIMIZATION_ENABLED=true
WEBP_SUPPORT_ENABLED=true
LAZY_LOADING_ENABLED=true
```

### 2. PHP Configuration (php.ini)

```ini
# Memory and Execution
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

# File Uploads
upload_max_filesize = 10M
post_max_size = 50M
max_file_uploads = 20

# OPcache Configuration
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1

# Session Configuration
session.driver=redis
session.lifetime=120
session.encrypt=true
```

## Database Optimization

### 1. MySQL Configuration (my.cnf)

```ini
[mysqld]
# InnoDB Settings
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Query Cache
query_cache_type = 1
query_cache_size = 128M
query_cache_limit = 2M

# Connection Settings
max_connections = 200
wait_timeout = 300
interactive_timeout = 300

# Slow Query Log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

### 2. Database Indexes

The following indexes are automatically created by the migration:

```sql
-- Events table indexes
CREATE INDEX events_status_date_index ON events (status, event_date);
CREATE INDEX events_fest_status_index ON events (fest_id, status);
CREATE INDEX events_type_status_index ON events (type, status);
CREATE INDEX events_registration_type_status_index ON events (registration_type, status);

-- Registrations table indexes
CREATE INDEX registrations_event_status_index ON registrations (event_id, status);
CREATE INDEX registrations_user_status_index ON registrations (user_id, status);
CREATE INDEX registrations_payment_index ON registrations (payment_status, payment_required);

-- Additional recommended indexes for production
CREATE INDEX events_author_date_index ON events (author_id, event_date);
CREATE INDEX registrations_date_status_index ON registrations (registered_at, status);
CREATE INDEX gallery_images_type_created_index ON gallery_images (imageable_type, created_at);
```

### 3. Database Maintenance

```bash
# Run these commands regularly
# Optimize tables
mysqlcheck -o --all-databases -u root -p

# Analyze tables for better query planning
mysqlcheck -a --all-databases -u root -p

# Check for table corruption
mysqlcheck -c --all-databases -u root -p
```

## Caching Configuration

### 1. Redis Configuration (redis.conf)

```conf
# Memory Management
maxmemory 1gb
maxmemory-policy allkeys-lru

# Persistence
save 900 1
save 300 10
save 60 10000

# Network
timeout 300
tcp-keepalive 300

# Security
requirepass your_redis_password_here
```

### 2. Laravel Cache Configuration

```php
// config/cache.php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
],

'prefix' => env('CACHE_PREFIX', 'siks_cache'),
```

### 3. Cache Warming Script

Create a cache warming script:

```bash
#!/bin/bash
# cache-warm.sh

echo "Warming up application cache..."

# Clear existing cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache configuration and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Warm up application-specific cache
php artisan tinker --execute="app(\App\Services\CacheService::class)->warmUpCache();"

echo "Cache warming completed!"
```

## File Storage Optimization

### 1. Image Optimization Setup

Install required packages:

```bash
# Install Intervention Image
composer require intervention/image

# Install image optimization tools (Ubuntu/Debian)
sudo apt-get install jpegoptim optipng pngquant gifsicle webp

# Install image optimization tools (CentOS/RHEL)
sudo yum install jpegoptim optipng pngquant gifsicle libwebp-tools
```

### 2. Storage Directory Structure

```
storage/app/public/
├── gallery/
│   ├── events/
│   │   └── {event_id}/
│   │       ├── thumbnails/
│   │       │   ├── small_*
│   │       │   ├── medium_*
│   │       │   └── large_*
│   │       └── original images
│   ├── fests/
│   └── general/
├── event_images/
├── blog_images/
└── uploads/
```

### 3. CDN Configuration (Optional)

For better performance, configure a CDN:

```php
// config/filesystems.php
'disks' => [
    'cdn' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
    ],
],
```

## Performance Monitoring

### 1. Application Performance Monitoring

Install Laravel Telescope for development/staging:

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### 2. Server Monitoring

Recommended monitoring tools:
- **New Relic** or **DataDog** for application performance
- **Prometheus + Grafana** for server metrics
- **ELK Stack** for log analysis

### 3. Performance Metrics to Monitor

- **Response Time**: < 200ms for cached pages, < 500ms for dynamic pages
- **Database Query Time**: < 100ms average
- **Cache Hit Ratio**: > 90%
- **Memory Usage**: < 80% of available RAM
- **CPU Usage**: < 70% average
- **Disk I/O**: Monitor for bottlenecks

## Deployment Steps

### 1. Initial Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install nginx mysql-server redis-server php8.2-fpm php8.2-mysql php8.2-redis php8.2-gd php8.2-imagick

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Application Deployment

```bash
# Clone repository
git clone https://github.com/your-repo/islamic-society-website.git
cd islamic-society-website

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --force
php artisan db:seed --class=AdminUserSeeder

# Performance optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Run performance indexes migration
php artisan migrate --path=database/migrations/2025_11_18_124440_add_performance_indexes_to_tables.php --force
```

### 3. Web Server Configuration

#### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/islamic-society-website/public;

    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Cache Static Assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt|webp)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # PHP Configuration
    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Post-Deployment Checklist

### 1. Functionality Testing

- [ ] User registration and login
- [ ] Event creation and management
- [ ] Registration workflows (individual and team)
- [ ] Payment submission and verification
- [ ] Gallery image upload and display
- [ ] Prayer times display and management
- [ ] Admin dashboard functionality
- [ ] Email notifications

### 2. Performance Testing

- [ ] Page load times < 2 seconds
- [ ] Image lazy loading working
- [ ] Cache hit ratios > 90%
- [ ] Database query optimization
- [ ] Mobile responsiveness
- [ ] SEO optimization

### 3. Security Testing

- [ ] SSL certificate properly configured
- [ ] Security headers present
- [ ] File upload restrictions working
- [ ] Authentication and authorization
- [ ] CSRF protection enabled
- [ ] Input validation and sanitization

### 4. Monitoring Setup

- [ ] Application performance monitoring
- [ ] Server resource monitoring
- [ ] Error logging and alerting
- [ ] Backup procedures
- [ ] Update procedures

### 5. Documentation

- [ ] Admin user guide
- [ ] API documentation (if applicable)
- [ ] Maintenance procedures
- [ ] Troubleshooting guide
- [ ] Contact information for support

## Maintenance Tasks

### Daily
- Monitor application logs
- Check system resource usage
- Verify backup completion

### Weekly
- Review performance metrics
- Update security patches
- Clean up temporary files
- Optimize database tables

### Monthly
- Review and rotate logs
- Update dependencies
- Performance audit
- Security audit

## Troubleshooting

### Common Issues

1. **Slow Page Load Times**
   - Check cache configuration
   - Review database queries
   - Monitor server resources

2. **Image Upload Failures**
   - Check file permissions
   - Verify storage space
   - Review PHP upload limits

3. **Cache Issues**
   - Clear application cache
   - Restart Redis service
   - Check Redis memory usage

4. **Database Performance**
   - Review slow query log
   - Check index usage
   - Monitor connection pool

For additional support, refer to the Laravel documentation and project-specific guides.
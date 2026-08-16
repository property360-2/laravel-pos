#!/usr/bin/env bash
set -e

# Configure Apache to listen on Render's dynamic PORT variable (defaults to 8080)
PORT=${PORT:-8080}
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure storage directories exist
mkdir -p /var/www/html/storage/framework/{sessions,views,cache} /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Link storage
php artisan storage:link --force || true

# Run database migrations automatically on boot
php artisan migrate --force

# Cache Laravel configuration & routes for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache web server in foreground
exec apache2-foreground

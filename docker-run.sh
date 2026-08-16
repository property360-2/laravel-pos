#!/usr/bin/env bash
set -e

# Configure Apache to listen on Render's dynamic PORT variable (defaults to 8080)
PORT=${PORT:-8080}
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure storage and database directories & SQLite file exist
mkdir -p /var/www/html/storage/framework/{sessions,views,cache} /var/www/html/storage/logs /var/www/html/database
touch /var/www/html/database/database.sqlite

# Link storage
php artisan storage:link --force || true

# Run database migrations and seeders automatically on boot
php artisan migrate --force --seed

# Grant write access on database folder and SQLite file to www-data AFTER migrations create/modify it
chown -R www-data:www-data /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache
chmod 664 /var/www/html/database/database.sqlite || true

# Cache Laravel configuration & routes for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache web server in foreground
exec apache2-foreground

# Stage 1: Build Frontend Assets with Node & Vite
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: PHP 8.2 + Apache Production Web Server
FROM php:8.2-apache

# Install System Dependencies & PHP Extensions required by Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql pdo_sqlite mbstring exif pcntl bcmath gd zip

# Enable Apache mod_rewrite for Laravel routing
RUN a2enmod rewrite

# Copy Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set Working Directory
WORKDIR /var/www/html

# Copy Application Files
COPY . /var/www/html

# Copy Compiled Frontend Assets from Stage 1
COPY --from=frontend /app/public/build /var/www/html/public/build

# Install PHP Production Dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set File Permissions for Storage, Database & Cache
RUN mkdir -p /var/www/html/database \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Point Apache DocumentRoot to Laravel /public directory
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Copy & prepare container startup script
COPY docker-run.sh /usr/local/bin/docker-run.sh
RUN chmod +x /usr/local/bin/docker-run.sh

ENTRYPOINT ["/usr/local/bin/docker-run.sh"]

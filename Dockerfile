# Gunakan image PHP + Apache
FROM php:8.2-apache

# Install dependency dasar
RUN apt-get update && apt-get install -y \
    libzip-dev unzip curl git zip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy file project ke folder apache
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Aktifkan mod_rewrite Laravel
RUN a2enmod rewrite

# Set permission
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Jalankan Composer
RUN composer install --no-interaction --optimize-autoloader

# Jalankan Laravel artisan
RUN php artisan config:clear
RUN php artisan route:clear
RUN php artisan view:clear

# Generate APP_KEY otomatis
RUN php artisan key:generate

# Expose port
EXPOSE 80

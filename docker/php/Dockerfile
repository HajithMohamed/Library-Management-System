# Use official PHP 8.2 FPM image with specific tag
FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apk update && apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && rm -rf /var/cache/apk/*

# Install Composer manually to avoid timeout issues
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy custom PHP config
COPY docker/php/php.ini /usr/local/etc/php/

# Copy composer files first
COPY composer.json composer.lock /var/www/html/

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy project files
COPY src /var/www/html

# Set permissions for PHP
RUN chown -R www-data:www-data /var/www/html

# Expose PHP-FPM port
EXPOSE 9000

# Default command
CMD ["php-fpm"]

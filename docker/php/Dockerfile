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

# Install Composer manually with retry logic
RUN for i in 1 2 3; do \
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && break || sleep 10; \
    done

# Copy custom PHP config
COPY docker/php/php.ini /usr/local/etc/php/

# Copy composer files first
COPY composer.json composer.lock /var/www/html/

# Install Composer dependencies with retry
RUN for i in 1 2 3; do \
        composer install --no-dev --optimize-autoloader && break || sleep 10; \
    done

# Copy project files
COPY src /var/www/html

# Set permissions for PHP
RUN chown -R www-data:www-data /var/www/html

# Expose PHP-FPM port
EXPOSE 9000

# Default command
CMD ["php-fpm"]

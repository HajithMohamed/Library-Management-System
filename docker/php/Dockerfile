# Use official PHP 8.2 FPM image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer (official installer)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy custom PHP config
COPY php.ini /usr/local/etc/php/

# Copy project files
COPY ./src /var/www/html

# Set permissions for PHP
RUN chown -R www-data:www-data /var/www/html

# Expose PHP-FPM port
EXPOSE 9000

# Default command
CMD ["php-fpm"]

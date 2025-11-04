# Use official PHP 8.2 FPM as a base
FROM php:8.2-fpm-alpine

# Install Nginx, Supervisor, and your other dependencies
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    git \
    unzip \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

# Copy your custom PHP settings.
# Putting it in conf.d ensures it's loaded without overwriting other defaults.
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Configure Nginx and Supervisor
# These files will tell Nginx how to serve your app and Supervisor how to run both services.
COPY nginx.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisord.conf

# Set up application directory
WORKDIR /var/www/html

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer update --no-dev --optimize-autoloader

# Copy your application source code
COPY src /var/www/html

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 for Nginx (Render's public-facing port)
EXPOSE 80

# Use Supervisor to start and manage Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

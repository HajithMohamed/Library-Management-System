# Use official PHP 8.2 FPM as a base
FROM php:8.2-fpm-alpine

# Install Nginx, Supervisor, and dependencies
RUN apk update && apk add --no-cache nginx supervisor git unzip libzip-dev

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory
WORKDIR /var/www/html

# --- START OF CRITICAL CHANGES ---

# 1. Copy composer files FIRST
COPY composer.json composer.lock ./

# 2. Run composer install. This creates /var/www/html/vendor/
RUN composer install --no-dev --optimize-autoloader

# 3. Copy the rest of your application code
COPY src/ .

# --- END OF CRITICAL CHANGES ---

# Copy configurations
COPY php.ini /usr/local/etc/php/conf.d/custom.ini
COPY nginx.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisord.conf

# Set permissions for Nginx logs and the entire app directory
RUN mkdir -p /var/log/nginx && chown -R www-data:www-data /var/log/nginx
RUN chown -R www-data:www-data /var/www/html

# Expose port 80 for Nginx
EXPOSE 80

# Use Supervisor to start Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

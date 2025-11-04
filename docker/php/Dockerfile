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

# --- START OF CHANGES ---

# Set the WORKDIR one level higher
WORKDIR /var/www

# Copy composer files to /var/www and install dependencies.
# This will create the vendor directory at /var/www/vendor/
COPY composer.json composer.lock ./
RUN composer update --no-dev --optimize-autoloader

# Now, copy the application source code into the 'html' subdirectory
COPY src/ /var/www/html/

# --- END OF CHANGES ---

# Copy configurations
COPY php.ini /usr/local/etc/php/conf.d/custom.ini
COPY nginx.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisord.conf

# --- Permission Fixes ---
# Create Nginx log directory and set permissions
RUN mkdir -p /var/log/nginx && \
    chown -R www-data:www-data /var/log/nginx

# Set correct permissions on the entire /var/www directory
# This ensures Nginx can read files and Composer can write the vendor dir.
RUN chown -R www-data:www-data /var/www

# Set correct execute/read permissions for directories and files
RUN find /var/www -type d -exec chmod 755 {} \;
RUN find /var/www -type f -exec chmod 644 {} \;
# --- End Permission Fixes ---

# Expose port 80 for Nginx
EXPOSE 80

# Use Supervisor to start and manage Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

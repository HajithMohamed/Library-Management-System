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

# Copy your custom PHP settings
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Configure Nginx and Supervisor
COPY nginx.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisord.conf

# Set up application directory
WORKDIR /var/www/html

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer update --no-dev --optimize-autoloader

# Copy your application source code
COPY src /var/www/html

# --- Permission Fixes ---
# Create Nginx log directory and set permissions
RUN mkdir -p /var/log/nginx && \
    chown -R www-data:www-data /var/log/nginx

# Set correct permissions on the entire web root
RUN chown -R www-data:www-data /var/www/html

# Set correct execute/read permissions for directories and files
RUN find /var/www/html -type d -exec chmod 755 {} \;
RUN find /var/www/html -type f -exec chmod 644 {} \;
# --- End Permission Fixes ---

# Expose port 80 for Nginx
EXPOSE 80

# Use Supervisor to start and manage Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

# Use official PHP 8.2 FPM as a base
FROM php:8.2-fpm-alpine

# Install Nginx, Supervisor, and required system libraries for PHP extensions
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    libzip-dev

# --- THIS IS THE FIX ---
# Install the required PHP extensions, including mysqli
RUN docker-php-ext-install mysqli pdo pdo_mysql zip
# --- END OF FIX ---

# Set the working directory
WORKDIR /var/www/html

# Copy your entire 'src' directory, which includes the 'vendor' folder,
# into the container's web root.
COPY src/ .

# Copy your configuration files
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

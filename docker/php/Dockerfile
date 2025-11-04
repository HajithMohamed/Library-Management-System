# Use official PHP 8.2 FPM as a base
FROM php:8.2-fpm-alpine

# Install Nginx and Supervisor. We don't need Composer's dependencies.
RUN apk update && apk add --no-cache nginx supervisor

# Set the working directory
WORKDIR /var/www/html

# --- THE KEY CHANGE ---
# Copy your entire 'src' directory, which already contains the 'vendor' folder,
# into the container's web root. This is the build-time equivalent
# of your local docker-compose volume mount.
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

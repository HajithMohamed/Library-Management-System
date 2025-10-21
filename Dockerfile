# Use official PHP + Apache image
FROM php:8.2-apache

# Install PHP extensions (mysqli for MySQL)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache mod_rewrite (if your app uses URL rewriting)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all files into the container
COPY . /var/www/html/

# Expose Apache port
EXPOSE 80

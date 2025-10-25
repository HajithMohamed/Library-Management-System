# Use official PHP 8.2 FPM
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk update && apk add --no-cache \
    git unzip libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

COPY docker/php/php.ini /usr/local/etc/php/

COPY composer.json composer.lock ./

# Verify PHP version before Composer
RUN php -v && composer update --no-dev --optimize-autoloader

COPY src /var/www/html
RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
CMD ["php-fpm"]

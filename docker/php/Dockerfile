FROM php:8.1-fpm

# Dependencias
RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html


RUN chown -R www-data:www-data /var/www/html

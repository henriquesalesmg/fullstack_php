FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libsodium-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    zip \
    git \
    && docker-php-ext-install pdo_mysql mbstring zip sodium

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

COPY ./app ./app
COPY ./public ./public

RUN composer install --no-interaction --no-dev --optimize-autoloader

CMD ["php-fpm"]
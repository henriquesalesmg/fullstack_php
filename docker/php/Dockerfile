FROM php:8.2-fpm

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    unzip \
    zip \
    && docker-php-ext-install pdo_mysql mbstring zip

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho no container
WORKDIR /var/www/html

# Copia os arquivos composer.json e composer.lock da raiz do projeto
COPY composer.* /var/www/html/

# Instala dependências PHP na raiz do projeto
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Copia a pasta app (código PHP) para /var/www/html
COPY ./app /var/www/html

CMD ["php-fpm"]

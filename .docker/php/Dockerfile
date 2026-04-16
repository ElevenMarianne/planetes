FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        intl \
        opcache \
        mbstring \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Créer un utilisateur avec le même UID que l'utilisateur host (1000)
RUN groupmod -g 1000 www-data && usermod -u 1000 -g 1000 www-data

WORKDIR /var/www/html

USER www-data

ENV COMPOSER_ALLOW_SUPERUSER=0

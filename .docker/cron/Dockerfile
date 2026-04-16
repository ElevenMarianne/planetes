FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    cron \
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
        mbstring \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini /usr/local/etc/php/conf.d/custom.ini
COPY crontab /etc/cron.d/planetes-cron

RUN chmod 0644 /etc/cron.d/planetes-cron \
    && crontab /etc/cron.d/planetes-cron

WORKDIR /var/www/html

CMD ["cron", "-f"]

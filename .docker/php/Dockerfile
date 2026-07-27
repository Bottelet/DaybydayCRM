FROM php:8.3-fpm

MAINTAINER Casper Bottelet <cbottelet@gmail.com>

# Install system dependencies
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y \
        mariadb-client \
        libmemcached-dev \
        libpq-dev \
        libzip-dev \
        libonig-dev \
        libicu-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libbz2-dev \
        unzip \
    && pecl channel-update pecl.php.net

# PHP extensions
RUN docker-php-ext-install -j$(nproc) \
        bcmath \
        zip \
        bz2 \
        mbstring \
        intl \
        pdo \
        pdo_mysql \
        pcntl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# PECL extensions: caching and memcached (mcrypt is gone for good in PHP 8 - Laravel 12 doesn't need it)
RUN pecl install apcu memcached redis \
    && docker-php-ext-enable apcu memcached redis

# Composer (pinned via official image, no curl-pipe-to-shell)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Node.js 20 LTS + Yarn (via corepack; the old deb.nodesource/apt yarn repo is unmaintained)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && corepack enable \
    && corepack prepare yarn@stable --activate

RUN groupadd -g 1000 daybyday \
    && useradd -u 1000 -ms /bin/bash daybyday -g daybyday

COPY --chown=daybyday:daybyday . /var/www/html
WORKDIR /var/www/html

# Set permissions
RUN chmod -R 0775 ./bootstrap/cache ./storage

USER 1000:1000

EXPOSE 9000
CMD ["php-fpm"]

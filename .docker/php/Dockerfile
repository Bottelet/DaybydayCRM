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
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libbz2-dev \
        libpng-dev \
        unzip \
        zip \
        cron \
        nginx \
        ca-certificates \
        curl \
        gnupg

# PHP Extensions
RUN docker-php-ext-install -j$(nproc) \
        bcmath \
        zip \
        bz2 \
        mbstring \
        pdo \
        pdo_mysql \
        pcntl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Optional: caching and memcached
RUN pecl install apcu memcached \
    && docker-php-ext-enable apcu memcached

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Node.js 20 + Yarn
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && corepack enable

# User
RUN groupadd -g 1000 daybyday \
    && useradd -u 1000 -ms /bin/bash daybyday -g daybyday

# App files
COPY --chown=daybyday:daybyday . /var/www/html
WORKDIR /var/www/html

# Permissions
RUN chmod -R 0775 ./bootstrap/cache ./storage

USER 1000:1000

EXPOSE 9000
CMD ["php-fpm"]
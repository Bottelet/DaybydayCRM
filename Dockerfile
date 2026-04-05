FROM php:8.3-fpm

MAINTAINER Casper Bottelet <cbottelet@gmail.com>

# Update packages and install composer and PHP dependencies.
RUN apt-get update && \
  DEBIAN_FRONTEND=noninteractive apt-get install -y \
    mariadb-client \
    libmemcached-dev \
    libpq-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libbz2-dev \
    cron \
    nginx \
    nano \
    python \
    python-pip \
    && pecl channel-update pecl.php.net \
    && pecl install apcu \
    && pip install awscli

# PHP Extensions
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install zip
RUN docker-php-ext-install bz2
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pcntl


RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
&& docker-php-ext-install gd

# Install the PHP mcrypt extention (from PECL, mcrypt has been removed from PHP 7.2)
RUN pecl install mcrypt-1.0.2
RUN docker-php-ext-enable mcrypt

# Install the PHP redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install the php memcached extension
RUN pecl install memcached && docker-php-ext-enable memcached

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure nginx
COPY .docker/nginx/nginx.conf /etc/nginx/nginx.conf

#Frotend NPM/YARN
# Install Node.js 20 LTS + Yarn (modern method)
RUN apt-get update \
    && apt-get install -y ca-certificates curl gnupg \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
        | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" \
        > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs yarn \
    && corepack enable

RUN useradd -u 1000 -ms /bin/bash daybyday

# Make sure files/folders needed by the processes are accessable when they run under the nobody user
RUN chown -R daybyday.www-data /run && \
  chown -R daybyday.www-data /var/lib/nginx && \
  chown -R daybyday.www-data /var/log/nginx

ADD . /var/www/html
WORKDIR /var/www/html

RUN npm install --pure-lockfile --ignore-engines
RUN npm run prod
# Set permissions
RUN chmod 0777 ./bootstrap/cache -R
RUN chmod 0777 ./storage/* -R

EXPOSE 80
EXPOSE 433

# RUN cd /var/www/html && composer install -q --no-dev -o
CMD composer install --no-ansi --no-dev --no-interaction --optimize-autoloader && php-fpm -D && nginx -g "daemon off;"


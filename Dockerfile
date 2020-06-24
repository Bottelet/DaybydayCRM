FROM php:7.3-fpm

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

# Install the php memcached extension
RUN pecl install memcached && docker-php-ext-enable memcached

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure nginx
COPY .docker/nginx/nginx.conf /etc/nginx/nginx.conf

#Frotend NPM/YARN
RUN curl -sL https://deb.nodesource.com/setup_11.x | bash -
RUN apt-get install -y nodejs
RUN curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update && apt-get install yarn

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


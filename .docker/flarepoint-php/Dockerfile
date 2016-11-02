FROM php:7.0-fpm

MAINTAINER Saleh Saeed <saleh.saiid@gmail.com>

WORKDIR /tmp

RUN usermod -u 1000 www-data

RUN docker-php-source extract \
    && apt-get -qq update && apt-get install -y \
                              git zlib1g-dev \
                              libfreetype6-dev \
                              libjpeg62-turbo-dev \
                              libmcrypt-dev \
                              libpng12-dev \
	&& docker-php-ext-install pdo pdo_mysql zip \
	&& docker-php-ext-install -j$(nproc) iconv mcrypt \
	&& docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
	&& docker-php-ext-install -j$(nproc) gd \
	&& apt-get autoclean && apt-get autoremove && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
	&& docker-php-source delete

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Xdebug
RUN pecl install -o -f xdebug-2.4.0

ADD .docker/flarepoint-php/php-xdebug.ini /usr/local/etc/php/conf.d/php-xdebug.ini
RUN docker-php-ext-enable xdebug

COPY . /var/www/html
WORKDIR /var/www/html

# Set permissions
RUN chmod 0777 ./bootstrap/cache -R
RUN chmod 0777 ./storage/* -R

# RUN cd /var/www/html && composer install -q --no-dev -o

CMD ["php-fpm"]
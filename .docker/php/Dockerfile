FROM php:8.3-fpm-bookworm

MAINTAINER DaybydayCRM Development Team

# Install system dependencies
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    build-essential \
    libpq-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libbz2-dev \
    libmemcached-dev \
    zlib1g-dev \
    curl \
    git \
    ca-certificates \
    mariadb-client \
    cron \
    && rm -rf /var/lib/apt/lists/*

# PHP Extensions - Core
RUN docker-php-ext-install \
    bcmath \
    zip \
    bz2 \
    mbstring \
    pdo \
    pdo_mysql \
    pcntl \
    opcache

# PHP Extensions - GD (updated for PHP 8.3)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# PHP Extensions - PECL
RUN pecl install apcu memcached redis \
    && docker-php-ext-enable apcu memcached redis

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Node.js 20 (for Laravel's asset compilation)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Yarn package manager
RUN npm install -g yarn

# Playwright system dependencies for e2e testing
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    libatk-bridge2.0-0 \
    libatk1.0-0 \
    libc6 \
    libcairo2 \
    libcups2 \
    libdbus-1-3 \
    libexpat1 \
    libgbm1 \
    libglib2.0-0 \
    libglib2.0-0 \
    libgobject2.0-0 \
    libgtk-3-0 \
    libpango-1.0-0 \
    libpango-gobject-2.0-0 \
    libpangocairo-1.0-0 \
    libstdc++6 \
    libx11-6 \
    libx11-xcb1 \
    libxcb1 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxext6 \
    libxfixes3 \
    libxi6 \
    libxinerama1 \
    libxkbcommon0 \
    libxrandr2 \
    libxrender1 \
    libxss1 \
    libxtst6 \
    && rm -rf /var/lib/apt/lists/*

# Create application user
RUN groupadd -g 1000 daybyday \
    && useradd -u 1000 -ms /bin/bash daybyday -g daybyday

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY --chown=daybyday:daybyday . .

# Set permissions
RUN chmod -R 0775 bootstrap/cache storage \
    && find . -type d -exec chmod 755 {} \; \
    && find . -type f -exec chmod 644 {} \;

# Set PHP-FPM user
USER daybyday:daybyday

EXPOSE 9000
CMD ["php-fpm"]

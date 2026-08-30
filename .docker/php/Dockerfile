FROM php:8.3-fpm-bookworm

LABEL maintainer="DaybydayCRM Development Team"

# Install system dependencies and PHP extensions in one layer for better caching
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    build-essential \
    ca-certificates \
    cron \
    curl \
    git \
    libbz2-dev \
    libcairo2 \
    libcups2 \
    libdbus-1-3 \
    libexpat1 \
    libfreetype6-dev \
    libgbm1 \
    libglib2.0-0 \
    libgtk-3-0 \
    libjpeg62-turbo-dev \
    libmemcached-dev \
    libpango-1.0-0 \
    libpng-dev \
    libpq-dev \
    libxcb1 \
    libxrender1 \
    libxss1 \
    mariadb-client \
    zlib1g-dev \
    && docker-php-ext-install \
    bcmath \
    zip \
    bz2 \
    mbstring \
    pdo \
    pdo_mysql \
    pcntl \
    opcache \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && pecl install apcu memcached redis \
    && docker-php-ext-enable apcu memcached redis \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js 20 and Yarn (for Laravel asset compilation)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && npm install -g yarn \
    && npm cache clean --force \
    && rm -rf /var/lib/apt/lists/*

# Create application user (non-root for security)
RUN groupadd -g 1000 daybyday \
    && useradd -u 1000 -ms /bin/bash daybyday -g daybyday

# Set working directory
WORKDIR /var/www/html

# Copy application files with correct ownership
COPY --chown=daybyday:daybyday . .

# Set proper permissions (directories 755, files 644)
RUN chmod -R 0755 . \
    && chmod -R 0775 bootstrap/cache storage

# Run as non-root user
USER daybyday:daybyday

EXPOSE 9000

CMD ["php-fpm"]

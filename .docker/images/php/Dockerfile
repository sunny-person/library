FROM php:8.0.19-fpm

RUN apt-get update && apt-get install -y \
    curl \
    wget \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    libmcrypt-dev \
    libpq-dev \
      && pecl install mcrypt \
        && docker-php-ext-enable mcrypt \
      && docker-php-ext-install -j$(nproc) iconv mbstring mysqli pdo pdo_mysql zip \
        && docker-php-ext-configure gd --with-freetype --with-jpeg \
      && docker-php-ext-install -j$(nproc) gd \
        && docker-php-ext-enable mysqli pdo_mysql

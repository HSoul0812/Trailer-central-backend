FROM php:8.0-fpm-bullseye

ENV TZ='America/Chicago'

RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    libsodium-dev \
    unzip \
    libssl-dev \
    libpq-dev \
    libpng-dev \
    npm

RUN npm install -g cross-env

# php extension for concurrent programming
RUN pecl install swoole xdebug
RUN docker-php-ext-enable swoole xdebug \
    && echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini


# laravel and project extensions
RUN docker-php-ext-install zip pdo_mysql pgsql pdo_pgsql pcntl

RUN curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

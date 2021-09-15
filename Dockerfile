FROM php:8-fpm-bullseye

ENV TZ='America/Chicago'

RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    libsodium-dev \
    unzip \
    libssl-dev \
    libpq-dev

# php extension for concurrent programming
RUN pecl install swoole
RUN docker-php-ext-enable swoole

# laravel and project extensions
RUN docker-php-ext-install sodium zip pdo pdo_mysql pgsql pdo_pgsql pcntl

RUN curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

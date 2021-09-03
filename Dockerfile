FROM php:8-fpm-bullseye

ENV TZ='America/Chicago'

RUN apt-get update && apt-get install -y \
    git \
    libzip-dev \
    libsodium-dev \
    unzip \
    libssl-dev

# for concurrent programming
RUN pecl install swoole

RUN docker-php-ext-install sodium zip swoole

RUN curl -sS https://getcomposer.org/installer | php -- \
        --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# docker/Dockerfile.php

FROM php:8.3-fpm-alpine

RUN apk update && apk add --no-cache \
    git \
    unzip \
    acl \
    mysql-client \
    && rm -rf /var/cache/apk/*
RUN docker-php-ext-install pdo_mysql opcache
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
COPY . .

USER root

# RUN chown -R www-data:www-data /var/www/html/docker/db_setup.sh

RUN composer install --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html

USER www-data

CMD ["php-fpm"]
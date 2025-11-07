FROM php:8.3-fpm-alpine

# 1️⃣ Installer les dépendances système
RUN apk update && apk add --no-cache \
    git \
    unzip \
    acl \
    mysql-client \
    && rm -rf /var/cache/apk/*

RUN docker-php-ext-install pdo_mysql opcache

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY --chown=1000:1000 ./ /var/www/PriceManager

WORKDIR /var/www/PriceManager

COPY composer.json composer.lock ./

RUN chmod -R 775 composer.json composer.lock ./

COPY bin ./bin

RUN composer install --optimize-autoloader --no-interaction --no-progress || true

COPY . .

RUN mkdir -p /var/www/html/var /var/www/html/PriceManager \
    && chown -R 1000:1000 /var/www/PriceManager \
    && chmod -R 775 /var/www/PriceManager/var /var/www/PriceManager/vendor

RUN chmod -R 775 composer.json composer.lock ./

USER 1000:1000

CMD ["php-fpm"]

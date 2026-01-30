# syntax=docker/dockerfile:1

FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache \
    postgresql-dev \
    icu-dev \
    && docker-php-ext-install \
    pdo_pgsql \
    intl \
    opcache

WORKDIR /var/www/html

# Composer stage
FROM base AS composer

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY backend/composer.json backend/composer.lock ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY backend/ .

RUN composer dump-autoload --optimize --classmap-authoritative \
    && composer run-script post-install-cmd --no-interaction || true

# Production stage
FROM base AS production

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/backend/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/backend/www.conf /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer /var/www/html /var/www/html

RUN mkdir -p var/cache var/log config/jwt \
    && chown -R www-data:www-data var config/jwt

USER www-data

EXPOSE 9000

CMD ["php-fpm"]

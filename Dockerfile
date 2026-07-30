# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json* ./
RUN --mount=type=cache,target=/root/.npm npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM dunglas/frankenphp:1-php8.4 AS runtime

ARG APP_RELEASE=dev
ENV APP_RELEASE=${APP_RELEASE} \
    SERVER_NAME=:8080 \
    OCTANE_SERVER=frankenphp

RUN install-php-extensions \
        pdo_mysql \
        redis \
        opcache \
        intl \
        zip \
        bcmath \
        pcntl

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /app

COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

RUN php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rw storage bootstrap/cache

EXPOSE 8080

HEALTHCHECK --interval=10s --timeout=3s --start-period=20s --retries=5 \
    CMD curl -fsS http://127.0.0.1:8080/health || exit 1

ENTRYPOINT ["docker/entrypoint.sh"]

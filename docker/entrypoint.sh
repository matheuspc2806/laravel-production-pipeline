#!/bin/sh
set -eu

php artisan config:cache
php artisan route:cache
php artisan view:cache

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --isolated
fi

exec frankenphp php-server \
    --root /app/public \
    --listen "${SERVER_NAME}"

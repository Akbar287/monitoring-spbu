#!/bin/sh
set -e

mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

if [ "${CREATE_STORAGE_LINK:-true}" = "true" ]; then
    php artisan storage:link >/dev/null 2>&1 || true
fi

if [ "${LARAVEL_OPTIMIZE:-true}" = "true" ]; then
    php artisan config:cache
    php artisan view:cache
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"

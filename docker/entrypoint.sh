#!/bin/sh
set -e

mkdir -p \
    storage/app/public/profile_img \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

DEFAULT_PROFILE_IMAGE="/usr/local/share/monitoring-spbu/profile_img/default.png"
if [ -f "$DEFAULT_PROFILE_IMAGE" ] && [ ! -f storage/app/public/profile_img/default.png ]; then
    cp "$DEFAULT_PROFILE_IMAGE" storage/app/public/profile_img/default.png
fi

chown -R www-data:www-data storage bootstrap/cache

if [ "${CREATE_STORAGE_LINK:-true}" = "true" ]; then
    if [ -L public/storage ]; then
        link_target="$(readlink public/storage || true)"
        if [ "$link_target" != "/var/www/html/storage/app/public" ] && [ "$link_target" != "../storage/app/public" ]; then
            rm -f public/storage
        fi
    elif [ -e public/storage ]; then
        rmdir public/storage 2>/dev/null || true
    fi

    if [ ! -e public/storage ] && [ ! -L public/storage ]; then
        php artisan storage:link --force >/dev/null 2>&1 || ln -s /var/www/html/storage/app/public public/storage
    fi
fi

if [ "${LARAVEL_OPTIMIZE:-true}" = "true" ]; then
    php artisan config:cache
    php artisan view:cache
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"

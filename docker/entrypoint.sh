#!/bin/sh
set -e

mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

PUBLIC_STORAGE_SEED="/usr/local/share/monitoring-spbu/storage-public"
if [ -d "$PUBLIC_STORAGE_SEED" ]; then
    find "$PUBLIC_STORAGE_SEED" -type d | while IFS= read -r dir; do
        rel="${dir#"$PUBLIC_STORAGE_SEED"}"
        mkdir -p "storage/app/public$rel"
    done

    find "$PUBLIC_STORAGE_SEED" -type f | while IFS= read -r file; do
        rel="${file#"$PUBLIC_STORAGE_SEED"/}"
        target="storage/app/public/$rel"
        if [ ! -e "$target" ]; then
            mkdir -p "$(dirname "$target")"
            cp "$file" "$target"
        fi
    done
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

# syntax=docker/dockerfile:1

FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

FROM php:8.2-fpm-alpine AS app

WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        curl \
        freetype \
        icu-libs \
        libjpeg-turbo \
        libpng \
        libzip \
        nginx \
        oniguruma \
        postgresql-libs \
        supervisor \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        pgsql \
        zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --optimize-autoloader

COPY . .
COPY storage/app/public /usr/local/share/monitoring-spbu/storage-public
COPY --from=assets /app/public/build ./public/build
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint

RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && cp -a /usr/local/share/monitoring-spbu/storage-public/. storage/app/public/ \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 80

ENTRYPOINT ["docker-entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]

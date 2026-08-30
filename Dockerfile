FROM node:22-bookworm-slim AS frontend-build

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci --legacy-peer-deps

COPY frontend/ ./

ARG VITE_FILING_ICP_TEXT=""
ARG VITE_FILING_ICP_URL=""
ARG VITE_FILING_PSB_TEXT=""
ARG VITE_FILING_PSB_URL=""
ENV VITE_FILING_ICP_TEXT=${VITE_FILING_ICP_TEXT} \
    VITE_FILING_ICP_URL=${VITE_FILING_ICP_URL} \
    VITE_FILING_PSB_TEXT=${VITE_FILING_PSB_TEXT} \
    VITE_FILING_PSB_URL=${VITE_FILING_PSB_URL}

RUN npm run build

FROM composer:2 AS vendor-build

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

FROM php:8.4-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        libonig-dev \
        libzip-dev \
        nginx \
        supervisor \
        $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" bcmath mbstring pdo_mysql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY lang ./lang
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY artisan composer.json composer.lock ./
COPY --from=vendor-build /app/vendor ./vendor
COPY --from=frontend-build /app/dist/ ./public/

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/classmanager.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/classmanager.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zzz-classmanager.conf
COPY docker/entrypoint.sh /usr/local/bin/classmanager-entrypoint

RUN php artisan package:discover --ansi \
    && chmod +x /usr/local/bin/classmanager-entrypoint \
    && mkdir -p \
        storage/app/public \
        storage/files/activity/homework \
        storage/files/activity/output \
        storage/files/activity/publish \
        storage/files/activity/temp \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 4157

ENTRYPOINT ["classmanager-entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]

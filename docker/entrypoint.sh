#!/bin/sh
set -eu

mkdir -p \
    storage/app/public \
    storage/files/activity/homework \
    storage/files/activity/output \
    storage/files/activity/publish \
    storage/files/activity/temp \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

chown -R www-data:www-data storage bootstrap/cache

if [ ! -L public/storage ]; then
    php artisan storage:link
fi

php artisan config:cache

exec "$@"

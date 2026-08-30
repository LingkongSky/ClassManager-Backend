#!/bin/sh
set -eu

app_listen_port=${APP_LISTEN_PORT:-4157}
case "$app_listen_port" in
    ''|*[!0-9]*)
        echo 'APP_LISTEN_PORT must be a numeric TCP port.' >&2
        exit 1
        ;;
esac

sed -i "s/listen 80 default_server;/listen ${app_listen_port} default_server;/" /etc/nginx/nginx.conf

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

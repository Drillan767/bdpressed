#!/usr/bin/env bash
php artisan config:cache
php artisan migrate --force
php artisan db:seed
php-fpm -D
caddy run --config /srv/ci/Caddyfile

#!/usr/bin/env bash
php-fpm -D
caddy run --config /var/www/html/ci/Caddyfile

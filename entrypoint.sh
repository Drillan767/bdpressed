#!/bin/sh

# Start PHP-FPM in the background
php-fpm -D

# Start Caddy in the foreground
exec caddy run --config /etc/caddy/Caddyfile

#!/usr/bin/env bash
php-fpm -D
caddy run --config /srv/ci/Caddyfile

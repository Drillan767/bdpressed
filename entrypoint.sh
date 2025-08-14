#!/bin/sh

# Exit on any error
set -e

# Create necessary directories
# mkdir -p /var/www/html/storage/logs
# mkdir -p /var/log/supervisor

# Set proper permissions
# chown -R www-data:www-data /var/www/html/storage
# chown -R www-data:www-data /var/www/html/bootstrap/cache

# Run Laravel optimizations (only once)
# echo "Running Laravel optimizations..."
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

# Run database migrations
# php artisan migrate --force

# Clear and warm up caches
# php artisan queue:clear
# php artisan queue:restart

# Ensure log file exists for queue workers
# touch /var/www/html/storage/logs/worker.log
# chown www-data:www-data /var/www/html/storage/logs/worker.log

# echo "Starting services..."

# Start php-fpm in background
# php-fpm --daemonize

# Start queue workers in background
# php artisan queue:work --daemon --sleep=3 --tries=3 &

# Start Caddy (this keeps the container alive)
php-fpm -D

# Start Caddy in the foreground
exec caddy run --config /etc/caddy/Caddyfile

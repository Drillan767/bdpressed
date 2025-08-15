#!/bin/sh

# Exit on any error
set -e

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

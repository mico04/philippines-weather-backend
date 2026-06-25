#!/usr/bin/env bash
set -e

cd /var/www/html

echo "Caching config..."
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

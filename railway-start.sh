#!/bin/bash
set -e

if [ ! -f /app/database/database.sqlite ]; then
    echo "Creating database file..."
    mkdir -p /app/database
    touch /app/database/database.sqlite
    chmod 666 /app/database/database.sqlite
fi

php artisan migrate --force
php artisan db:seed --force

# Clear semua cache sebelum start
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache ulang
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan serve --host=0.0.0.0 --port=$PORT

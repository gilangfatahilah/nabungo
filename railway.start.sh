#!/bin/bash
set -e

# Ensure database exists
if [ ! -f /app/database/database.sqlite ]; then
    echo "Creating database file..."
    mkdir -p /app/database
    touch /app/database/database.sqlite
    chmod 666 /app/database/database.sqlite
fi

# Run migrations
php artisan migrate --force

# Run seeders
php artisan db:seed --force

# Start server
php artisan serve --host=0.0.0.0 --port=$PORT

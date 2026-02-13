#!/bin/bash
set -e

echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm ci
npm run build

echo "Setting up database..."
mkdir -p database
touch database/database.sqlite
chmod 777 database/database.sqlite

echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Build completed!"

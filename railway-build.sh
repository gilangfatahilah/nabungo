#!/bin/bash
set -e

echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm ci
npm run build

echo "Setting up database connection..."
# PostgreSQL doesn't need file creation
# Database should be provisioned by Railway

echo "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Build completed!"

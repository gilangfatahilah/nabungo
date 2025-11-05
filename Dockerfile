  FROM php:8.3-fpm AS base

  RUN apt-get update && apt-get install -y \
      git curl zip unzip sqlite3 libsqlite3-dev \
      && docker-php-ext-install pdo pdo_sqlite

  COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
  WORKDIR /var/www/html

  FROM node:22 AS node
  WORKDIR /app
  COPY . .
  RUN npm ci && npm run build

  FROM php:8.3-cli AS production

  RUN apt-get update && apt-get install -y sqlite3 libsqlite3-dev \
      && docker-php-ext-install pdo pdo_sqlite

  WORKDIR /var/www/html
  COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
  COPY . .
  COPY --from=node /app/public/build ./public/build

  RUN composer install --no-dev --optimize-autoloader

  RUN php artisan config:cache \
      && php artisan route:cache \
      && php artisan view:cache \
      && chown -R www-data:www-data storage bootstrap/cache \
      && chmod -R 775 storage bootstrap/cache

  EXPOSE 8000

  CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

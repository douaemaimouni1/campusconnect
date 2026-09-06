# Étape 1 : construire les assets frontend (CSS/JS)
FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Étape 2 : préparer l'application PHP
FROM php:8.4-cli-alpine
WORKDIR /var/www/html

RUN apk add --no-cache \
    git curl libpng-dev libzip-dev zip unzip icu-dev oniguruma-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath gd zip intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction

EXPOSE 10000
CMD php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
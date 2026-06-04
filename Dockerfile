# syntax=docker/dockerfile:1.7
#
# Hey, Alter! Essen — Computerverwaltung
# Single-Image (PHP 8.4 + Apache) für einfache Bereitstellung.
#
# Aufbau:
#   1. composer-Stage:  PHP-Abhängigkeiten installieren
#   2. node-Stage:      Frontend-Assets bauen (Vite)
#   3. runtime-Stage:   PHP + Apache, alles zusammenführen

# -------- Stage 1: Composer --------
FROM composer:2 AS composer

WORKDIR /app
COPY composer.json composer.lock ./
# Code wird in der Runtime-Stage gemerged — hier nur Pakete laden
RUN composer install --no-dev --prefer-dist --no-interaction \
    --no-scripts --no-autoloader --no-progress

# -------- Stage 2: Node / Vite --------
FROM node:20-alpine AS node

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY resources ./resources
COPY public ./public
COPY vite.config.js tailwind.config.js postcss.config.js* ./

RUN npm run build

# -------- Stage 3: Runtime --------
FROM php:8.4-apache AS runtime

# System-Pakete für die PHP-Extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libsodium-dev \
        default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        bcmath \
        gd \
        zip \
        intl \
        sodium \
        exif \
        pcntl \
    && rm -rf /var/lib/apt/lists/*

# Apache: Document-Root auf /public + mod_rewrite
RUN a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# PHP-Konfiguration
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# Composer in den Runtime-Container kopieren (für Wartung)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 1. Vendor-Verzeichnis aus der Composer-Stage
COPY --from=composer /app/vendor ./vendor

# 2. Anwendungscode
COPY . .

# 3. Frontend-Build aus der Node-Stage
COPY --from=node /app/public/build ./public/build

# 4. Autoload regenerieren + Optimierungen
RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+w storage bootstrap/cache

# Entrypoint kümmert sich um Warten auf DB, Migrate, Cache-Aufbau, Apache-Start
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]

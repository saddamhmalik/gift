# ── Stage 1: PHP + Composer (Laravel app) ───────────────────────────────────
FROM php:8.3-cli AS phpbase

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install zip pdo_mysql mbstring exif pcntl bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --ignore-platform-reqs

# ── Stage 2: Vite build (admin Blade UI needs public/build/manifest.json) ──
FROM node:22-alpine AS assets
WORKDIR /var/www
# Full tree so Tailwind @source paths (vendor, resources, storage views) resolve like local `npm run build`
COPY --from=phpbase /var/www .
RUN npm ci && npm run build

# ── Stage 3: Final image ────────────────────────────────────────────────────
FROM phpbase

COPY --from=assets /var/www/public/build ./public/build

COPY docker/backend-entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]

#!/bin/sh
set -e
cd /var/www

# Ensure storage is writable
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Migrations must succeed for SESSION_DRIVER=database and CACHE_STORE=database (see .env)
if ! php artisan migrate --force; then
  echo "gift-backend: migrate failed — check DB_HOST/DB_DATABASE and MySQL logs (docker compose logs mysql)" >&2
  exit 1
fi

# Default admin + settings rows (idempotent — seeders use updateOrCreate)
php artisan db:seed --force || echo "gift-backend: warning: db:seed failed — run: php artisan db:seed" >&2

# Clear stale Horizon workers
php artisan horizon:terminate 2>/dev/null || true

# Start Horizon in background
php artisan horizon &

# Start Laravel server (foreground)
exec php artisan serve --host=0.0.0.0 --port=8000
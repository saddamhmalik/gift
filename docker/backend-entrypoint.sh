#!/bin/sh
set -e
cd /var/www

# Ensure storage is writable
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Run migrations
php artisan migrate --force 2>/dev/null || true

# Clear stale Horizon workers
php artisan horizon:terminate 2>/dev/null || true

# Start Horizon in background
php artisan horizon &

# Start Laravel server (foreground)
exec php artisan serve --host=0.0.0.0 --port=8000
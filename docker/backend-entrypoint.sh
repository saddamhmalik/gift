#!/bin/sh
set -e
cd /var/www
# Ensure storage is writable
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
# Run migrations (MySQL is waited for via depends_on; sqlite is local)
php artisan migrate --force 2>/dev/null || true
exec php artisan serve --host=0.0.0.0 --port=8000

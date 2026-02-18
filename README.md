# Gift Box API

Laravel API for a gift cards and vouchers platform. Users browse products, place orders (one product per order), and after payment the backend fulfills via **Woohoo (QwikGift)**.

## Docs

- **[Project overview](docs/OVERVIEW.md)** — What it is, stack, setup, run, main features.
- **[Documentation](docs/DOCUMENTATION.md)** — Full technical docs: API reference, Order API, React Google OAuth, Woohoo, payment webhook, Postman.

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

API base: **http://localhost:8000**

For auth (orders require login): `composer require laravel/sanctum` and publish/migrate as per [OVERVIEW](docs/OVERVIEW.md).

### Test order flow

```bash
# 1. Ensure server is running: php artisan serve
# 2. Add TEST_EMAIL and TEST_PASSWORD to .env (or use a registered user)
# 3. Sync products: php artisan woohoo:fetch-products
./scripts/test-order-flow.sh
```

## License

MIT.

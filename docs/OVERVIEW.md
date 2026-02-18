# Gift Box — Project Overview

## What It Is

**Gift Box** is a Laravel API for a gift cards and vouchers platform (in the spirit of gyftr.com). Users browse categories and products, add a product to their order, and after payment the backend fulfills the order via **Woohoo (QwikGift)** to create and deliver the gift card.

## Stack

- **Backend:** Laravel 12, PHP 8.2+
- **Auth:** Laravel Sanctum (API tokens)
- **Database:** SQLite (default), or MySQL/PostgreSQL
- **Frontend:** React (separate app); API is backend-only

## Requirements

- PHP 8.2+
- Composer
- Node/npm (only if you run a React frontend)

## Quick Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
# Optional: Sanctum for API auth
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## Run

```bash
php artisan serve
```

API base: **http://localhost:8000**

## Main Features

| Area | Description |
|------|-------------|
| **Categories & products** | Public APIs to list categories, products (hot deals, trending, featured, etc.), and product detail. Products come from Woohoo catalog sync. |
| **Auth** | Register, login, OTP, Google (Gmail) login. Returns Sanctum token for protected routes. |
| **Orders** | One product per order. Create/fetch order, set/update/clear item. Price from product min/max or denominations (RANGE vs SLAB). **All order endpoints require a logged-in user.** |
| **Payment** | User pays via your payment gateway (to be integrated). On success, your backend calls the fulfillment webhook; backend then creates the Woohoo order (SVC wallet) and optionally polls for card details. |
| **Woohoo** | OAuth2 for Woohoo API; catalog sync (categories/products); Order Create API (SVC); Order Details/Status for async orders. |

## Project Structure

```
app/
  Http/Controllers/Api/V1/   # API controllers (Auth, Order, Product, Category, Webhook)
  Models/                     # User, Order, OrderItem, Product, Category, etc.
  Services/                   # Order, Woohoo (client, order create, status), Woohoo sync
  Repositories/
config/                       # woohoo, cors, sanctum, etc.
database/migrations/
docs/                         # OVERVIEW.md, DOCUMENTATION.md, Postman
routes/api.php                # All API routes under /api/v1
```

## Documentation

- **[DOCUMENTATION.md](DOCUMENTATION.md)** — Full technical docs: API reference, Order API, React Google OAuth, Woohoo, payment webhook, Postman.

## License

MIT.

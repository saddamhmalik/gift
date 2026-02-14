# Gift Box API

Backend-only Laravel 12 API replica of [gyftr.com](https://www.gyftr.com) — gift cards & vouchers platform.

## Requirements

- PHP 8.2+
- Composer
- SQLite (default) or MySQL/PostgreSQL

## Setup

```bash
# Install dependencies (run when you have network; Sanctum may need composer update)
composer install

# Copy env and generate key
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# (Optional) Install Laravel Sanctum for API token auth (orders, /api/user)
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## Run

```bash
php artisan serve
```

API base: **http://localhost:8000**

## API Endpoints

All API routes are under `/api`. Versioned under `/api/v1`.

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/health` | No | Health check |
| GET | `/api/v1/categories` | No | List categories (optional: `?with_brands=1`) |
| GET | `/api/v1/categories/{id}` | No | Category detail with brands |
| GET | `/api/v1/brands` | No | List brands (`?category_id=`, `?with_category=1`, `?with_vouchers=1`) |
| GET | `/api/v1/brands/{id}` | No | Brand detail with vouchers |
| GET | `/api/v1/vouchers` | No | List vouchers (`?brand_id=`, `?category_id=`, `?with_brand=1`) |
| GET | `/api/v1/vouchers/{id}` | No | Voucher detail |
| GET | `/api/user` | Sanctum | Current user |
| GET | `/api/v1/orders` | Sanctum | My orders |
| GET | `/api/v1/orders/{id}` | Sanctum | Order detail |
| POST | `/api/v1/orders` | Sanctum | Create order (voucher_id, amount, quantity?, recipient_email?, message?) |
## Woohoo (QwikGift) OAuth2

Gift Box includes **Woohoo OAuth2** for obtaining a Bearer token (woohoo.in / Qwikcilver QwikGift API).

### Flow

1. **Get authorization code** — `POST <base_url>/oauth2/verify` with `clientId`, `username`, `password`.
2. **Get Bearer token** — `POST <base_url>/oauth2/token` with `clientId`, `clientSecret`, `authorizationCode`.
3. Token is cached (default 7 days).

### Configuration (.env)

```env
WOOHOO_BASE_URL=https://sandbox.woohoo.in
WOOHOO_CLIENT_ID=your_client_id
WOOHOO_CLIENT_SECRET=your_client_secret
WOOHOO_USERNAME=your_username
WOOHOO_PASSWORD=your_password
```

**CloudFront 403:** If you get HTTP 403 from CloudFront, ask Qwikcilver to whitelist your server IP. Try `WOOHOO_USER_AGENT` (e.g. browser-like UA) if they suggest it.

### CLI

```bash
# Get and cache Bearer token
php artisan giftbox:woohoo-token

# Force new token (ignore cache)
php artisan giftbox:woohoo-token --fresh

# Show token
php artisan giftbox:woohoo-token --show
php artisan giftbox:woohoo-token --fresh --show
```

## Data model (gyftr-style)

- **Categories** — e.g. E-Commerce, Entertainment, Food
- **Brands** — e.g. Amazon, Netflix (belong to a category)
- **Vouchers** — gift card products (brand, min/max value, fixed denominations)
- **Orders** — user purchases (voucher, amount, quantity, status, optional recipient_email/message)

## Project structure

- `app/Http/Controllers/Api/V1/` — API controllers
- `app/Models/` — Category, Brand, Voucher, Order, User
- `routes/api.php` — API routes
- Migrations for categories, brands, vouchers, orders

## License

MIT.

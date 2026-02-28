# Docker deployment (PayFlex / Gift)

## Current setup (IP)

- **Frontend:** http://13.202.3.204 (port 80)
- **Backend API:** http://13.202.3.204:8000 (port 8000)

The frontend nginx proxies `/api` to the backend, so the app at port 80 works without CORS.

## Quick start on server (13.202.3.204)

1. **Copy env and set APP_KEY**
   ```bash
   cp env.docker.example .env
   # Generate APP_KEY (run on server):
   docker run --rm php:8.2-cli php -r "echo 'APP_KEY=base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
   # Add the output line to .env
   ```

2. **Build and run**
   ```bash
   docker compose up -d --build
   ```

3. **Open**
   - App: http://13.202.3.204
   - API: http://13.202.3.204:8000

## Using a domain and subdomain later

1. **Point DNS**
   - Frontend: e.g. `app.yourdomain.com` or `yourdomain.com` → server IP 13.202.3.204
   - Backend: e.g. `api.yourdomain.com` → same IP (or same server)

2. **Set URLs in `.env`**
   ```env
   APP_URL=https://api.yourdomain.com
   FRONTEND_URL=https://app.yourdomain.com
   ```
   (Use `http://` if you are not on HTTPS yet.)

3. **Optional: HTTPS with a reverse proxy**

   Run a reverse proxy (e.g. Caddy or Nginx) on the host that:
   - Terminates SSL for `app.yourdomain.com` and `api.yourdomain.com`
   - Proxies to Docker:
     - `app.yourdomain.com` → `http://127.0.0.1:80` (frontend)
     - `api.yourdomain.com` → `http://127.0.0.1:8000` (backend)

   Then in `.env` use `https://` for both URLs and restart:
   ```bash
   docker compose down && docker compose up -d
   ```

4. **If the frontend is built with a fixed API URL**

   The image is built with `VITE_API_URL=/api/v1`, so the browser talks to the same origin and nginx proxies to the backend. No rebuild is needed when you add a domain.

   If you ever build the frontend with an absolute API URL (e.g. `VITE_API_URL=https://api.yourdomain.com/api/v1`), rebuild the frontend image after setting the domain and restart:
   ```bash
   docker compose build frontend --build-arg VITE_API_URL=https://api.yourdomain.com/api/v1
   docker compose up -d
   ```

## Data persistence

- **Storage and cache** are in Docker volumes and persist across restarts.
- **MySQL** data is stored in the `mysql_data` volume and persists across restarts. Default credentials in `.env`: `DB_USERNAME=laravel`, `DB_PASSWORD=secret` (change in production). The backend runs `php artisan migrate --force` on startup.

## Optional: Redis (Horizon / queue)

1. In `docker-compose.yml`, uncomment the `redis` service and volume.
2. In `.env` set `QUEUE_CONNECTION=redis`.
3. Run the queue worker (e.g. Horizon) in the backend container:
   ```bash
   docker compose exec backend php artisan horizon
   ```
   Or add a separate service that runs the same backend image with `CMD ["php", "artisan", "horizon"]`.

## Ports

- Change host ports via `.env`: `FRONTEND_PORT=80`, `BACKEND_PORT=8000` (defaults shown in `docker-compose.yml`).

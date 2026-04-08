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

## Redis (Horizon / queue)

The stack includes a `redis` service. Set `QUEUE_CONNECTION=redis` in `.env`. The backend entrypoint starts **Horizon** in the background; you can also run `docker compose exec backend php artisan horizon` manually if needed.

## Ports

- Change host ports via `.env`: `FRONTEND_PORT=80`, `BACKEND_PORT=8000` (defaults shown in `docker-compose.yml`).

## Docker troubleshooting

1. **Image build: TLS handshake timeout to Docker Hub** — Network/VPN/DNS issue reaching `registry-1.docker.io`. Retry, change network, or `docker login`. Not an application bug.

2. **Backend container exits or restarts** — Migrations must succeed. The entrypoint runs `php artisan migrate --force` and exits on failure. Check `docker compose logs backend` and MySQL (`docker compose logs mysql`). Ensure `DB_HOST=mysql`, `DB_PASSWORD` matches `MYSQL_PASSWORD`, and `DB_DATABASE` matches what MySQL created on first run.

3. **`.env` for Compose** — Use `REDIS_HOST=redis` and `DB_HOST=mysql` when services run inside Docker (not `127.0.0.1`). The compose file also sets these if omitted.

4. **`CACHE_STORE=database` / `SESSION_DRIVER=database`** — Require migrations (tables `cache`, `sessions`, etc.). If you change DB name or wipe MySQL volume, run `docker compose exec backend php artisan migrate --force`.

5. **Horizon** — The backend entrypoint already starts Horizon in the background. Ensure `QUEUE_CONNECTION=redis` and Redis is healthy.

6. **`/admin/login` returns 500 or blank** — Admin Blade views use **Vite** (`@vite`). The Docker image runs **`npm run build`** in a Node stage and copies **`public/build`** into the final image. Rebuild the backend: `docker compose build backend --no-cache`. For **local** `php artisan serve` (no Docker), run **`npm run build`** once so `public/build` exists, or run **`npm run dev`** in another terminal.

7. **Localhost vs server IP** — If you open `http://localhost:8000` but `.env` has `APP_URL=http://13.202.3.204:8000`, set `APP_URL=http://localhost:8000` for local testing so sessions and redirects behave correctly.

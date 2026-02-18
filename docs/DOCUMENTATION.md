# Gift Box — Documentation

Single reference for all technical documentation.

---

## Table of contents

1. [API endpoints summary](#1-api-endpoints-summary)
2. [Order API](#2-order-api)
3. [React + Google (Gmail) login](#3-react--google-gmail-login)
4. [Woohoo (QwikGift)](#4-woohoo-qwikgift)
5. [Payment & webhook](#5-payment--webhook)
6. [Postman](#6-postman)

---

## 1. API endpoints summary

Base URL: `/api/v1`. All order and payment-related routes require **Bearer token** (Sanctum).

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/health` | No | Health check |
| GET | `/categories` | No | List categories |
| GET | `/categories/{slug}` | No | Category detail |
| GET | `/products/hot-deals` | No | Hot deals |
| GET | `/products/trending` | No | Trending products |
| GET | `/products/best-sellers` | No | Best sellers |
| GET | `/products/featured` | No | Featured products |
| GET | `/products/new-arrivals` | No | New arrivals |
| GET | `/products/{product}` | No | Product detail |
| POST | `/auth/register` | No | Register |
| POST | `/auth/login` | No | Login |
| POST | `/auth/otp/send` | No | Send OTP |
| POST | `/auth/otp/verify` | No | Verify OTP |
| POST | `/auth/google` | No | Google login (send `access_token`) |
| POST | `/auth/logout` | **Sanctum** | Logout |
| GET | `/auth/me` | **Sanctum** | Current user |
| POST | `/order` | **Sanctum** | Create/fetch order |
| GET | `/order` | **Sanctum** | Get current order |
| POST | `/order/item` | **Sanctum** | Set product on order |
| PUT | `/order/item` | **Sanctum** | Update quantity/price |
| DELETE | `/order/item` | **Sanctum** | Clear order item |
| POST | `/webhooks/payment-success` | No (secure with secret) | Payment success → Woohoo fulfill |

---

## 2. Order API

### Overview

One product per order. User selects **price** from the product’s **min_price**, **max_price**, or **denominations** (see **Price types**: RANGE vs SLAB below). **All order endpoints require a logged-in user** (Bearer token).

### Authentication

- **Order and payment routes are protected.** Send `Authorization: Bearer <token>` (Sanctum) on every request.
- Unauthenticated requests to order or payment endpoints return `401 Unauthorized`.
- Obtain a token via `POST /api/v1/auth/login`, register, or your auth flow.

### Order flow

1. **Create/fetch order**: `POST /api/v1/order` (auth required) → returns current user’s pending order.
2. **Set product**: `POST /api/v1/order/item` with `product_id`, `quantity`, and either:
   - `unit_price` (must satisfy product price rules: RANGE or SLAB), or
   - `selected_denomination` (value from product denominations/slabs, e.g. `"500"`).
3. **Update**: `PUT /api/v1/order/item` to change quantity or price.
4. **Clear**: `DELETE /api/v1/order/item` to remove the product from the order.
5. **Payment**: After payment success (via your gateway), your backend calls the fulfillment webhook or service to create the Woohoo order.

### Price types

- **RANGE** (`price_type === "RANGE"`): Any value between `min_price` and `max_price` (inclusive) is allowed.
- **SLAB** (`price_type === "SLAB"`): Only values in `denominations` (price slabs) are allowed. Send `unit_price` equal to a slab value or use `selected_denomination`.

### Order APIs

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/order` | **Yes** (Bearer) | Create or fetch current user’s order |
| GET | `/api/v1/order` | **Yes** (Bearer) | Get current user’s order |
| POST | `/api/v1/order/item` | **Yes** (Bearer) | Set the single product (`product_id`, `quantity`, `unit_price` or `selected_denomination`) |
| PUT | `/api/v1/order/item` | **Yes** (Bearer) | Update quantity and/or price |
| DELETE | `/api/v1/order/item` | **Yes** (Bearer) | Remove product from order |

### Request examples

**Headers (required for all order endpoints):**
```
Authorization: Bearer <your_sanctum_token>
Content-Type: application/json
```

**Set product with specific price (RANGE: any value in min–max):**
```json
POST /api/v1/order/item
{ "product_id": 1, "quantity": 2, "unit_price": 500 }
```

**Set product with denomination/slab (SLAB: only allowed values):**
```json
POST /api/v1/order/item
{ "product_id": 1, "quantity": 1, "selected_denomination": "1000" }
```

### Edge cases

- **Unauthenticated**: 401 on any order endpoint without a valid Bearer token.
- **Invalid price**: 422 if `unit_price` does not satisfy RANGE (min–max) or SLAB (must be in denominations).
- **Invalid denomination**: 422 if `selected_denomination` is not in product denominations (slabs).
- **Only one product**: Setting a new product replaces the existing one.
- **No order**: 404 with message to create order first (POST /order).

---

## 3. React + Google (Gmail) login

### Overview

1. User clicks "Sign in with Google" in your React app.
2. `@react-oauth/google` handles the Google OAuth popup/redirect.
3. Frontend receives a Google `access_token`.
4. Frontend sends `access_token` to our API `POST /api/v1/auth/google`.
5. Backend validates token with Google, creates/finds user, returns Sanctum token.
6. Frontend stores Sanctum token and uses it for protected APIs (checkout, orders).

### Prerequisites

- React app (Vite, CRA, or Next.js)
- Google Cloud Console project with OAuth 2.0 credentials
- Gift Box API running (e.g. `http://localhost:8000`)

### Step 1: Google Cloud Console setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/).
2. Create or select a project.
3. **APIs & Services** → **Credentials** → **Create Credentials** → **OAuth client ID**.
4. Application type: **Web application**.
5. Add **Authorized JavaScript origins**:
   - `http://localhost:5173` (Vite default)
   - `http://localhost:3000` (CRA default)
   - Your production domain (e.g. `https://yourdomain.com`)
6. Add **Authorized redirect URIs** (if using redirect flow):
   - `http://localhost:5173` (or your app URL)
7. Copy **Client ID** and **Client Secret**.

### Step 2: Backend configuration

Add to your Laravel `.env`:

```env
GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_client_secret
```

The backend uses these to validate the `access_token` sent by the frontend.

### Step 3: Install @react-oauth/google

```bash
npm install @react-oauth/google
```

### Step 4: Wrap your app with GoogleOAuthProvider

In your root component (e.g. `main.jsx` or `App.jsx`):

```jsx
import { GoogleOAuthProvider } from '@react-oauth/google';

const clientId = import.meta.env.VITE_GOOGLE_CLIENT_ID; // or process.env.REACT_APP_GOOGLE_CLIENT_ID

function App() {
  return (
    <GoogleOAuthProvider clientId={clientId}>
      <YourApp />
    </GoogleOAuthProvider>
  );
}
```

Create `.env` in your React project:

```env
VITE_GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
```

### Step 5: Google login button component

```jsx
import { useGoogleLogin } from '@react-oauth/google';

const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export function GoogleLoginButton({ onSuccess, onError }) {
  const login = useGoogleLogin({
    onSuccess: async (tokenResponse) => {
      try {
        const res = await fetch(`${API_BASE}/api/v1/auth/google`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ access_token: tokenResponse.access_token }),
        });
        const data = await res.json();

        if (!data.success) {
          onError?.(data.message || 'Login failed');
          return;
        }

        const { token, user } = data.data;
        localStorage.setItem('sanctum_token', token);
        onSuccess?.({ token, user });
      } catch (err) {
        onError?.(err.message || 'Network error');
      }
    },
    onError: (error) => onError?.(error?.error_description || 'Google login failed'),
    flow: 'implicit', // Returns access_token directly (no backend redirect needed)
  });

  return (
    <button type="button" onClick={() => login()}>
      Sign in with Google
    </button>
  );
}
```

### Step 6: Storing the Sanctum token

After successful login, store the token:

```js
localStorage.setItem('sanctum_token', data.data.token);
```

For better security in production, consider:
- `httpOnly` cookies (requires backend CORS/cookie config)
- Secure storage (e.g. encrypted sessionStorage)

### Step 7: Using the token in protected APIs

Create an API client:

```js
const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000';

export function apiClient() {
  const token = localStorage.getItem('sanctum_token');

  return {
    get: (path) =>
      fetch(`${API_BASE}${path}`, {
        headers: token ? { Authorization: `Bearer ${token}` } : {},
      }),
    post: (path, body) =>
      fetch(`${API_BASE}${path}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
        body: JSON.stringify(body),
      }),
  };
}
```

Example: Get current user (protected):

```js
const client = apiClient();
const res = await client.get('/api/v1/auth/me');
const data = await res.json();
if (data.success) {
  console.log('User:', data.data);
}
```

### Step 8: Checkout-based login flow

1. User browses products (no login required).
2. User adds to cart.
3. User clicks "Checkout".
4. If not logged in → show login modal with Email + Password, Google (Gmail), Mobile OTP.
5. After login → redirect to checkout with token.
6. Checkout API calls use `Authorization: Bearer <token>`.

Example flow:

```jsx
function CheckoutPage() {
  const [user, setUser] = useState(null);
  const token = localStorage.getItem('sanctum_token');

  useEffect(() => {
    if (!token) return;
    apiClient().get('/api/v1/auth/me')
      .then((r) => r.json())
      .then((data) => {
        if (data.success) setUser(data.data);
      });
  }, [token]);

  if (!token) {
    return <LoginModal onSuccess={() => window.location.reload()} />;
  }

  return <CheckoutForm user={user} />;
}
```

### Flow diagram

```
┌─────────────┐     ┌──────────────────┐     ┌─────────────┐
│   React     │     │  Google OAuth    │     │  Gift Box   │
│   App       │     │  (@react-oauth)  │     │  API        │
└──────┬──────┘     └────────┬─────────┘     └──────┬──────┘
       │                     │                      │
       │  Click "Sign in"    │                      │
       │────────────────────>│                      │
       │  Popup / Redirect   │                      │
       │<────────────────────│                      │
       │  access_token       │<────────────────────│
       │  POST /auth/google  │  { access_token }    │
       │──────────────────────────────────────────>│
       │  { token, user }    │                      │
       │<──────────────────────────────────────────│
       │  Store token, use Authorization: Bearer   │
```

### Troubleshooting (Google OAuth)

- **Invalid Google token:** Ensure `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` match the Google Cloud project. Token may be expired (~1 hour).
- **CORS errors:** Laravel CORS config must allow your frontend origin (`config/cors.php`).
- **Popup blocked:** Use `flow: 'implicit'` for popup; ensure login is triggered by user click.

---

## 4. Woohoo (QwikGift)

### OAuth2

Gift Box uses **Woohoo OAuth2** to obtain a Bearer token for the Woohoo (Qwikcilver QwikGift) API.

**Flow:**

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
WOOHOO_ORG_SHORT_CODE=ONEZERO
```

**CloudFront 403:** If you get HTTP 403 from CloudFront, ask Qwikcilver to whitelist your server IP. Try `WOOHOO_USER_AGENT` if they suggest it.

### CLI

```bash
# Get and cache Bearer token
php artisan giftbox:woohoo-token

# Force new token (ignore cache)
php artisan giftbox:woohoo-token --fresh

# Show token
php artisan giftbox:woohoo-token --show
```

### Order Create (SVC) & fulfillment

- After **payment success**, the backend creates a Woohoo order using **SVC (wallet)** payment.
- **Endpoint:** `POST /rest/v3/orders` with `payments: [{ code: "svc", amount, poNumber, mode: "ANY" }]`, `deliveryMode: "API"`, and a unique `refno` (e.g. `ORGCODE_YYYYMMDD_SEQ`).
- **Sync vs async:** `syncOnly: true` → 201 and card details in response (store encrypted). `syncOnly: false` → 202 and poll Order Details API until COMPLETE.
- **Error codes:** 5313 (duplicate refno), 6063 (insufficient balance), 5035 (SVC not enabled).
- Fulfillment is triggered by the payment-success webhook or by calling `FulfillOrderViaWoohooService` after your gateway confirms payment.

### Order Details API

- **GET /rest/v3/orders/{order_id}** — Fetch order details (status, products, address, billing, etc.).
- **Statuses:** PENDING, PROCESSING, CANCELED, COMPLETE. Use for polling async orders.

---

## 5. Payment & webhook

- **Initiate payment:** Handled by your frontend and payment gateway (protected routes when you add them).
- **Payment success webhook:** `POST /api/v1/webhooks/payment-success` is called **server-to-server** by your payment gateway. It does not use user Bearer tokens. **Secure it with your gateway’s webhook secret/signature.**

**Webhook body (example):**

- `order_id` or `order_token` — to identify the order.
- Optional: `billing` (email, telephone required if not already on order), `address`, `sync_only`.

On success, the backend creates the Woohoo order (SVC) and, for async orders, dispatches a job to poll Order Status and store card details when ready.

---

## 6. Postman

- Collection: **docs/postman/Gift-Box-API.postman_collection.json**
- **Variables:** `base_url` (e.g. `http://localhost:8000`), `token` (Sanctum token).
- **Order APIs** require `Authorization: Bearer {{token}}`. Set `token` from the Login response before calling order endpoints.

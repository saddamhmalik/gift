# React + Google (Gmail) Login Integration

This guide explains how to integrate Google (Gmail) login in a React frontend with the Gift Box Laravel API using Sanctum tokens.

## Overview

1. User clicks "Sign in with Google" in your React app
2. `@react-oauth/google` handles the Google OAuth popup/redirect
3. Frontend receives a Google `access_token`
4. Frontend sends `access_token` to our API `POST /api/v1/auth/google`
5. Backend validates token with Google, creates/finds user, returns Sanctum token
6. Frontend stores Sanctum token and uses it for protected APIs (checkout, orders)

## Prerequisites

- React app (Vite, CRA, or Next.js)
- Google Cloud Console project with OAuth 2.0 credentials
- Gift Box API running (e.g. `http://localhost:8000`)

---

## Step 1: Google Cloud Console Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create or select a project
3. **APIs & Services** → **Credentials** → **Create Credentials** → **OAuth client ID**
4. Application type: **Web application**
5. Add **Authorized JavaScript origins**:
   - `http://localhost:5173` (Vite default)
   - `http://localhost:3000` (CRA default)
   - Your production domain (e.g. `https://yourdomain.com`)
6. Add **Authorized redirect URIs** (if using redirect flow):
   - `http://localhost:5173` (or your app URL)
7. Copy **Client ID** and **Client Secret**

---

## Step 2: Backend Configuration

Add to your Laravel `.env`:

```env
GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_client_secret
```

The backend uses these to validate the `access_token` sent by the frontend.

---

## Step 3: Install @react-oauth/google

```bash
npm install @react-oauth/google
```

---

## Step 4: Wrap Your App with GoogleOAuthProvider

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

---

## Step 5: Google Login Button Component

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

---

## Step 6: Storing the Sanctum Token

After successful login, store the token:

```js
localStorage.setItem('sanctum_token', data.data.token);
```

For better security in production, consider:
- `httpOnly` cookies (requires backend CORS/cookie config)
- Secure storage (e.g. encrypted sessionStorage)

---

## Step 7: Using the Token in Protected APIs

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

---

## Step 8: Checkout-Based Login Flow

1. User browses products (no login required)
2. User adds to cart
3. User clicks "Checkout"
4. If not logged in → show login modal with:
   - Email + Password
   - Google (Gmail)
   - Mobile OTP
5. After login → redirect to checkout with token
6. Checkout API calls use `Authorization: Bearer <token>`

Example flow:

```jsx
function CheckoutPage() {
  const [user, setUser] = useState(null);
  const token = localStorage.getItem('sanctum_token');

  useEffect(() => {
    if (!token) {
      // Show login modal, don't allow checkout until logged in
      return;
    }
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

---

## Flow Diagram

```
┌─────────────┐     ┌──────────────────┐     ┌─────────────┐
│   React     │     │  Google OAuth    │     │  Gift Box   │
│   App      │     │  (@react-oauth)   │     │  API        │
└──────┬──────┘     └────────┬─────────┘     └──────┬──────┘
       │                     │                      │
       │  Click "Sign in"     │                      │
       │────────────────────>│                      │
       │                     │                      │
       │  Popup / Redirect   │                      │
       │<────────────────────│                      │
       │                     │                      │
       │  access_token       │                      │
       │<────────────────────│                      │
       │                     │                      │
       │  POST /auth/google  │                      │
       │  { access_token }   │                      │
       │──────────────────────────────────────────>│
       │                     │                      │
       │                     │  (Backend validates  │
       │                     │   with Google)       │
       │                     │                      │
       │  { token, user }    │                      │
       │<──────────────────────────────────────────│
       │                     │                      │
       │  Store token        │                      │
       │  Use in API calls   │                      │
       │  Authorization: Bearer <token>             │
       │──────────────────────────────────────────>│
```

---

## Troubleshooting

### "Invalid Google token"
- Ensure `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` match the Google Cloud project
- Token may be expired; Google access tokens typically last ~1 hour

### CORS errors
- Laravel CORS config must allow your frontend origin
- Check `config/cors.php` and `Access-Control-Allow-Origin`

### Popup blocked
- Use `flow: 'implicit'` for popup; or `flow: 'auth-code'` if you prefer redirect
- Ensure login is triggered by user click (not on page load)

---

## Summary

| Step | Action |
|------|--------|
| 1 | Create OAuth client in Google Cloud Console |
| 2 | Add `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` to Laravel `.env` |
| 3 | Install `@react-oauth/google` |
| 4 | Wrap app with `GoogleOAuthProvider` |
| 5 | Use `useGoogleLogin` to get `access_token` |
| 6 | POST `access_token` to `POST /api/v1/auth/google` |
| 7 | Store returned Sanctum `token` in localStorage |
| 8 | Send `Authorization: Bearer <token>` on protected API requests |

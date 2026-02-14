# QwikGift Postman Collection

Import `QwikGift_Categories.postman_collection.json` into Postman.

## Setup

1. Open the collection → Variables tab
2. Set: `client_id`, `client_secret`, `username`, `password` (from Qwikcilver)
3. Optionally set `base_url` (default: https://sandbox.woohoo.in)

## Usage

1. **1. OAuth - Get Authorization Code** – Run first. Saves `authorization_code` automatically.
2. **2. OAuth - Get Bearer Token** – Run second. Saves `bearer_token` automatically.
3. **3. Get Categories (full list)** – Run to fetch all categories. Pre-request script adds `dateAtClient` and `signature` headers per OAuth2.0 spec.
4. **4. Get Categories by ID** – Run to fetch a single category. Set `category_id` variable (default: 7).

## Signature (OAuth2.0)

Per QwikGift docs:
- Base string: `METHOD&URL_ENCODED_FULL_URL` (for GET)
- Signature: HMAC-SHA512(baseString, clientSecret) → hex
- Headers: `dateAtClient` (ISO 8601), `signature`

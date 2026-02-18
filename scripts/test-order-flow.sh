#!/usr/bin/env bash
#
# End-to-end order flow test: login → create order → set item → webhook
# Usage: ./scripts/test-order-flow.sh [BASE_URL]
#
# Set TEST_EMAIL and TEST_PASSWORD in .env or export before running.
# Example: TEST_EMAIL=user@example.com TEST_PASSWORD=secret ./scripts/test-order-flow.sh
#

set -e

# Load TEST_* from .env if present
if [[ -f .env ]]; then
  export $(grep -E '^TEST_EMAIL=|^TEST_PASSWORD=' .env 2>/dev/null | xargs) 2>/dev/null || true
fi

BASE_URL="${1:-${APP_URL:-http://localhost:8000}}"
BASE_URL="${BASE_URL%/}"
EMAIL="${TEST_EMAIL:-john@example.com}"
PASSWORD="${TEST_PASSWORD:-password123}"

# Check for jq (needed for JSON parsing)
if ! command -v jq &>/dev/null; then
  echo "Error: jq is required. Install with: brew install jq"
  exit 1
fi

echo "=== Gift Box Order Flow Test ==="
echo "Base URL: $BASE_URL"
echo "Email: $EMAIL"
echo ""

# 1. Health check
echo "[1/7] Health check..."
HEALTH=$(curl -s "$BASE_URL/api/v1/health")
if ! echo "$HEALTH" | jq -e '.status == "ok"' &>/dev/null; then
  echo "  FAIL: API not reachable. Is the server running? (php artisan serve)"
  echo "  Response: $HEALTH"
  exit 1
fi
echo "  OK"

# 2. Login
echo "[2/7] Login..."
LOGIN=$(curl -s -X POST "$BASE_URL/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

if ! echo "$LOGIN" | jq -e '.success == true' &>/dev/null; then
  echo "  FAIL: Login failed. Check TEST_EMAIL and TEST_PASSWORD."
  echo "  Response: $(echo "$LOGIN" | jq -c .)"
  exit 1
fi

TOKEN=$(echo "$LOGIN" | jq -r '.data.token')
if [[ -z "$TOKEN" || "$TOKEN" == "null" ]]; then
  echo "  FAIL: No token in response"
  exit 1
fi
echo "  OK (token obtained)"

# 3. Get a product (use featured, fallback to best-sellers)
echo "[3/7] Fetch product..."
PRODUCTS=$(curl -s "$BASE_URL/api/v1/products/featured?limit=5")
PRODUCT_ID=$(echo "$PRODUCTS" | jq -r '.data[0].id // empty')
if [[ -z "$PRODUCT_ID" ]]; then
  PRODUCTS=$(curl -s "$BASE_URL/api/v1/products/best-sellers?limit=5")
  PRODUCT_ID=$(echo "$PRODUCTS" | jq -r '.data[0].id // empty')
fi
if [[ -z "$PRODUCT_ID" ]]; then
  PRODUCTS=$(curl -s "$BASE_URL/api/v1/products/trending?limit=5")
  PRODUCT_ID=$(echo "$PRODUCTS" | jq -r '.data[0].id // empty')
fi

if [[ -z "$PRODUCT_ID" ]]; then
  echo "  FAIL: No products found. Run Woohoo sync first: php artisan woohoo:fetch-products"
  exit 1
fi

UNIT_PRICE=$(echo "$PRODUCTS" | jq -r '.data[0].min_price // 100')
UNIT_PRICE="${UNIT_PRICE:-100}"
echo "  OK (product_id=$PRODUCT_ID, unit_price=$UNIT_PRICE)"

# 4. Create order
echo "[4/7] Create order..."
ORDER_CREATE=$(curl -s -X POST "$BASE_URL/api/v1/order" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{}')

if ! echo "$ORDER_CREATE" | jq -e '.success == true' &>/dev/null; then
  echo "  FAIL: $(echo "$ORDER_CREATE" | jq -r '.message // .')"
  exit 1
fi

ORDER_ID=$(echo "$ORDER_CREATE" | jq -r '.data.id')
echo "  OK (order_id=$ORDER_ID)"

# 5. Set order item
echo "[5/7] Set order item..."
SET_ITEM=$(curl -s -X POST "$BASE_URL/api/v1/order/item" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d "{\"product_id\":$PRODUCT_ID,\"quantity\":1,\"unit_price\":$UNIT_PRICE}")

if ! echo "$SET_ITEM" | jq -e '.success == true' &>/dev/null; then
  echo "  FAIL: $(echo "$SET_ITEM" | jq -r '.message // .')"
  exit 1
fi
echo "  OK"

# 6. Trigger payment webhook (simulates payment success)
echo "[6/7] Payment webhook (fulfill via Woohoo)..."
WEBHOOK=$(curl -s -X POST "$BASE_URL/api/v1/webhooks/payment-success" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{
    \"order_id\": $ORDER_ID,
    \"billing\": {
      \"email\": \"$EMAIL\",
      \"telephone\": \"+919876543210\",
      \"name\": \"Test Customer\"
    }
  }")

if ! echo "$WEBHOOK" | jq -e '.woohoo_order_id or .refno' &>/dev/null; then
  echo "  FAIL or Woohoo error:"
  echo "$WEBHOOK" | jq . 2>/dev/null || echo "$WEBHOOK"
  exit 1
fi

echo "  OK"
echo "  Woohoo refno: $(echo "$WEBHOOK" | jq -r '.refno // "—"')"
echo "  Woohoo order_id: $(echo "$WEBHOOK" | jq -r '.woohoo_order_id // "—"')"
echo "  Poll dispatched: $(echo "$WEBHOOK" | jq -r '.poll_dispatched // "—"')"

# 7. Process queue (poll Woohoo Order Details) and fetch updated order
echo "[7/7] Processing queue (poll Woohoo Order Details, update delivery_status)..."
php artisan queue:work redis --queue=woohoo-order-poll --stop-when-empty --max-time=360 2>/dev/null || true
echo "  Queue processed"

# Fetch order by ID to show updated status from Woohoo (order may be completed, not pending)
ORDER_GET=$(curl -s "$BASE_URL/api/v1/order/$ORDER_ID" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN")

echo ""
echo "=== Result ==="
echo "$WEBHOOK" | jq .
echo ""
echo "=== Order (after Woohoo status update) ==="
echo "$ORDER_GET" | jq '.data | {id, woohoo_refno, woohoo_order_id, delivery_status, status}' 2>/dev/null || echo "$ORDER_GET" | jq .
echo ""
echo "Delivery status: $(echo "$ORDER_GET" | jq -r '.data.delivery_status // "—"')"
echo ""
echo "=== Test complete ==="

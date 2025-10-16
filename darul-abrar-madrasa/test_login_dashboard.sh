#!/usr/bin/env bash
set -euo pipefail

BASE="${BASE:-https://darulabrar.ailearnersbd.com}"
COOKIE="$(mktemp -u /tmp/test_login_cookies.XXXXXX.txt)"

echo "=========================================="
echo "🧪 Testing Login and Dashboard Access"
echo "=========================================="
echo ""

ua="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36"

# Step 1: Get CSRF token and set cookie jar
echo "1. Getting CSRF and XSRF tokens from login page..."
curl -s -A "$ua" -c "$COOKIE" "$BASE/login" -D /tmp/test_login_headers_get.txt -o /tmp/test_login_body_get.html >/dev/null 2>&1 || true

# Prefer hidden input _token, fallback to meta csrf-token
CSRF_TOKEN="$(grep -oP 'name="_token" value="\K[^"]+' /tmp/test_login_body_get.html | head -1 || true)"
if [ -z "${CSRF_TOKEN:-}" ]; then
  CSRF_TOKEN="$(grep -oP 'csrf-token" content="\K[^"]+' /tmp/test_login_body_get.html | head -1 || true)"
fi
XSRF_TOKEN="$(awk '$0 !~ /^#/ && $6=="XSRF-TOKEN"{print $7}' "$COOKIE" 2>/dev/null | tail -1 || true)"

echo "   CSRF Token: ${CSRF_TOKEN:0:20}..."
echo "   XSRF cookie present: $( [ -n "${XSRF_TOKEN:-}" ] && echo yes || echo no )"
echo ""

# Step 2: Attempt login (send CSRF headers + form token, with Origin/Referer and post-follow)
echo "2. Attempting login with admin credentials..."
LOGIN_HEADERS="/tmp/test_login_headers_post.txt"
LOGIN_BODY="/tmp/test_login_body_post.html"
curl -i -s -A "$ua" -b "$COOKIE" -c "$COOKIE" --post302 --post301 \
  -X POST "$BASE/login" \
  -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Origin: $BASE" \
  -e "$BASE/login" \
  -H "X-CSRF-TOKEN: $CSRF_TOKEN" \
  ${XSRF_TOKEN:+-H "X-XSRF-TOKEN: $XSRF_TOKEN"} \
  --data "email=admin@darulabrar.com&password=Admin@2025&_token=$CSRF_TOKEN" \
  -D "$LOGIN_HEADERS" -o "$LOGIN_BODY" -w "HTTP_CODE:%{http_code}\n" | tee /tmp/test_login_result.txt >/dev/null

HTTP_CODE="$(sed -n 's/.*HTTP_CODE:\([0-9]\+\).*/\1/p' /tmp/test_login_result.txt)"
echo "   HTTP Response Code: ${HTTP_CODE:-unknown}"
echo ""

# Step 3: Try to access dashboard
echo "3. Accessing dashboard..."
DASH_HEADERS="/tmp/test_dash_headers.txt"
DASH_BODY="/tmp/test_dash_body.html"
curl -i -s -A "$ua" -b "$COOKIE" -c "$COOKIE" "$BASE/dashboard" -D "$DASH_HEADERS" -o "$DASH_BODY" -w "HTTP_CODE:%{http_code}\n" | tee /tmp/test_dash_result.txt >/dev/null
DASHBOARD_HTTP_CODE="$(sed -n 's/.*HTTP_CODE:\([0-9]\+\).*/\1/p' /tmp/test_dash_result.txt)"

echo "   Dashboard HTTP Code: ${DASHBOARD_HTTP_CODE:-unknown}"
echo ""

if [ "${DASHBOARD_HTTP_CODE:-}" = "200" ]; then
  echo "✅ Dashboard loaded successfully!"
  echo ""
  echo "Dashboard content preview:"
  head -20 "$DASH_BODY"
elif [ "${DASHBOARD_HTTP_CODE:-}" = "500" ]; then
  echo "❌ Dashboard returned 500 error"
  echo ""
  echo "Error page content:"
  grep -A 5 -iE "error|exception" "$DASH_BODY" | head -20 || true
else
  echo "⚠️  Unexpected response code: ${DASHBOARD_HTTP_CODE:-unknown}"
fi

# Cleanup
rm -f "$COOKIE" 2>/dev/null || true

echo ""
echo "=========================================="
echo "Test Complete"
echo "=========================================="

#!/usr/bin/env bash
set -u

# Manual Authentication Tests
# Run from: /root/darul-abrar-madrasa/darul-abrar-madrasa
TS="$(date +%Y%m%d_%H%M%S)"
mkdir -p ../outputs
exec > >(tee ../outputs/auth_manual_tests_${TS}.txt)
exec 2>&1

BASE="https://darulabrar.ailearnersbd.com"
UA="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36"

section() { printf "\n============================================================\n%s\n============================================================\n" "$1"; }
show_head() { local f="$1"; echo "----- HEADERS (top 40) -----"; sed -n "1,40p" "$f"; }
show_code() { local f="$1"; local c; c=$(grep -o "HTTP/1\.[01] [0-9][0-9][0-9]" "$f" | tail -1 | awk '{print $2}'); echo "HTTP_CODE: ${c:-unknown}"; }

# Utility to extract CSRF & XSRF tokens after GET /login, keeping cookie jar
get_login_tokens() {
  COOKIE="$1"
  curl -i -s -A "$UA" -c "$COOKIE" "$BASE/login" -D /tmp/h_login_get.txt -o /tmp/b_login_get.html
  CSRF=$(grep -oP 'name="_token" value="\K[^"]+' /tmp/b_login_get.html | head -1)
  if [ -z "${CSRF:-}" ]; then
    CSRF=$(grep -oP 'csrf-token" content="\K[^"]+' /tmp/b_login_get.html | head -1)
  fi
  XSRF=$(awk '$0 !~ /^#/ && $6=="XSRF-TOKEN"{print $7}' "$COOKIE" | tail -1)
  echo "CSRF token: ${CSRF:0:16}... | XSRF cookie present: $( [ -n "$XSRF" ] && echo yes || echo no )"
}

# 1) Successful login and dashboard access
section "1) Successful login and dashboard access"
COOKIE="/tmp/c_auth_ok_${TS}.txt"
get_login_tokens "$COOKIE"
curl -i -s -A "$UA" -b "$COOKIE" -c "$COOKIE" --post302 --post301 -X POST "$BASE/login" \
  -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Origin: $BASE" -e "$BASE/login" \
  -H "X-CSRF-TOKEN: $CSRF" ${XSRF:+-H "X-XSRF-TOKEN: $XSRF"} \
  --data "email=admin@darulabrar.com&password=Admin@2025&_token=$CSRF" \
  -D /tmp/h_login_post_ok.txt -o /tmp/b_login_post_ok.html
show_head /tmp/h_login_post_ok.txt
show_code /tmp/h_login_post_ok.txt

# Dashboard after login
echo
echo "-- GET /dashboard --"
curl -i -s -A "$UA" -b "$COOKIE" -c "$COOKIE" "$BASE/dashboard" -D /tmp/h_dash_ok.txt -o /tmp/b_dash_ok.html
show_code /tmp/h_dash_ok.txt

# 2) Logout flow
section "2) Logout flow"
# Extract fresh CSRF from dashboard meta tag
CSRF2=$(grep -oP 'name="csrf-token" content="\K[^"]+' /tmp/b_dash_ok.html | head -1)
curl -i -s -A "$UA" -b "$COOKIE" -c "$COOKIE" --post302 --post301 -X POST "$BASE/logout" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Origin: $BASE" -e "$BASE/dashboard" \
  -H "X-CSRF-TOKEN: $CSRF2" \
  --data "_token=$CSRF2" \
  -D /tmp/h_logout.txt -o /tmp/b_logout.html
show_head /tmp/h_logout.txt
show_code /tmp/h_logout.txt

# 3) Failed login (wrong password)
section "3) Failed login (wrong password)"
COOKIE_BAD="/tmp/c_auth_bad_${TS}.txt"
get_login_tokens "$COOKIE_BAD"
curl -i -s -A "$UA" -b "$COOKIE_BAD" -c "$COOKIE_BAD" --post302 --post301 -X POST "$BASE/login" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Origin: $BASE" -e "$BASE/login" \
  -H "X-CSRF-TOKEN: $CSRF" ${XSRF:+-H "X-XSRF-TOKEN: $XSRF"} \
  --data "email=admin@darulabrar.com&password=WrongPassword&_token=$CSRF" \
  -D /tmp/h_login_post_bad.txt -o /tmp/b_login_post_bad.html
show_head /tmp/h_login_post_bad.txt
show_code /tmp/h_login_post_bad.txt

# 4) Protected route without login (fresh cookie jar)
section "4) Protected route without login"
COOKIE_NONE="/tmp/c_auth_none_${TS}.txt"
curl -i -s -A "$UA" -c "$COOKIE_NONE" "$BASE/dashboard" -D /tmp/h_dash_anon.txt -o /tmp/b_dash_anon.html
show_head /tmp/h_dash_anon.txt
show_code /tmp/h_dash_anon.txt

# 5) CSRF missing on login (expect 419)
section "5) CSRF missing on login (expect 419)"
COOKIE_NO_CSRF="/tmp/c_auth_nocsrf_${TS}.txt"
# Intentionally skip fetching /login to avoid CSRF, and post without _token/headers
curl -i -s -A "$UA" -c "$COOKIE_NO_CSRF" -X POST "$BASE/login" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data "email=admin@darulabrar.com&amp;password=Admin@2025" \
  -D /tmp/h_login_no_csrf.txt -o /tmp/b_login_no_csrf.html
show_head /tmp/h_login_no_csrf.txt
show_code /tmp/h_login_no_csrf.txt

echo
echo "=== DONE: Authentication manual tests ==="

#!/usr/bin/env bash
set -euo pipefail

BASE="${BASE:-https://darulabrar.ailearnersbd.com}"
COOKIE="cookies_full_phase1.txt"
TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR" "$COOKIE" 2>/dev/null || true' EXIT
UA="Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36"

log() { echo -e "$*"; }
hr() { echo "------------------------------------------------------------"; }

# Extract CSRF token from a page (input _token or meta csrf-token)
csrf_from() {
  local url="$1"
  curl -s -b "$COOKIE" -c "$COOKIE" -L -A "$UA" "$url" -D "$TMP_DIR/headers_csrf.txt" -o "$TMP_DIR/page.html" || true
  local token=""
  token="$(grep -oP 'name="_token" value="\K[^"]+' "$TMP_DIR/page.html" | head -1 || true)"
  if [[ -z "$token" ]]; then
    token="$(grep -oP 'csrf-token" content="\K[^"]+' "$TMP_DIR/page.html" | head -1 || true)"
  fi
  echo "$token"
}

# Read a cookie value by name from the cookie jar
cookie_val() { awk -v name="$1" '$0 !~ /^#/ && $6==name {print $7}' "$COOKIE" 2>/dev/null | tail -1; }

# HTTP check helper (detects if redirected to login page)
check() {
  local path="$1"; local name="$2"
  local body_file="$TMP_DIR/check.html"
  local code
  code="$(curl -s -b "$COOKIE" -c "$COOKIE" -L "$BASE$path" -D "$TMP_DIR/headers.txt" -o "$body_file" -w "%{http_code}" || true)"
  local marker=""
  if grep -qiE '<h2[^>]*>Login<' "$body_file" || grep -qiE 'name="email".*name="password"' "$body_file"; then
    marker="(login page)"
  fi
  printf "%-30s %-32s => %s %s\n" "$name" "$path" "$code" "$marker"
}

# POST form helper
post_form() {
  local url="$1"; local ref="$2"; shift 2
  local origin
  origin="$(echo "$url" | sed -E 's#^(https?://[^/]+).*#\1#')"
  local code
  local header_args=()
  if [ -n "${CSRF_HEADER:-}" ]; then
    header_args+=(-H "X-CSRF-TOKEN: $CSRF_HEADER")
  fi
  if [ -n "${XSRF_HEADER:-}" ]; then
    header_args+=(-H "X-XSRF-TOKEN: $XSRF_HEADER")
  fi
  code="$(curl -s -b "$COOKIE" -c "$COOKIE" --post302 --post301 -X POST "$url" \
    -A "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36" \
    -H "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    -H "Origin: $origin" \
    -e "$ref" \
    "${header_args[@]}" \
    --data "$*" \
    -o /dev/null -w "%{http_code}" || true)"
  echo "$code"
}

log "=== Phase 1 Full Health Check ==="
hr
log "1) Login as admin"
CSRF="$(csrf_from "$BASE/login")"
CSRF_HEADER="$CSRF"
XSRF="$(cookie_val 'XSRF-TOKEN')"
XSRF_HEADER="$XSRF"
log "   CSRF: ${CSRF:0:16}..."
LOGIN_CODE="$(post_form "$BASE/login" "$BASE/login" "email=admin@darulabrar.com&password=Admin@2025&_token=$CSRF")"
log "   Login HTTP: $LOGIN_CODE"
hr

log "2) GET endpoints (expect 200)"
check "/dashboard"                "Dashboard"
check "/teachers"                 "Teachers index"
check "/teachers/create"          "Teachers create"
check "/students"                 "Students index"
check "/students/create"          "Students create"
check "/departments"              "Departments index"
check "/departments/create"       "Departments create"
check "/classes"                  "Classes index"
check "/classes/create"           "Classes create"
check "/subjects"                 "Subjects index"
check "/subjects/create"          "Subjects create"
check "/users"                    "Users index"
check "/users/create"             "Users create"
check "/fees"                     "Fees index"
check "/fees/create"              "Fees create"
check "/fees-reports"             "Fees reports index"
check "/fees-reports/collection"  "Fees collection report"
check "/fees-reports/outstanding" "Fees outstanding report"
check "/attendances"              "Attendances index"
check "/exams"                    "Exams index"
check "/results"                  "Results index"
check "/notices"                  "Notices index"
check "/notices/create"           "Notices create"
check "/notices/public"           "Notices public"
hr

log "3) POST sanity creates (expect 302 redirect on success)"
# 3.1 Create Department (no FKs)
CSRF_DEP="$(csrf_from "$BASE/departments/create")"
CSRF_HEADER="$CSRF_DEP"
XSRF="$(cookie_val 'XSRF-TOKEN')"
XSRF_HEADER="$XSRF"
DEPT_CODE="$(post_form "$BASE/departments" "$BASE/departments/create" \
  "name=QA Dept $(date +%s)&code=QA-$(date +%s)&description=Created via health check&is_active=1&_token=$CSRF_DEP")"
log "   Department create HTTP: $DEPT_CODE"

# 3.2 Create Teacher (needs department_id; assume 1 exists from seeds or just created new)
CSRF_TEA="$(csrf_from "$BASE/teachers/create")"
CSRF_HEADER="$CSRF_TEA"
XSRF="$(cookie_val 'XSRF-TOKEN')"
XSRF_HEADER="$XSRF"
TEACH_EMAIL="healthcheck.teacher.$(date +%s)@darulabrar.com"
TEACH_CODE="$(post_form "$BASE/teachers" "$BASE/teachers/create" \
  "name=Health Check Teacher&email=$TEACH_EMAIL&password=Password123!&password_confirmation=Password123!&phone=01700000000&department_id=1&designation=Senior Teacher&qualification=MA&joining_date=$(date +%F)&address=Dhaka&salary=20000&is_active=1&_token=$CSRF_TEA")"
log "   Teacher create HTTP: $TEACH_CODE"

# 3.3 Create Notice (no FKs)
CSRF_NOT="$(csrf_from "$BASE/notices/create")"
CSRF_HEADER="$CSRF_NOT"
XSRF="$(cookie_val 'XSRF-TOKEN')"
XSRF_HEADER="$XSRF"
NOT_CODE="$(post_form "$BASE/notices" "$BASE/notices/create" \
  "title=Health Check Notice $(date +%s)&description=Created during Phase 1 full test&notice_for=all&publish_date=$(date +%F)&is_active=1&_token=$CSRF_NOT")"
log "   Notice create HTTP: $NOT_CODE"
hr

log "4) Fees reports re-check (expect 200)"
check "/fees-reports/collection"  "Fees collection report"
check "/fees-reports/outstanding" "Fees outstanding report"
hr

log "5) Role-protected core pages OK if above are 200. For deep CRUD/validation and PDFs, proceed interactively if needed."
log "=== End of Health Check ==="

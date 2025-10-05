#!/usr/bin/env bash
set -euo pipefail

BASE="${BASE:-https://darulabrar.ailearnersbd.com}"
COOKIE="cookies_full_phase1.txt"
TMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TMP_DIR" "$COOKIE" 2>/dev/null || true' EXIT

log() { echo -e "$*"; }
hr() { echo "------------------------------------------------------------"; }

# Extract CSRF token from a page (input _token or meta csrf-token)
csrf_from() {
  local url="$1"
  curl -s -c "$COOKIE" "$url" > "$TMP_DIR/page.html" || true
  local token=""
  token="$(grep -oP 'name="_token" value="\K[^"]+' "$TMP_DIR/page.html" | head -1 || true)"
  if [[ -z "$token" ]]; then
    token="$(grep -oP 'csrf-token" content="\K[^"]+' "$TMP_DIR/page.html" | head -1 || true)"
  fi
  echo "$token"
}

# HTTP check helper
check() {
  local path="$1"; local name="$2"
  local code
  code="$(curl -s -b "$COOKIE" -o /dev/null -w "%{http_code}" -L "$BASE$path" || true)"
  printf "%-30s %-32s => %s\n" "$name" "$path" "$code"
}

# POST form helper
post_form() {
  local url="$1"; shift
  local code
  code="$(curl -s -b "$COOKIE" -c "$COOKIE" -L -X POST "$url" \
    -H "Content-Type: application/x-www-form-urlencoded" \
    --data "$*" \
    -o /dev/null -w "%{http_code}" || true)"
  echo "$code"
}

log "=== Phase 1 Full Health Check ==="
hr
log "1) Login as admin"
CSRF="$(csrf_from "$BASE/login")"
log "   CSRF: ${CSRF:0:16}..."
LOGIN_CODE="$(post_form "$BASE/login" "email=admin@darulabrar.com&amp;password=Admin@2025&amp;_token=$CSRF")"
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
DEPT_CODE="$(post_form "$BASE/departments" \
  "name=QA Dept $(date +%s)&amp;code=QA-$(date +%s)&amp;description=Created via health check&amp;is_active=1&amp;_token=$CSRF_DEP")"
log "   Department create HTTP: $DEPT_CODE"

# 3.2 Create Teacher (needs department_id; assume 1 exists from seeds or just created new)
CSRF_TEA="$(csrf_from "$BASE/teachers/create")"
TEACH_EMAIL="healthcheck.teacher.$(date +%s)@darulabrar.com"
TEACH_CODE="$(post_form "$BASE/teachers" \
  "name=Health Check Teacher&amp;email=$TEACH_EMAIL&amp;password=Password123!&amp;password_confirmation=Password123!&amp;phone=01700000000&amp;department_id=1&amp;designation=Senior Teacher&amp;qualification=MA&amp;joining_date=$(date +%F)&amp;address=Dhaka&amp;salary=20000&amp;is_active=1&amp;_token=$CSRF_TEA")"
log "   Teacher create HTTP: $TEACH_CODE"

# 3.3 Create Notice (no FKs)
CSRF_NOT="$(csrf_from "$BASE/notices/create")"
NOT_CODE="$(post_form "$BASE/notices" \
  "title=Health Check Notice $(date +%s)&amp;description=Created during Phase 1 full test&amp;notice_for=all&amp;publish_date=$(date +%F)&amp;is_active=1&amp;_token=$CSRF_NOT")"
log "   Notice create HTTP: $NOT_CODE"
hr

log "4) Fees reports re-check (expect 200)"
check "/fees-reports/collection"  "Fees collection report"
check "/fees-reports/outstanding" "Fees outstanding report"
hr

log "5) Role-protected core pages OK if above are 200. For deep CRUD/validation and PDFs, proceed interactively if needed."
log "=== End of Health Check ==="

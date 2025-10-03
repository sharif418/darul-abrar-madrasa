#!/bin/bash

echo "=========================================="
echo "🧪 Testing Login and Dashboard Access"
echo "=========================================="
echo ""

# Step 1: Get CSRF token from login page
echo "1. Getting CSRF token from login page..."
CSRF_TOKEN=$(curl -s -c cookies.txt https://darulabrar.ailearnersbd.com/login | grep -oP 'csrf-token" content="\K[^"]+' | head -1)
echo "   CSRF Token: ${CSRF_TOKEN:0:20}..."
echo ""

# Step 2: Attempt login
echo "2. Attempting login with admin credentials..."
LOGIN_RESPONSE=$(curl -s -b cookies.txt -c cookies.txt -L \
  -X POST https://darulabrar.ailearnersbd.com/login \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "email=admin@darulabrar.com&password=Admin@2025&_token=$CSRF_TOKEN" \
  -w "\nHTTP_CODE:%{http_code}")

HTTP_CODE=$(echo "$LOGIN_RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)
echo "   HTTP Response Code: $HTTP_CODE"
echo ""

# Step 3: Try to access dashboard
echo "3. Accessing dashboard..."
DASHBOARD_RESPONSE=$(curl -s -b cookies.txt https://darulabrar.ailearnersbd.com/dashboard -w "\nHTTP_CODE:%{http_code}")
DASHBOARD_HTTP_CODE=$(echo "$DASHBOARD_RESPONSE" | grep "HTTP_CODE" | cut -d: -f2)

echo "   Dashboard HTTP Code: $DASHBOARD_HTTP_CODE"
echo ""

if [ "$DASHBOARD_HTTP_CODE" = "200" ]; then
    echo "✅ Dashboard loaded successfully!"
    echo ""
    echo "Dashboard content preview:"
    echo "$DASHBOARD_RESPONSE" | head -20
elif [ "$DASHBOARD_HTTP_CODE" = "500" ]; then
    echo "❌ Dashboard returned 500 error"
    echo ""
    echo "Error page content:"
    echo "$DASHBOARD_RESPONSE" | grep -A 5 "error\|Error\|Exception" | head -20
else
    echo "⚠️  Unexpected response code: $DASHBOARD_HTTP_CODE"
fi

# Cleanup
rm -f cookies.txt

echo ""
echo "=========================================="
echo "Test Complete"
echo "=========================================="

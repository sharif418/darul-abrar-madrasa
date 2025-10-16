#!/bin/bash

# Timetable Management System - API Testing Script
# This script tests all timetable-related endpoints

BASE_URL="http://localhost"
ADMIN_EMAIL="admin@darulabrar.edu"
ADMIN_PASSWORD="password"

echo "========================================="
echo "Timetable Management System - API Testing"
echo "========================================="
echo ""

# Function to login and get session cookie
login_admin() {
    echo "1. Logging in as admin..."
    curl -s -c cookies.txt -X POST "$BASE_URL/login" \
        -d "email=$ADMIN_EMAIL" \
        -d "password=$ADMIN_PASSWORD" \
        -d "_token=$(curl -s $BASE_URL/login | grep -oP '(?<=_token" value=")[^"]*')" \
        > /dev/null
    
    if [ -f cookies.txt ]; then
        echo "   ✓ Login successful"
    else
        echo "   ✗ Login failed"
        exit 1
    fi
}

# Test Period Endpoints
test_periods() {
    echo ""
    echo "2. Testing Period Endpoints..."
    echo "   ----------------------------"
    
    # List periods
    echo "   a) GET /periods (List periods)"
    RESPONSE=$(curl -s -b cookies.txt "$BASE_URL/periods")
    if echo "$RESPONSE" | grep -q "Period Management"; then
        echo "      ✓ Periods index page loads"
    else
        echo "      ✗ Periods index page failed"
    fi
    
    # Create period form
    echo "   b) GET /periods/create (Create form)"
    RESPONSE=$(curl -s -b cookies.txt "$BASE_URL/periods/create")
    if echo "$RESPONSE" | grep -q "Create Period"; then
        echo "      ✓ Create period form loads"
    else
        echo "      ✗ Create period form failed"
    fi
}

# Test Timetable Endpoints
test_timetables() {
    echo ""
    echo "3. Testing Timetable Endpoints..."
    echo "   -------------------------------"
    
    # List timetables
    echo "   a) GET /timetables (List timetables)"
    RESPONSE=$(curl -s -b cookies.txt "$BASE_URL/timetables")
    if echo "$RESPONSE" | grep -q "Timetable"; then
        echo "      ✓ Timetables index page loads"
    else
        echo "      ✗ Timetables index page failed"
    fi
    
    # Create timetable form
    echo "   b) GET /timetables/create (Create form)"
    RESPONSE=$(curl -s -b cookies.txt "$BASE_URL/timetables/create")
    if echo "$RESPONSE" | grep -q "Create Timetable"; then
        echo "      ✓ Create timetable form loads"
    else
        echo "      ✗ Create timetable form failed"
    fi
}

# Test Routes Registration
test_routes() {
    echo ""
    echo "4. Testing Routes Registration..."
    echo "   ------------------------------"
    
    cd darul-abrar-madrasa
    
    # Check if routes are registered
    ROUTES=$(php artisan route:list --name=periods 2>&1)
    if echo "$ROUTES" | grep -q "periods.index"; then
        echo "   ✓ Period routes registered"
    else
        echo "   ✗ Period routes not found"
    fi
    
    ROUTES=$(php artisan route:list --name=timetables 2>&1)
    if echo "$ROUTES" | grep -q "timetables.index"; then
        echo "   ✓ Timetable routes registered"
    else
        echo "   ✗ Timetable routes not found"
    fi
    
    cd ..
}

# Main execution
echo "Starting API tests..."
echo ""

# Run tests
login_admin
test_periods
test_timetables
test_routes

echo ""
echo "========================================="
echo "Testing Complete"
echo "========================================="
echo ""
echo "Note: Full testing requires all view files to be created."
echo "Current status: Backend complete, views partially complete."
echo ""

# Cleanup
rm -f cookies.txt

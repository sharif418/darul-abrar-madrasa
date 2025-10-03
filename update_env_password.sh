#!/bin/bash

# Update .env file with correct database password
cd /root/darul-abrar-madrasa/darul-abrar-madrasa

# Escape special characters for sed
PASSWORD='Madrasa@2025#Secure'

# Update the password line
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD='${PASSWORD}'/" .env

echo "✓ Database password updated in .env file"

# Verify the connection
echo ""
echo "Testing database connection..."
php artisan migrate:status 2>&1 | head -5

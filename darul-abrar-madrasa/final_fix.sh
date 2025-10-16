#!/bin/bash
set -e
echo "--- Applying Final .env Configuration ---"

# Backup the current .env file
cp .env .env.backup_before_final_fix_$(date +%s)

# Force set APP_URL
sed -i -E "s/^APP_URL=.*/APP_URL=https:\/\/darulabrar.ailearnersbd.com/" .env

# Force set SESSION_DOMAIN to the correct apex domain
sed -i -E "s/^SESSION_DOMAIN=.*/SESSION_DOMAIN=.ailearnersbd.com/" .env

# Force set SESSION_SECURE_COOKIE to true for HTTPS
sed -i -E "s/^SESSION_SECURE_COOKIE=.*/SESSION_SECURE_COOKIE=true/" .env

echo "--- Clearing All Caches ---"
php artisan optimize:clear

echo "--- Restarting PHP-FPM Service ---"
sudo systemctl restart php8.2-fpm || sudo systemctl restart php8.1-fpm

echo "--- FIX APPLIED. Final verification needed. ---"

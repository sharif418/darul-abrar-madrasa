#!/bin/bash

# Laravel Application Configuration Script
# For Darul Abrar Madrasa Management System

set -e  # Exit on error

echo "=========================================="
echo "Laravel Application Configuration"
echo "=========================================="
echo ""

# Navigate to project directory
cd /root/darul-abrar-madrasa/darul-abrar-madrasa

echo "Step 1: Updating .env file..."
# Update .env file with production settings
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's|APP_URL=http://localhost|APP_URL=https://darulabrar.ailearnersbd.com|' .env

# Update database configuration
sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
sed -i 's/# DB_HOST=127.0.0.1/DB_HOST=127.0.0.1/' .env
sed -i 's/# DB_PORT=3306/DB_PORT=3306/' .env
sed -i 's/# DB_DATABASE=laravel/DB_DATABASE=darul_abrar_madrasa/' .env
sed -i 's/# DB_USERNAME=root/DB_USERNAME=madrasa_user/' .env
sed -i 's/# DB_PASSWORD=/DB_PASSWORD=Madrasa@2025#Secure/' .env

echo "✓ .env file updated"
echo ""

echo "Step 2: Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader
echo "✓ Composer dependencies installed"
echo ""

echo "Step 3: Generating application key..."
php artisan key:generate --force
echo "✓ Application key generated"
echo ""

echo "Step 4: Setting up storage permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
echo "✓ Storage permissions set"
echo ""

echo "Step 5: Creating storage symbolic link..."
php artisan storage:link
echo "✓ Storage link created"
echo ""

echo "Step 6: Running database migrations..."
php artisan migrate --force
echo "✓ Database migrations completed"
echo ""

echo "Step 7: Clearing and caching configuration..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✓ Cache cleared and optimized"
echo ""

echo "Step 8: Installing NPM dependencies..."
npm install
echo "✓ NPM dependencies installed"
echo ""

echo "Step 9: Building frontend assets..."
npm run build
echo "✓ Frontend assets built"
echo ""

echo "=========================================="
echo "Configuration Complete!"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Configure Nginx server block"
echo "2. Obtain SSL certificate"
echo "3. Test the application"
echo ""

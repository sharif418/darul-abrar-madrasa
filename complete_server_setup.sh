#!/bin/bash

set -e  # Exit on error

echo "=========================================="
echo "Darul Abrar Madrasa - Complete Server Setup"
echo "=========================================="
echo ""

PROJECT_DIR="/root/darul-abrar-madrasa/darul-abrar-madrasa"
DOMAIN="darulabrar.ailearnersbd.com"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Step 1: Check NPM build status
echo "Step 1: Checking frontend build..."
if [ -d "$PROJECT_DIR/public/build" ]; then
    print_status "Build directory exists"
    BUILD_FILES=$(ls -1 "$PROJECT_DIR/public/build" 2>/dev/null | wc -l)
    if [ "$BUILD_FILES" -gt 0 ]; then
        print_status "Build files found: $BUILD_FILES files"
    else
        print_warning "Build directory empty, running npm build..."
        cd "$PROJECT_DIR"
        timeout 180 npm run build || print_warning "Build may have timed out, continuing..."
    fi
else
    print_warning "Build directory not found, running npm build..."
    cd "$PROJECT_DIR"
    timeout 180 npm run build || print_warning "Build may have timed out, continuing..."
fi
echo ""

# Step 2: Set proper permissions
echo "Step 2: Setting file permissions..."
cd "$PROJECT_DIR"
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
print_status "Permissions set"
echo ""

# Step 3: Create database seeder for admin user
echo "Step 3: Creating admin user seeder..."
cat > "$PROJECT_DIR/database/seeders/AdminUserSeeder.php" << 'SEEDER_EOF'
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user if not exists
        if (!User::where('email', 'admin@darulabrar.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@darulabrar.com',
                'password' => Hash::make('Admin@2025'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            
            echo "Admin user created successfully!\n";
            echo "Email: admin@darulabrar.com\n";
            echo "Password: Admin@2025\n";
        } else {
            echo "Admin user already exists.\n";
        }
    }
}
SEEDER_EOF
print_status "Admin seeder created"
echo ""

# Step 4: Run seeder
echo "Step 4: Creating admin user..."
cd "$PROJECT_DIR"
php artisan db:seed --class=AdminUserSeeder --force
echo ""

# Step 5: Install Nginx configuration
echo "Step 5: Installing Nginx configuration..."
if [ -f "/root/darul-abrar-madrasa/darulabrar_nginx.conf" ]; then
    cp /root/darul-abrar-madrasa/darulabrar_nginx.conf /etc/nginx/sites-available/darulabrar
    
    # Remove default site if exists
    if [ -L "/etc/nginx/sites-enabled/default" ]; then
        rm /etc/nginx/sites-enabled/default
        print_status "Removed default Nginx site"
    fi
    
    # Enable our site
    ln -sf /etc/nginx/sites-available/darulabrar /etc/nginx/sites-enabled/
    print_status "Nginx configuration installed"
else
    print_error "Nginx configuration file not found"
    exit 1
fi
echo ""

# Step 6: Test Nginx configuration
echo "Step 6: Testing Nginx configuration..."
if nginx -t 2>&1 | grep -q "successful"; then
    print_status "Nginx configuration is valid"
else
    print_error "Nginx configuration has errors"
    nginx -t
    exit 1
fi
echo ""

# Step 7: Restart services
echo "Step 7: Restarting services..."
systemctl restart php8.2-fpm
print_status "PHP-FPM restarted"
systemctl restart nginx
print_status "Nginx restarted"
echo ""

# Step 8: Check service status
echo "Step 8: Checking service status..."
if systemctl is-active --quiet php8.2-fpm; then
    print_status "PHP-FPM is running"
else
    print_error "PHP-FPM is not running"
fi

if systemctl is-active --quiet nginx; then
    print_status "Nginx is running"
else
    print_error "Nginx is not running"
fi
echo ""

# Step 9: Install Certbot for SSL (if not already installed)
echo "Step 9: Checking SSL certificate setup..."
if ! command -v certbot &> /dev/null; then
    print_warning "Certbot not installed. Installing..."
    apt-get update -qq
    apt-get install -y certbot python3-certbot-nginx -qq
    print_status "Certbot installed"
fi

# Check if certificate already exists
if [ -d "/etc/letsencrypt/live/$DOMAIN" ]; then
    print_status "SSL certificate already exists for $DOMAIN"
else
    print_warning "SSL certificate not found. You can obtain it by running:"
    echo "    sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN"
fi
echo ""

# Step 10: Display summary
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "Application Details:"
echo "  URL: http://$DOMAIN"
echo "  Project Path: $PROJECT_DIR"
echo "  Public Path: $PROJECT_DIR/public"
echo ""
echo "Admin Credentials:"
echo "  Email: admin@darulabrar.com"
echo "  Password: Admin@2025"
echo ""
echo "Database:"
echo "  Name: darul_abrar_madrasa"
echo "  User: madrasa_user"
echo ""
echo "Next Steps:"
echo "  1. Update your domain DNS to point to this server IP: $(curl -s ifconfig.me)"
echo "  2. Obtain SSL certificate:"
echo "     sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN"
echo "  3. Access your application at: http://$DOMAIN"
echo ""
echo "Logs:"
echo "  Nginx Access: /var/log/nginx/darulabrar_access.log"
echo "  Nginx Error: /var/log/nginx/darulabrar_error.log"
echo "  Laravel: $PROJECT_DIR/storage/logs/laravel.log"
echo ""
echo "=========================================="

#!/bin/bash

echo "=========================================="
echo "🔍 Final Deployment Verification"
echo "=========================================="
echo ""

# Check services
echo "1. Checking Services..."
echo "   - Nginx: $(systemctl is-active nginx)"
echo "   - PHP-FPM: $(systemctl is-active php8.2-fpm)"
echo "   - MySQL: $(systemctl is-active mysql)"
echo ""

# Check website
echo "2. Checking Website..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://darulabrar.ailearnersbd.com)
echo "   - Homepage: HTTP $HTTP_CODE"

LOGIN_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://darulabrar.ailearnersbd.com/login)
echo "   - Login Page: HTTP $LOGIN_CODE"
echo ""

# Check database
echo "3. Checking Database..."
DB_TABLES=$(mysql -u madrasa_user -p'Madrasa@2025#Secure' darul_abrar_madrasa -e "SHOW TABLES;" 2>/dev/null | wc -l)
echo "   - Tables Count: $((DB_TABLES - 1))"
echo ""

# Check admin user
echo "4. Checking Admin User..."
ADMIN_EXISTS=$(mysql -u madrasa_user -p'Madrasa@2025#Secure' darul_abrar_madrasa -e "SELECT COUNT(*) FROM users WHERE email='admin@darulabrar.com';" 2>/dev/null | tail -1)
echo "   - Admin User Exists: $([ "$ADMIN_EXISTS" = "1" ] && echo "✅ Yes" || echo "❌ No")"
echo ""

# Check SSL
echo "5. Checking SSL Certificate..."
SSL_EXPIRY=$(echo | openssl s_client -servername darulabrar.ailearnersbd.com -connect darulabrar.ailearnersbd.com:443 2>/dev/null | openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)
echo "   - SSL Expires: $SSL_EXPIRY"
echo ""

# Check file permissions
echo "6. Checking File Permissions..."
STORAGE_PERM=$(stat -c "%a" /root/darul-abrar-madrasa/darul-abrar-madrasa/storage 2>/dev/null)
echo "   - Storage Directory: $STORAGE_PERM"
echo ""

echo "=========================================="
echo "✅ Verification Complete!"
echo "=========================================="
echo ""
echo "🌐 Access your application at:"
echo "   https://darulabrar.ailearnersbd.com"
echo ""
echo "🔐 Admin Login:"
echo "   Email: admin@darulabrar.com"
echo "   Password: Admin@2025"
echo ""

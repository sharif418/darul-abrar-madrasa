#!/bin/bash

echo "=========================================="
echo "DEPLOYMENT VERIFICATION"
echo "=========================================="
echo ""

echo "✓ Nginx: $(systemctl is-active nginx)"
echo "✓ PHP-FPM: $(systemctl is-active php8.2-fpm)"
echo "✓ MySQL: $(systemctl is-active mysql)"
echo ""

echo "Database Tables:"
mysql -u madrasa_user -p'Madrasa@2025#Secure' -e "USE darul_abrar_madrasa; SHOW TABLES;" 2>/dev/null | tail -n +2 | wc -l | xargs echo "  Total:"

echo ""
echo "Admin User:"
mysql -u madrasa_user -p'Madrasa@2025#Secure' -e "USE darul_abrar_madrasa; SELECT email FROM users WHERE role='admin';" 2>/dev/null | tail -n +2

echo ""
echo "Server IP: $(curl -s -4 ifconfig.me 2>/dev/null || echo 'Unable to fetch')"
echo ""
echo "=========================================="
echo "STATUS: READY FOR DNS CONFIGURATION"
echo "=========================================="

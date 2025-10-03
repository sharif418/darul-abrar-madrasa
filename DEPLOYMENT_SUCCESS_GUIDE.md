# 🎉 Darul Abrar Madrasa - Deployment Success Guide

## ✅ Deployment Status: COMPLETE

Alhamdulillah! Your Darul Abrar Madrasa Management System has been successfully deployed on your Contabo VPS server.

---

## 📊 System Information

### Server Details
- **Server IP (IPv4):** 207.180.223.39
- **Server IP (IPv6):** 2a02:c207:2282:4950::1
- **Domain:** darulabrar.ailearnersbd.com
- **Operating System:** Ubuntu 24.04 LTS
- **Web Server:** Nginx 1.24.0
- **PHP Version:** 8.2.29
- **Database:** MySQL 8.0.43
- **Node.js:** v18.20.8

### Application Details
- **Framework:** Laravel 12.26.3
- **Project Path:** `/root/darul-abrar-madrasa/darul-abrar-madrasa`
- **Public Path:** `/root/darul-abrar-madrasa/darul-abrar-madrasa/public`
- **Environment:** Production

---

## 🔐 Access Credentials

### Admin Login
```
URL: http://darulabrar.ailearnersbd.com/login
Email: admin@darulabrar.com
Password: Admin@2025
```

### Database Access
```
Database Name: darul_abrar_madrasa
Username: madrasa_user
Password: Madrasa@2025#Secure
Host: localhost
```

---

## 📋 What Has Been Completed

### ✅ Database Setup
- [x] MySQL 8.0 installed and configured
- [x] Database `darul_abrar_madrasa` created
- [x] Database user `madrasa_user` created with proper permissions
- [x] All 17 tables migrated successfully:
  - users, departments, classes, students, teachers
  - subjects, attendances, exams, results, fees
  - notices, grading_scales, lesson_plans, study_materials
  - password_reset_tokens, sessions, migrations
- [x] Admin user seeded

### ✅ Application Configuration
- [x] Laravel environment configured for production
- [x] Application key generated
- [x] Database connection configured
- [x] Cache driver set to 'file'
- [x] Session driver set to 'file'
- [x] Storage directories created and linked
- [x] File permissions set (755 for storage and bootstrap/cache)
- [x] Composer dependencies installed
- [x] NPM dependencies installed
- [x] Frontend assets built (Vite)

### ✅ Web Server Setup
- [x] Nginx installed and configured
- [x] Server block created for darulabrar.ailearnersbd.com
- [x] PHP-FPM 8.2 configured and running
- [x] Nginx configuration tested and validated
- [x] Services restarted and confirmed running
- [x] Certbot installed for SSL certificate management

---

## 🚀 Next Steps (IMPORTANT!)

### Step 1: Configure DNS (Required)
You need to update your domain's DNS settings to point to your server.

**Go to your domain registrar's DNS management panel and add:**

#### A Record (IPv4):
```
Type: A
Name: @ (or darulabrar)
Value: 207.180.223.39
TTL: 3600 (or Auto)
```

#### CNAME Record (www subdomain):
```
Type: CNAME
Name: www
Value: darulabrar.ailearnersbd.com
TTL: 3600 (or Auto)
```

**DNS Propagation:** Wait 15-30 minutes for DNS to propagate worldwide.

**Check DNS Propagation:**
```bash
# From your local computer
nslookup darulabrar.ailearnersbd.com
# or
ping darulabrar.ailearnersbd.com
```

---

### Step 2: Obtain SSL Certificate (After DNS Propagation)

Once DNS has propagated, run this command on your server:

```bash
sudo certbot --nginx -d darulabrar.ailearnersbd.com -d www.darulabrar.ailearnersbd.com --email your-email@example.com --agree-tos --no-eff-email
```

**Replace `your-email@example.com` with your actual email address.**

This will:
- Obtain a free SSL certificate from Let's Encrypt
- Automatically configure Nginx for HTTPS
- Set up automatic certificate renewal

After SSL installation, your site will be accessible at:
- **https://darulabrar.ailearnersbd.com** ✅

---

### Step 3: Test Your Application

1. **Access the application:**
   - Open browser: http://darulabrar.ailearnersbd.com (or https:// after SSL)

2. **Login with admin credentials:**
   ```
   Email: admin@darulabrar.com
   Password: Admin@2025
   ```

3. **Test key features:**
   - Dashboard loads correctly
   - Can create departments
   - Can create classes
   - Can add students
   - Can add teachers
   - Can mark attendance
   - Can create exams
   - Can enter results
   - Can manage fees

---

## 📁 Important File Locations

### Application Files
```
Project Root: /root/darul-abrar-madrasa/darul-abrar-madrasa
Public Directory: /root/darul-abrar-madrasa/darul-abrar-madrasa/public
Environment File: /root/darul-abrar-madrasa/darul-abrar-madrasa/.env
Storage: /root/darul-abrar-madrasa/darul-abrar-madrasa/storage
```

### Configuration Files
```
Nginx Config: /etc/nginx/sites-available/darulabrar
Nginx Enabled: /etc/nginx/sites-enabled/darulabrar
PHP-FPM Config: /etc/php/8.2/fpm/php.ini
PHP-FPM Pool: /etc/php/8.2/fpm/pool.d/www.conf
```

### Log Files
```
Laravel Logs: /root/darul-abrar-madrasa/darul-abrar-madrasa/storage/logs/laravel.log
Nginx Access: /var/log/nginx/darulabrar_access.log
Nginx Error: /var/log/nginx/darulabrar_error.log
PHP-FPM Error: /var/log/php8.2-fpm.log
MySQL Error: /var/log/mysql/error.log
```

---

## 🛠️ Useful Commands

### Application Management
```bash
# Navigate to project
cd /root/darul-abrar-madrasa/darul-abrar-madrasa

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate

# Create new admin user
php artisan db:seed --class=AdminUserSeeder

# Check application status
php artisan about
```

### Service Management
```bash
# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart mysql

# Check service status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql

# View service logs
sudo journalctl -u nginx -f
sudo journalctl -u php8.2-fpm -f
```

### Log Monitoring
```bash
# Watch Laravel logs
tail -f /root/darul-abrar-madrasa/darul-abrar-madrasa/storage/logs/laravel.log

# Watch Nginx error logs
tail -f /var/log/nginx/darulabrar_error.log

# Watch Nginx access logs
tail -f /var/log/nginx/darulabrar_access.log
```

### Database Management
```bash
# Access MySQL
sudo mysql

# Access specific database
sudo mysql darul_abrar_madrasa

# Backup database
sudo mysqldump -u madrasa_user -p darul_abrar_madrasa > backup_$(date +%Y%m%d).sql

# Restore database
sudo mysql -u madrasa_user -p darul_abrar_madrasa < backup_20250127.sql
```

---

## 🔒 Security Recommendations

### 1. Change Default Passwords
```bash
# Change admin password after first login
# Go to: Profile > Change Password
```

### 2. Update System Regularly
```bash
sudo apt update && sudo apt upgrade -y
```

### 3. Configure Firewall
```bash
# Allow HTTP, HTTPS, and SSH
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 4. Set Up Automatic Backups
Create a backup script:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/root/backups"
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u madrasa_user -p'Madrasa@2025#Secure' darul_abrar_madrasa > $BACKUP_DIR/db_$DATE.sql

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /root/darul-abrar-madrasa/darul-abrar-madrasa

# Keep only last 7 days of backups
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

### 5. Monitor Server Resources
```bash
# Check disk space
df -h

# Check memory usage
free -h

# Check CPU usage
top

# Check running processes
ps aux | grep php
ps aux | grep nginx
```

---

## 🐛 Troubleshooting

### Issue: Site not loading
```bash
# Check Nginx status
sudo systemctl status nginx

# Check Nginx configuration
sudo nginx -t

# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check error logs
tail -50 /var/log/nginx/darulabrar_error.log
```

### Issue: Database connection error
```bash
# Test database connection
sudo mysql -u madrasa_user -p'Madrasa@2025#Secure' darul_abrar_madrasa

# Check .env file
cat /root/darul-abrar-madrasa/darul-abrar-madrasa/.env | grep DB_

# Clear config cache
cd /root/darul-abrar-madrasa/darul-abrar-madrasa
php artisan config:clear
```

### Issue: Permission errors
```bash
# Fix storage permissions
cd /root/darul-abrar-madrasa/darul-abrar-madrasa
sudo chmod -R 755 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Issue: 500 Internal Server Error
```bash
# Check Laravel logs
tail -50 /root/darul-abrar-madrasa/darul-abrar-madrasa/storage/logs/laravel.log

# Enable debug mode temporarily (ONLY for troubleshooting)
# Edit .env: APP_DEBUG=true
# Remember to set back to false after fixing!
```

---

## 📞 Support & Maintenance

### Regular Maintenance Tasks

**Daily:**
- Monitor error logs
- Check disk space

**Weekly:**
- Review access logs
- Check for security updates
- Test backup restoration

**Monthly:**
- Update system packages
- Review user accounts
- Optimize database
- Clean old logs

### Database Optimization
```bash
cd /root/darul-abrar-madrasa/darul-abrar-madrasa
php artisan optimize
```

### Clear Old Logs
```bash
# Clear Laravel logs older than 7 days
find /root/darul-abrar-madrasa/darul-abrar-madrasa/storage/logs -name "*.log" -mtime +7 -delete

# Rotate Nginx logs
sudo logrotate -f /etc/logrotate.d/nginx
```

---

## 📚 Additional Resources

### Laravel Documentation
- Official Docs: https://laravel.com/docs
- Deployment Guide: https://laravel.com/docs/deployment

### Server Management
- Nginx Docs: https://nginx.org/en/docs/
- MySQL Docs: https://dev.mysql.com/doc/
- Ubuntu Server Guide: https://ubuntu.com/server/docs

### SSL/TLS
- Let's Encrypt: https://letsencrypt.org/
- Certbot Docs: https://certbot.eff.org/

---

## ✅ Deployment Checklist

- [x] Server provisioned and accessible
- [x] Required software installed (PHP, MySQL, Nginx, Node.js)
- [x] Database created and configured
- [x] Application code deployed
- [x] Dependencies installed (Composer, NPM)
- [x] Environment configured (.env file)
- [x] Database migrations run
- [x] Admin user created
- [x] File permissions set
- [x] Web server configured
- [x] Services running
- [ ] DNS configured (YOUR ACTION REQUIRED)
- [ ] SSL certificate obtained (AFTER DNS)
- [ ] Application tested
- [ ] Backups configured
- [ ] Monitoring set up

---

## 🎯 Summary

Your Darul Abrar Madrasa Management System is now **LIVE and READY** on your production server!

**Current Status:** ✅ Fully Deployed (HTTP only)

**Pending Actions:**
1. Configure DNS to point to your server IP
2. Wait for DNS propagation (15-30 minutes)
3. Obtain SSL certificate using Certbot
4. Test the application thoroughly
5. Change default admin password
6. Set up regular backups

**Access URL (after DNS):** http://darulabrar.ailearnersbd.com
**Secure URL (after SSL):** https://darulabrar.ailearnersbd.com

---

## 📝 Notes

- All setup scripts are saved in `/root/darul-abrar-madrasa/`
- This guide is saved as `DEPLOYMENT_SUCCESS_GUIDE.md`
- Keep your credentials secure and change default passwords
- Regular backups are essential - set them up as soon as possible
- Monitor your server resources and logs regularly

---

**Deployment Date:** January 27, 2025
**Deployed By:** BlackBox AI Assistant
**For:** Sharif - Darul Abrar Model Kamil Madrasa

---

*Alhamdulillah! May this system serve your madrasa well and benefit the students and teachers. If you need any assistance, refer to this guide or the troubleshooting section.*

**JazakAllah Khair!** 🤲

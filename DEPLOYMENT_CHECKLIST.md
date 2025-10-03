# ✅ Deployment Checklist - Darul Abrar Madrasa

## 📋 Pre-Deployment Checklist

### Server Requirements
- [ ] PHP 8.2+ installed
- [ ] MySQL 8.0+ or PostgreSQL 13+ installed
- [ ] Composer 2.x installed
- [ ] Node.js 18+ and NPM installed
- [ ] Nginx or Apache web server configured
- [ ] SSL certificate installed (for production)
- [ ] Minimum 2GB RAM available
- [ ] Minimum 10GB storage available

### Server Software Installation (Ubuntu/Debian)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP and extensions
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml \
    php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath

# Install MySQL
sudo apt install -y mysql-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx

# Install Git
sudo apt install -y git
```

---

## 🚀 Deployment Steps

### Step 1: Clone Repository
```bash
cd /var/www
sudo git clone https://github.com/your-username/darul-abrar-madrasa.git
cd darul-abrar-madrasa
```

### Step 2: Set Permissions
```bash
sudo chown -R www-data:www-data /var/www/darul-abrar-madrasa
sudo chmod -R 755 /var/www/darul-abrar-madrasa
sudo chmod -R 775 /var/www/darul-abrar-madrasa/storage
sudo chmod -R 775 /var/www/darul-abrar-madrasa/bootstrap/cache
```

### Step 3: Install Dependencies
```bash
cd /var/www/darul-abrar-madrasa/darul-abrar-madrasa
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### Step 4: Environment Configuration
```bash
cp .env.example .env
nano .env
```

**Production .env settings:**
```env
APP_NAME="Darul Abrar Madrasa"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=darul_abrar_madrasa
DB_USERNAME=madrasa_user
DB_PASSWORD=strong_password_here

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@darulabrar.com
MAIL_FROM_NAME="${APP_NAME}"

SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

### Step 5: Generate Application Key
```bash
php artisan key:generate
```

### Step 6: Database Setup
```bash
# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE darul_abrar_madrasa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'madrasa_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON darul_abrar_madrasa.* TO 'madrasa_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link
```

### Step 7: Create Admin User
```bash
php artisan tinker
```

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@darulabrar.com';
$user->password = Hash::make('SecurePassword123!');
$user->role = 'admin';
$user->is_active = true;
$user->save();
exit;
```

### Step 8: Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Step 9: Configure Nginx

Create Nginx configuration:
```bash
sudo nano /etc/nginx/sites-available/darulabrar
```

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/darul-abrar-madrasa/darul-abrar-madrasa/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/darulabrar /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 10: SSL Certificate (Let's Encrypt)
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

### Step 11: Setup Cron Jobs
```bash
sudo crontab -e
```

Add this line:
```
* * * * * cd /var/www/darul-abrar-madrasa/darul-abrar-madrasa && php artisan schedule:run >> /dev/null 2>&1
```

### Step 12: Setup Queue Worker (Optional)
```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/darul-abrar-madrasa/darul-abrar-madrasa/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/darul-abrar-madrasa/darul-abrar-madrasa/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## 🔒 Security Checklist

### Application Security
- [ ] APP_DEBUG=false in production
- [ ] Strong APP_KEY generated
- [ ] Database credentials secured
- [ ] File permissions set correctly (755 for directories, 644 for files)
- [ ] Storage and cache directories writable (775)
- [ ] .env file not accessible from web
- [ ] SSL certificate installed
- [ ] HTTPS enforced

### Server Security
- [ ] Firewall configured (UFW)
- [ ] SSH key authentication enabled
- [ ] Root login disabled
- [ ] Fail2ban installed
- [ ] Regular security updates enabled
- [ ] Database accessible only from localhost
- [ ] Unnecessary services disabled

### Firewall Setup
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

---

## 🧪 Post-Deployment Testing

### Functional Testing
- [ ] Homepage loads correctly
- [ ] Login works (admin@darulabrar.com)
- [ ] Dashboard displays properly
- [ ] Student registration works
- [ ] Teacher registration works
- [ ] Attendance marking works
- [ ] Exam creation works
- [ ] Result entry works
- [ ] Fee management works
- [ ] Notice creation works
- [ ] File uploads work (avatars)
- [ ] PDF generation works (invoices, mark sheets)

### Performance Testing
- [ ] Page load time < 3 seconds
- [ ] Database queries optimized
- [ ] Images optimized
- [ ] CSS/JS minified
- [ ] Caching working

### Security Testing
- [ ] SQL injection protection
- [ ] XSS protection
- [ ] CSRF protection
- [ ] File upload validation
- [ ] Password strength enforcement
- [ ] Session security

---

## 📊 Monitoring Setup

### Log Monitoring
```bash
# Laravel logs
tail -f /var/www/darul-abrar-madrasa/darul-abrar-madrasa/storage/logs/laravel.log

# Nginx access logs
tail -f /var/log/nginx/access.log

# Nginx error logs
tail -f /var/log/nginx/error.log

# PHP-FPM logs
tail -f /var/log/php8.2-fpm.log
```

### Disk Space Monitoring
```bash
df -h
du -sh /var/www/darul-abrar-madrasa
```

### Database Size
```sql
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'darul_abrar_madrasa'
GROUP BY table_schema;
```

---

## 🔄 Backup Strategy

### Daily Backup Script
Create `/root/backup-madrasa.sh`:

```bash
#!/bin/bash

# Configuration
BACKUP_DIR="/root/backups"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="darul_abrar_madrasa"
DB_USER="madrasa_user"
DB_PASS="your_password"
APP_DIR="/var/www/darul-abrar-madrasa"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Backup files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz $APP_DIR/darul-abrar-madrasa/storage

# Delete backups older than 7 days
find $BACKUP_DIR -type f -mtime +7 -delete

echo "Backup completed: $DATE"
```

Make executable and add to cron:
```bash
chmod +x /root/backup-madrasa.sh
sudo crontab -e
# Add: 0 2 * * * /root/backup-madrasa.sh >> /root/backup.log 2>&1
```

---

## 📱 Maintenance Mode

### Enable Maintenance Mode
```bash
php artisan down --message="System maintenance in progress" --retry=60
```

### Disable Maintenance Mode
```bash
php artisan up
```

---

## 🆘 Troubleshooting

### Issue: 500 Internal Server Error
```bash
# Check Laravel logs
tail -100 storage/logs/laravel.log

# Check Nginx error logs
sudo tail -100 /var/log/nginx/error.log

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Issue: Permission Denied
```bash
sudo chown -R www-data:www-data /var/www/darul-abrar-madrasa
sudo chmod -R 755 /var/www/darul-abrar-madrasa
sudo chmod -R 775 storage bootstrap/cache
```

### Issue: Database Connection Failed
```bash
# Test MySQL connection
mysql -u madrasa_user -p darul_abrar_madrasa

# Check MySQL service
sudo systemctl status mysql

# Restart MySQL
sudo systemctl restart mysql
```

### Issue: Nginx Not Starting
```bash
# Test Nginx configuration
sudo nginx -t

# Check Nginx logs
sudo tail -100 /var/log/nginx/error.log

# Restart Nginx
sudo systemctl restart nginx
```

---

## 📞 Support Contacts

- **Technical Support:** [Your Email]
- **Server Provider:** Contabo Support
- **Emergency Contact:** [Your Phone]

---

## ✅ Final Checklist

- [ ] All deployment steps completed
- [ ] Security measures implemented
- [ ] Backups configured
- [ ] Monitoring setup
- [ ] Testing completed
- [ ] Documentation updated
- [ ] Admin credentials secured
- [ ] Team trained
- [ ] Go-live date confirmed

---

**Deployment Date:** _______________  
**Deployed By:** _______________  
**Verified By:** _______________  
**Status:** _______________

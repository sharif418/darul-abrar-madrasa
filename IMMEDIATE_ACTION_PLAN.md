# 🚀 তাৎক্ষণিক কর্ম পরিকল্পনা (Immediate Action Plan)

## 📅 আজকের কাজ - Phase 1: Critical Setup & Verification

### ✅ Step 1: Environment Check (15 মিনিট)

```bash
# 1. Current directory check করুন
pwd
# Expected: /root/darul-abrar-madrasa

# 2. Laravel project directory তে যান
cd darul-abrar-madrasa

# 3. PHP version check করুন
php -v
# Required: PHP 8.2 or higher

# 4. Composer check করুন
composer --version

# 5. Node.js check করুন
node -v
npm -v
```

### ✅ Step 2: Dependencies Installation (10-15 মিনিট)

```bash
# 1. Composer dependencies install করুন
composer install

# 2. NPM dependencies install করুন
npm install

# 3. Generate application key (যদি না থাকে)
php artisan key:generate
```

### ✅ Step 3: Environment Configuration (10 মিনিট)

```bash
# 1. .env file check করুন (যদি না থাকে তাহলে copy করুন)
cp .env.example .env

# 2. .env file edit করুন
nano .env

# নিচের settings update করুন:
# APP_NAME="Darul Abrar Madrasa"
# APP_ENV=local
# APP_DEBUG=true
# APP_URL=http://your-server-ip

# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=darul_abrar_madrasa
# DB_USERNAME=your_db_user
# DB_PASSWORD=your_db_password
```

### ✅ Step 4: Database Setup (10 মিনিট)

```bash
# 1. MySQL তে login করুন
mysql -u root -p

# 2. Database তৈরি করুন
CREATE DATABASE darul_abrar_madrasa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 3. User তৈরি করুন (optional, security জন্য ভালো)
CREATE USER 'madrasa_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON darul_abrar_madrasa.* TO 'madrasa_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# 4. Database connection test করুন
php artisan migrate:status
```

### ✅ Step 5: Fix Missing Migration Fields (15 মিনিট)

আমি এখন missing fields এর জন্য migration তৈরি করব:

```bash
# Migration তৈরি করুন
php artisan make:migration add_missing_fields_to_exams_and_results_table
```

### ✅ Step 6: Run Migrations (5 মিনিট)

```bash
# সব migrations run করুন
php artisan migrate

# যদি error আসে, তাহলে fresh migration করুন (সাবধান: সব data মুছে যাবে)
php artisan migrate:fresh
```

### ✅ Step 7: Storage Link (2 মিনিট)

```bash
# Storage link তৈরি করুন (avatar upload এর জন্য)
php artisan storage:link
```

### ✅ Step 8: Create Admin User (5 মিনিট)

```bash
# Tinker দিয়ে admin user তৈরি করুন
php artisan tinker

# Tinker console এ এই code run করুন:
```

```php
$user = new App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@darulabrar.com';
$user->password = Hash::make('admin123456');
$user->role = 'admin';
$user->is_active = true;
$user->save();
exit;
```

### ✅ Step 9: Build Frontend Assets (5 মিনিট)

```bash
# Development build
npm run dev

# অথবা production build
npm run build
```

### ✅ Step 10: Start Development Server (2 মিনিট)

```bash
# Laravel development server start করুন
php artisan serve --host=0.0.0.0 --port=8000

# অথবা যদি আপনার Nginx/Apache configured থাকে
# তাহলে browser এ যান: http://your-server-ip
```

---

## 🧪 Testing Checklist (30 মিনিট)

### 1. Login Test
- [ ] Admin login করুন (admin@darulabrar.com / admin123456)
- [ ] Dashboard দেখুন
- [ ] Profile page check করুন

### 2. Student Management Test
- [ ] নতুন student তৈরি করুন
- [ ] Student list দেখুন
- [ ] Student profile দেখুন
- [ ] Student edit করুন

### 3. Teacher Management Test
- [ ] নতুন teacher তৈরি করুন
- [ ] Teacher list দেখুন
- [ ] Teacher profile দেখুন

### 4. Academic Setup Test
- [ ] Department তৈরি করুন
- [ ] Class তৈরি করুন
- [ ] Subject তৈরি করুন
- [ ] Grading scale setup করুন

### 5. Attendance Test
- [ ] Attendance mark করুন
- [ ] Attendance report দেখুন

### 6. Exam & Result Test
- [ ] Exam তৈরি করুন
- [ ] Marks entry করুন
- [ ] Result দেখুন

### 7. Fee Management Test
- [ ] Fee তৈরি করুন
- [ ] Payment record করুন
- [ ] Invoice generate করুন

### 8. Notice Test
- [ ] Notice তৈরি করুন
- [ ] Notice list দেখুন

---

## 🐛 Common Issues & Solutions

### Issue 1: "Class not found" error
```bash
# Solution:
composer dump-autoload
php artisan clear-compiled
php artisan config:clear
php artisan cache:clear
```

### Issue 2: "Permission denied" for storage
```bash
# Solution:
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Issue 3: "SQLSTATE connection refused"
```bash
# Solution:
# 1. MySQL service চালু আছে কিনা check করুন
sudo systemctl status mysql

# 2. যদি বন্ধ থাকে তাহলে start করুন
sudo systemctl start mysql

# 3. .env file এ database credentials সঠিক আছে কিনা check করুন
```

### Issue 4: "npm run dev" fails
```bash
# Solution:
# 1. node_modules delete করুন
rm -rf node_modules package-lock.json

# 2. আবার install করুন
npm install

# 3. আবার try করুন
npm run dev
```

### Issue 5: "Mix manifest not found"
```bash
# Solution:
npm run build
```

---

## 📝 Bug Tracking Template

যখন testing করবেন, bugs পেলে এই format এ note করুন:

```markdown
### Bug #1: [Short Description]
- **Module:** [e.g., Student Management]
- **Page:** [e.g., Student Create Form]
- **Steps to Reproduce:**
  1. Step 1
  2. Step 2
  3. Step 3
- **Expected Result:** [What should happen]
- **Actual Result:** [What actually happened]
- **Error Message:** [If any]
- **Priority:** [High/Medium/Low]
- **Status:** [Open/In Progress/Fixed]
```

---

## 📊 Progress Tracking

আজকের কাজ শেষ হলে এই checklist fill করুন:

### Setup Completion:
- [ ] Environment verified
- [ ] Dependencies installed
- [ ] Database configured
- [ ] Migrations run successfully
- [ ] Storage link created
- [ ] Admin user created
- [ ] Frontend assets built
- [ ] Server running

### Testing Completion:
- [ ] Login working
- [ ] Student management working
- [ ] Teacher management working
- [ ] Academic setup working
- [ ] Attendance working
- [ ] Exam & Results working
- [ ] Fee management working
- [ ] Notice working

### Issues Found:
- Total bugs found: ___
- Critical bugs: ___
- Medium bugs: ___
- Low priority bugs: ___

---

## 🎯 আগামীকালের পরিকল্পনা

Setup এবং testing complete হলে আগামীকাল:

1. **Bug Fixing Session:**
   - আজকে পাওয়া সব bugs fix করুন
   - Re-test করুন

2. **Missing Migration Fix:**
   - Exam table এ fields যোগ করুন
   - Result table এ fields যোগ করুন

3. **Validation

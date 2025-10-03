# 🎉 দারুল আবরার মাদরাসা - সেটআপ সম্পূর্ণ গাইড

## ✅ সেটআপ স্ট্যাটাস: সম্পূর্ণ

আলহামদুলিল্লাহ! আপনার দারুল আবরার মাদরাসা ম্যানেজমেন্ট সিস্টেম সফলভাবে আপনার Contabo VPS সার্ভারে ডিপ্লয় করা হয়েছে।

---

## 📊 সার্ভার তথ্য

### সার্ভার বিস্তারিত
- **সার্ভার IP (IPv4):** 207.180.223.39
- **সার্ভার IP (IPv6):** 2a02:c207:2282:4950::1
- **ডোমেইন:** darulabrar.ailearnersbd.com
- **অপারেটিং সিস্টেম:** Ubuntu 24.04 LTS
- **ওয়েব সার্ভার:** Nginx 1.24.0
- **PHP ভার্সন:** 8.2.29
- **ডাটাবেস:** MySQL 8.0.43

---

## 🔐 লগইন তথ্য

### অ্যাডমিন লগইন
```
URL: http://darulabrar.ailearnersbd.com/login
ইমেইল: admin@darulabrar.com
পাসওয়ার্ড: Admin@2025
```

### ডাটাবেস অ্যাক্সেস
```
ডাটাবেস নাম: darul_abrar_madrasa
ইউজারনেম: madrasa_user
পাসওয়ার্ড: Madrasa@2025#Secure
```

---

## ✅ যা যা সম্পন্ন হয়েছে

### ডাটাবেস সেটআপ
- ✅ MySQL ইনস্টল ও কনফিগার করা হয়েছে
- ✅ ডাটাবেস তৈরি হয়েছে
- ✅ ১৭টি টেবিল সফলভাবে তৈরি হয়েছে:
  - users (ইউজার)
  - departments (বিভাগ)
  - classes (ক্লাস)
  - students (ছাত্র)
  - teachers (শিক্ষক)
  - subjects (বিষয়)
  - attendances (উপস্থিতি)
  - exams (পরীক্ষা)
  - results (ফলাফল)
  - fees (ফি)
  - notices (নোটিস)
  - grading_scales (গ্রেডিং স্কেল)
  - lesson_plans (পাঠ পরিকল্পনা)
  - study_materials (পড়ার উপকরণ)
  - এবং আরো...
- ✅ অ্যাডমিন ইউজার তৈরি হয়েছে

### অ্যাপ্লিকেশন কনফিগারেশন
- ✅ Laravel প্রোডাকশনের জন্য কনফিগার করা হয়েছে
- ✅ ডাটাবেস কানেকশন সেট করা হয়েছে
- ✅ ফাইল পারমিশন সেট করা হয়েছে
- ✅ Composer dependencies ইনস্টল হয়েছে
- ✅ NPM dependencies ইনস্টল হয়েছে
- ✅ Frontend assets বিল্ড হয়েছে

### ওয়েব সার্ভার সেটআপ
- ✅ Nginx ইনস্টল ও কনফিগার করা হয়েছে
- ✅ PHP-FPM চালু আছে
- ✅ Certbot ইনস্টল হয়েছে (SSL এর জন্য)

---

## 🚀 পরবর্তী পদক্ষেপ (গুরুত্বপূর্ণ!)

### ধাপ ১: DNS কনফিগার করুন (আবশ্যক)

আপনার ডোমেইন প্রোভাইডারের ওয়েবসাইটে যান এবং DNS সেটিংসে নিচের তথ্য যোগ করুন:

**A Record যোগ করুন:**
```
Type: A
Name: @ (অথবা darulabrar)
Value: 207.180.223.39
TTL: 3600 (অথবা Auto)
```

**CNAME Record যোগ করুন (www এর জন্য):**
```
Type: CNAME
Name: www
Value: darulabrar.ailearnersbd.com
TTL: 3600 (অথবা Auto)
```

**অপেক্ষা করুন:** DNS propagate হতে ১৫-৩০ মিনিট সময় লাগবে।

**DNS চেক করুন (আপনার কম্পিউটার থেকে):**
```bash
ping darulabrar.ailearnersbd.com
```

---

### ধাপ ২: SSL সার্টিফিকেট পান (DNS এর পরে)

DNS propagate হওয়ার পর, সার্ভারে এই কমান্ড চালান:

```bash
sudo certbot --nginx -d darulabrar.ailearnersbd.com -d www.darulabrar.ailearnersbd.com --email your-email@example.com --agree-tos --no-eff-email
```

**নোট:** `your-email@example.com` এর জায়গায় আপনার আসল ইমেইল দিন।

এটি করবে:
- Let's Encrypt থেকে ফ্রি SSL সার্টিফিকেট পাবে
- Nginx কে HTTPS এর জন্য কনফিগার করবে
- অটোমেটিক সার্টিফিকেট রিনিউয়াল সেট করবে

SSL এর পর আপনার সাইট এখানে পাওয়া যাবে:
- **https://darulabrar.ailearnersbd.com** ✅

---

### ধাপ ৩: অ্যাপ্লিকেশন টেস্ট করুন

১. **ব্রাউজারে খুলুন:**
   - http://darulabrar.ailearnersbd.com (অথবা SSL এর পর https://)

২. **অ্যাডমিন দিয়ে লগইন করুন:**
   ```
   ইমেইল: admin@darulabrar.com
   পাসওয়ার্ড: Admin@2025
   ```

৩. **মূল ফিচার টেস্ট করুন:**
   - ড্যাশবোর্ড লোড হচ্ছে কিনা
   - বিভাগ তৈরি করতে পারছেন কিনা
   - ক্লাস তৈরি করতে পারছেন কিনা
   - ছাত্র যোগ করতে পারছেন কিনা
   - শিক্ষক যোগ করতে পারছেন কিনা
   - উপস্থিতি নিতে পারছেন কিনা
   - পরীক্ষা তৈরি করতে পারছেন কিনা
   - ফলাফল এন্ট্রি করতে পারছেন কিনা
   - ফি ম্যানেজ করতে পারছেন কিনা

---

## 📁 গুরুত্বপূর্ণ ফাইল লোকেশন

### অ্যাপ্লিকেশন ফাইল
```
প্রজেক্ট রুট: /root/darul-abrar-madrasa/darul-abrar-madrasa
পাবলিক ডিরেক্টরি: /root/darul-abrar-madrasa/darul-abrar-madrasa/public
এনভায়রনমেন্ট ফাইল: /root/darul-abrar-madrasa/darul-abrar-madrasa/.env
```

### লগ ফাইল
```
Laravel লগ: /root/darul-abrar-madrasa/darul-abrar-madrasa/storage/logs/laravel.log
Nginx Access লগ: /var/log/nginx/darulabrar_access.log
Nginx Error লগ: /var/log/nginx/darulabrar_error.log
```

---

## 🛠️ দরকারি কমান্ড

### অ্যাপ্লিকেশন ম্যানেজমেন্ট
```bash
# প্রজেক্টে যান
cd /root/darul-abrar-madrasa/darul-abrar-madrasa

# ক্যাশ ক্লিয়ার করুন
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# প্রোডাকশনের জন্য অপটিমাইজ করুন
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### সার্ভিস ম্যানেজমেন্ট
```bash
# সার্ভিস রিস্টার্ট করুন
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart mysql

# সার্ভিস স্ট্যাটাস চেক করুন
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql
```

### লগ মনিটরিং
```bash
# Laravel লগ দেখুন
tail -f /root/darul-abrar-madrasa/darul-abrar-madrasa/storage/logs/laravel.log

# Nginx error লগ দেখুন
tail -f /var/log/nginx/darulabrar_error.log
```

### ডাটাবেস ম্যানেজমেন্ট
```bash
# MySQL এ প্রবেশ করুন
sudo mysql

# ডাটাবেস ব্যাকআপ নিন
sudo mysqldump -u madrasa_user -p darul_abrar_madrasa > backup_$(date +%Y%m%d).sql

# ডাটাবেস রিস্টোর করুন
sudo mysql -u madrasa_user -p darul_abrar_madrasa < backup_20250127.sql
```

---

## 🔒 নিরাপত্তা সুপারিশ

### ১. ডিফল্ট পাসওয়ার্ড পরিবর্তন করুন
```
প্রথম লগইনের পর অ্যাডমিন পাসওয়ার্ড পরিবর্তন করুন
Profile > Change Password এ যান
```

### ২. সিস্টেম নিয়মিত আপডেট করুন
```bash
sudo apt update && sudo apt upgrade -y
```

### ৩. ফায়ারওয়াল কনফিগার করুন
```bash
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw enable
```

### ৪. নিয়মিত ব্যাকআপ নিন
- প্রতিদিন ডাটাবেস ব্যাকআপ নিন
- প্রতি সপ্তাহে ফাইল ব্যাকআপ নিন
- ব্যাকআপ অন্য জায়গায় সংরক্ষণ করুন

---

## 🐛 সমস্যা সমাধান

### সমস্যা: সাইট লোড হচ্ছে না
```bash
# Nginx স্ট্যাটাস চেক করুন
sudo systemctl status nginx

# Nginx কনফিগারেশন টেস্ট করুন
sudo nginx -t

# PHP-FPM স্ট্যাটাস চেক করুন
sudo systemctl status php8.2-fpm

# Error লগ দেখুন
tail -50 /var/log/nginx/darulabrar_error.log
```

### সমস্যা: ডাটাবেস কানেকশন এরর
```bash
# ডাটাবেস কানেকশন টেস্ট করুন
sudo mysql -u madrasa_user -p'Madrasa@2025#Secure' darul_abrar_madrasa

# .env ফাইল চেক করুন
cat /root/darul-abrar-madrasa/darul-abrar-madrasa/.env | grep DB_

# Config ক্যাশ ক্লিয়ার করুন
cd /root/darul-abrar-madrasa/darul-abrar-madrasa
php artisan config:clear
```

### সমস্যা: Permission এরর
```bash
# Storage পারমিশন ঠিক করুন
cd /root/darul-abrar-madrasa/darul-abrar-madrasa
sudo chmod -R 755 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

---

## 📞 নিয়মিত রক্ষণাবেক্ষণ

### প্রতিদিন:
- Error লগ মনিটর করুন
- Disk space চেক করুন

### প্রতি সপ্তাহে:
- Access লগ রিভিউ করুন
- Security আপডেট চেক করুন
- ব্যাকআপ টেস্ট করুন

### প্রতি মাসে:
- সিস্টেম প্যাকেজ আপডেট করুন
- ইউজার অ্যাকাউন্ট রিভিউ করুন
- ডাটাবেস অপটিমাইজ করুন
- পুরনো লগ ক্লিন করুন

---

## ✅ চেকলিস্ট

- [x] সার্ভার সেটআপ সম্পূর্ণ
- [x] সফটওয়্যার ইনস্টল হয়েছে
- [x] ডাটাবেস তৈরি হয়েছে
- [x] অ্যাপ্লিকেশন ডিপ্লয় হয়েছে
- [x] Dependencies ইনস্টল হয়েছে
- [x] Environment কনফিগার হয়েছে
- [x] Migrations রান হয়েছে
- [x] অ্যাডমিন ইউজার তৈরি হয়েছে
- [x] ফাইল পারমিশন সেট হয়েছে
- [x] ওয়েব সার্ভার কনফিগার হয়েছে
- [x] সার্ভিস চালু আছে
- [ ] DNS কনফিগার করতে হবে (আপনার কাজ)
- [ ] SSL সার্টিফিকেট পেতে হবে (DNS এর পর)
- [ ] অ্যাপ্লিকেশন টেস্ট করতে হবে
- [ ] ব্যাকআপ সেটআপ করতে হবে

---

## 🎯 সারসংক্ষেপ

আপনার দারুল আবরার মাদরাসা ম্যানেজমেন্ট সিস্টেম এখন **লাইভ এবং প্রস্তুত**!

**বর্তমান স্ট্যাটাস:** ✅ সম্পূর্ণভাবে ডিপ্লয় হয়েছে (শুধু HTTP)

**বাকি কাজ:**
1. DNS কনফিগার করুন
2. DNS propagate হওয়ার জন্য অপেক্ষা করুন (১৫-৩০ মিনিট)
3. SSL সার্টিফিকেট পান
4. অ্যাপ্লিকেশন ভালোভাবে টেস্ট করুন
5. ডিফল্ট পাসওয়ার্ড পরিবর্তন করুন
6. নিয়মিত ব্যাকআপ সেটআপ করুন

**অ্যাক্সেস URL (DNS এর পর):** http://darulabrar.ailearnersbd.com
**সিকিউর URL (SSL এর পর):** https://darulabrar.ailearnersbd.com

---

## 📝 গুরুত্বপূর্ণ নোট

- সব সেটআপ স্ক্রিপ্ট `/root/darul-abrar-madrasa/` এ সংরক্ষিত আছে
- এই গাইড `BANGLA_SETUP_GUIDE.md` হিসেবে সংরক্ষিত আছে
- আপনার credentials সুরক্ষিত রাখুন এবং ডিফল্ট পাসওয়ার্ড পরিবর্তন করুন
- নিয়মিত ব্যাকআপ অত্যন্ত গুরুত্বপূর্ণ - যত তাড়াতাড়ি সম্ভব সেটআপ করুন
- আপনার সার্ভার রিসোর্স এবং লগ নিয়মিত মনিটর করুন

---

**ডিপ্লয়মেন্ট তারিখ:** ২৭ জানুয়ারি, ২০২৫
**ডিপ্লয় করেছেন:** BlackBox AI Assistant
**জন্য:** শরীফ ভাই - দারুল আবরার মডেল কামিল মাদরাসা

---

*আলহামদুলিল্লাহ! এই সিস্টেম আপনার মাদরাসার ছাত্র ও শিক্ষকদের ভালোভাবে সেবা করুক। যদি কোনো সাহায্যের প্রয়োজন হয়, এই গাইড বা সমস্যা সমাধান অংশ দেখুন।*

**জাযাকাল্লাহ খাইরান!** 🤲

---

## 📞 যোগাযোগ ও সাহায্য

যদি কোনো সমস্যা হয় বা সাহায্যের প্রয়োজন হয়:

1. প্রথমে এই গাইডের "সমস্যা সমাধান" অংশ দেখুন
2. লগ ফাইল চেক করুন
3. Error messages সংরক্ষণ করুন
4. প্রয়োজনে technical support এর সাথে যোগাযোগ করুন

**মনে রাখবেন:**
- সবসময় ব্যাকআপ নিয়ে কাজ করুন
- Production সার্ভারে সরাসরি টেস্ট করবেন না
- পরিবর্তন করার আগে ব্যাকআপ নিন
- নিয়মিত আপডেট করুন

**শুভকামনা রইলো!** 🌟

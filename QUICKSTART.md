# دليل التشغيل السريع | Quick Start Guide

## البدء السريع | Quick Start

### 1. التثبيت الأساسي | Basic Installation

```bash
# نسخ المشروع | Clone project
git clone <repository-url>
cd formio-backend

# تثبيت التبعيات | Install dependencies
composer install

# نسخ ملف البيئة | Copy environment file
cp .env.example .env

# توليد مفتاح التطبيق | Generate app key
php artisan key:generate
```

### 2. إعداد قاعدة البيانات السريع | Quick Database Setup

**للتطوير السريع (SQLite):**
```bash
# تثبيت SQLite إذا لم يكن مثبتاً | Install SQLite if not installed
sudo apt install sqlite3 php-sqlite3

# إنشاء ملف قاعدة البيانات | Create database file
touch database/database.sqlite

# تحديث .env للـ SQLite | Update .env for SQLite
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i 's|DB_DATABASE=formio_backend|DB_DATABASE='$(pwd)'/database/database.sqlite|' .env
```

**للإنتاج (MySQL):**
```bash
# إنشاء قاعدة البيانات | Create database
mysql -u root -p -e "CREATE DATABASE formio_backend CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# تحديث .env للـ MySQL | Update .env for MySQL
# تحرير الملف يدوياً أو استخدام sed
```

### 3. تشغيل المشروع | Run Project

```bash
# تشغيل Migrations | Run migrations
php artisan migrate

# إضافة البيانات التجريبية | Add sample data
php artisan db:seed

# تشغيل الخادم | Start server
php artisan serve
```

الخادم سيعمل على: `http://localhost:8000`

## اختبار سريع | Quick Test

### اختبار الصحة | Health Check
```bash
curl http://localhost:8000/api/v1/health
```

### تسجيل مستخدم جديد | Register New User
```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "language": "en"
  }'
```

### تسجيل الدخول | Login
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@formio.com",
    "password": "password123"
  }'
```

### الحصول على النماذج العامة | Get Public Forms
```bash
curl http://localhost:8000/api/v1/forms/public
```

## المستخدمون التجريبيون | Sample Users

| البريد الإلكتروني | كلمة المرور | الدور | اللغة |
|-------------------|-------------|-------|-------|
| admin@formio.com | password123 | admin | en |
| admin.ar@formio.com | password123 | admin | ar |
| user@formio.com | password123 | user | en |
| user.ar@formio.com | password123 | user | ar |

## API Endpoints الأساسية | Basic API Endpoints

### المصادقة | Authentication
- `POST /api/v1/register` - تسجيل مستخدم جديد
- `POST /api/v1/login` - تسجيل الدخول
- `POST /api/v1/logout` - تسجيل الخروج
- `GET /api/v1/user` - معلومات المستخدم

### النماذج | Forms
- `GET /api/v1/forms` - قائمة النماذج (مصادقة مطلوبة)
- `GET /api/v1/forms/public` - النماذج العامة
- `POST /api/v1/forms` - إنشاء نموذج جديد
- `GET /api/v1/forms/{id}` - عرض نموذج محدد

### المكونات | Components
- `GET /api/v1/components` - قائمة المكونات
- `GET /api/v1/components?category=basic` - مكونات أساسية
- `GET /api/v1/components?language=ar` - مكونات بالعربية

### الإرسالات | Submissions
- `GET /api/v1/submissions` - قائمة الإرسالات
- `POST /api/v1/forms/{id}/submit` - إرسال نموذج عام

## حل المشاكل السريع | Quick Troubleshooting

### مشكلة قاعدة البيانات | Database Issues
```bash
# تحقق من الاتصال | Check connection
php artisan tinker
# ثم في Tinker: DB::connection()->getPdo();
```

### مشكلة الصلاحيات | Permission Issues
```bash
# إصلاح صلاحيات المجلدات | Fix folder permissions
chmod -R 755 storage bootstrap/cache
chmod -R 644 database/database.sqlite  # للـ SQLite
```

### مشكلة CORS | CORS Issues
```bash
# تحقق من إعدادات CORS | Check CORS settings
php artisan config:clear
php artisan cache:clear
```

### إعادة تشغيل كاملة | Full Reset
```bash
# إعادة تعيين قاعدة البيانات | Reset database
php artisan migrate:fresh --seed

# مسح الكاش | Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

## التطوير | Development

### إضافة مكون جديد | Add New Component
```bash
# إنشاء seeder جديد | Create new seeder
php artisan make:seeder CustomComponentSeeder

# تشغيل seeder محدد | Run specific seeder
php artisan db:seed --class=CustomComponentSeeder
```

### إنشاء controller جديد | Create New Controller
```bash
php artisan make:controller Api/NewController --api
```

### إنشاء model جديد | Create New Model
```bash
php artisan make:model NewModel -m
```

## الإنتاج | Production

### تحسين الأداء | Performance Optimization
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### إعدادات الأمان | Security Settings
- تحديث `APP_ENV=production`
- تعيين `APP_DEBUG=false`
- استخدام HTTPS
- تحديث كلمات المرور الافتراضية

---

**للمزيد من التفاصيل، راجع ملفات README.md و SETUP.md**

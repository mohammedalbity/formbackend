# Form.io Backend API - Laravel 12

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

## نظرة عامة | Overview

**العربية:**
نظام backend شامل مبني على Laravel 12 لإدارة النماذج الديناميكية باستخدام Form.io مع دعم كامل للغة العربية والإنجليزية. يوفر النظام API متكامل لإنشاء وإدارة النماذج والمكونات والإرسالات مع نظام مصادقة آمن.

**English:**
A comprehensive Laravel 12 backend system for managing dynamic forms using Form.io with full Arabic and English language support. The system provides a complete API for creating and managing forms, components, and submissions with secure authentication.

## المميزات الرئيسية | Key Features

### 🔐 نظام المصادقة | Authentication System
- Laravel Sanctum للمصادقة الآمنة
- تسجيل دخول وإنشاء حسابات جديدة
- إدارة الأدوار (مدير/مستخدم)
- تحديث الملف الشخصي وتغيير كلمة المرور

### 📝 إدارة النماذج | Form Management
- إنشاء وتعديل النماذج الديناميكية
- دعم Form.io schema
- النماذج العامة والخاصة
- حالات النماذج (مسودة، منشور، مؤرشف)

### 🧩 مكونات النماذج | Form Components
- مكتبة شاملة من المكونات الأساسية والمتقدمة
- دعم المكونات المخصصة
- تصنيف المكونات (أساسي، متقدم، تخطيط)
- خصائص وقواعد التحقق لكل مكون

### 📊 إدارة الإرسالات | Submission Management
- استقبال ومعالجة إرسالات النماذج
- مراجعة وموافقة الإرسالات
- تتبع حالة الإرسالات
- إحصائيات مفصلة

### 🌐 الدعم متعدد اللغات | Multi-language Support
- دعم كامل للعربية والإنجليزية
- RTL/LTR support
- ترجمة جميع المكونات والرسائل
- إعدادات المنطقة الزمنية

## التقنيات المستخدمة | Tech Stack

- **Framework:** Laravel 12
- **Authentication:** Laravel Sanctum
- **Database:** MySQL/PostgreSQL/SQLite
- **API:** RESTful API
- **Language Support:** Arabic/English
- **Form Engine:** Form.io compatible

## هيكل المشروع | Project Structure

```
formio-backend/
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── AuthController.php
│   │   ├── FormController.php
│   │   ├── FormSubmissionController.php
│   │   └── FormComponentController.php
│   └── Models/
│       ├── User.php
│       ├── Form.php
│       ├── FormSubmission.php
│       └── FormComponent.php
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
└── config/
```

## التثبيت والإعداد | Installation & Setup

### 1. متطلبات النظام | System Requirements
- PHP 8.2+
- Composer
- MySQL/PostgreSQL/SQLite
- Node.js (اختياري)

### 2. تثبيت المشروع | Project Installation

```bash
# Clone the repository
git clone <repository-url>
cd formio-backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. إعداد قاعدة البيانات | Database Setup

**للـ MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=formio_backend
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**للـ SQLite:**
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
```

### 4. تشغيل Migrations والـ Seeders

```bash
# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed
```

### 5. تشغيل الخادم | Start Server

```bash
php artisan serve
```

الخادم سيعمل على: `http://localhost:8000`

## API Documentation

### Base URL
```
http://localhost:8000/api/v1
```

### Authentication Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/register` | تسجيل مستخدم جديد |
| POST | `/login` | تسجيل الدخول |
| POST | `/logout` | تسجيل الخروج |
| GET | `/user` | معلومات المستخدم |
| PUT | `/profile` | تحديث الملف الشخصي |
| PUT | `/change-password` | تغيير كلمة المرور |

### Forms Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/forms` | قائمة النماذج |
| POST | `/forms` | إنشاء نموذج جديد |
| GET | `/forms/{id}` | عرض نموذج محدد |
| PUT | `/forms/{id}` | تحديث نموذج |
| DELETE | `/forms/{id}` | حذف نموذج |
| GET | `/forms/public` | النماذج العامة |

### Form Submissions Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/submissions` | قائمة الإرسالات |
| POST | `/submissions` | إنشاء إرسال جديد |
| GET | `/submissions/{id}` | عرض إرسال محدد |
| PUT | `/submissions/{id}` | تحديث إرسال |
| DELETE | `/submissions/{id}` | حذف إرسال |
| POST | `/forms/{id}/submit` | إرسال نموذج عام |

### Form Components Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/components` | قائمة المكونات |
| POST | `/components` | إنشاء مكون جديد (مدير فقط) |
| GET | `/components/{id}` | عرض مكون محدد |
| PUT | `/components/{id}` | تحديث مكون (مدير فقط) |
| DELETE | `/components/{id}` | حذف مكون (مدير فقط) |

## بيانات تجريبية | Sample Data

النظام يأتي مع بيانات تجريبية تشمل:

### المستخدمين | Users
- **Admin (EN):** admin@formio.com / password123
- **Admin (AR):** admin.ar@formio.com / password123
- **User (EN):** user@formio.com / password123
- **User (AR):** user.ar@formio.com / password123

### المكونات | Components
- حقول النص الأساسية
- حقول البريد الإلكتروني والأرقام
- قوائم الاختيار والمربعات
- مكونات التاريخ والوقت
- رفع الملفات
- عناصر HTML المخصصة

## الأمان | Security

- **Authentication:** Laravel Sanctum tokens
- **Authorization:** Role-based access control
- **Validation:** Comprehensive input validation
- **CORS:** Configured for frontend integration
- **Rate Limiting:** API rate limiting enabled

## التطوير | Development

### إضافة مكونات جديدة | Adding New Components

```php
// في FormComponentSeeder.php
FormComponent::create([
    'type' => 'custom_component',
    'label' => 'Custom Component',
    'label_ar' => 'مكون مخصص',
    'key' => 'custom_component',
    'properties' => [...],
    'validation' => [...],
    'category' => 'advanced',
    // ...
]);
```

### إضافة API endpoints جديدة

```php
// في routes/api.php
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('new-resource', NewResourceController::class);
});
```

## الاختبار | Testing

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=AuthTest
```

## النشر | Deployment

### إعداد الإنتاج | Production Setup

1. تحديث متغيرات البيئة
2. تحسين الأداء
3. إعداد HTTPS
4. إعداد قاعدة البيانات الإنتاجية

```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## المساهمة | Contributing

نرحب بالمساهمات! يرجى اتباع الخطوات التالية:

1. Fork المشروع
2. إنشاء branch جديد للميزة
3. Commit التغييرات
4. Push إلى Branch
5. إنشاء Pull Request

## الترخيص | License

هذا المشروع مرخص تحت رخصة MIT. راجع ملف [LICENSE](LICENSE) للتفاصيل.

## الدعم | Support

للحصول على الدعم أو الإبلاغ عن مشاكل:
- إنشاء Issue في GitHub
- التواصل عبر البريد الإلكتروني

---

**تم تطويره بـ ❤️ باستخدام Laravel 12 | Developed with ❤️ using Laravel 12**
#   f o r m b a c k e n d  
 
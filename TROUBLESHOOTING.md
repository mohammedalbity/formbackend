# استكشاف الأخطاء وحلها | Troubleshooting Guide

## مشكلة: Error fetching users

### الأعراض:
```
TypeError: apiClient.get is not a function
فشل في تحميل البيانات
```

### الحل:
✅ **تم الإصلاح!** أضفنا دوال HTTP عامة في `apiClient.ts`

---

## التحقق من أن كل شيء يعمل

### 1. تشغيل Backend
```bash
cd c:\xampp\htdocs\form\formio-backend
php artisan serve
```

**يجب أن يعمل على:** `http://localhost:8000`

### 2. تشغيل Frontend
```bash
cd c:\xampp\htdocs\form\formio
npm run dev
```

**يجب أن يعمل على:** `http://localhost:5173`

### 3. التحقق من Database
```bash
# التأكد من أن MySQL يعمل
# افتح phpMyAdmin: http://localhost/phpmyadmin
# تحقق من وجود قاعدة البيانات: formio_backend
```

### 4. إنشاء Admin User
```bash
cd c:\xampp\htdocs\form\formio-backend
php artisan db:seed --class=AdminUserSeeder
```

**Credentials:**
- Email: `admin@formio.com`
- Password: `admin123456`

---

## اختبار API مباشرة

### Test 1: Health Check
```bash
curl http://localhost:8000/api/v1/health
```

**Expected Response:**
```json
{
  "status": "ok",
  "timestamp": "2025-10-22T10:00:00.000000Z"
}
```

### Test 2: Login as Admin
```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@formio.com",
    "password": "admin123456"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@formio.com",
      "role": "admin"
    },
    "token": "..."
  }
}
```

### Test 3: Get Users (Admin Only)
```bash
# استبدل YOUR_TOKEN بالـ token من الخطوة السابقة
curl http://localhost:8000/api/v1/admin/users \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Admin User",
        "email": "admin@formio.com",
        "role": "admin",
        "is_active": true,
        "forms_count": 0,
        "submissions_count": 0
      }
    ]
  }
}
```

---

## الأخطاء الشائعة وحلولها

### ❌ Error 1: CORS Error
**السبب:** Frontend و Backend على ports مختلفة

**الحل:**
تحقق من `config/cors.php`:
```php
'allowed_origins' => ['http://localhost:5173'],
```

### ❌ Error 2: 401 Unauthorized
**السبب:** Token غير صالح أو منتهي

**الحل:**
1. تسجيل دخول جديد
2. تحقق من أن Token يُحفظ في localStorage

### ❌ Error 3: 403 Forbidden
**السبب:** المستخدم ليس admin

**الحل:**
```bash
# ترقية مستخدم إلى admin
php artisan tinker
>>> $user = App\Models\User::find(1);
>>> $user->role = 'admin';
>>> $user->save();
```

### ❌ Error 4: Connection Refused
**السبب:** Backend غير مشغل

**الحل:**
```bash
php artisan serve
# أو
composer run dev
```

### ❌ Error 5: Database Connection Error
**السبب:** MySQL غير مشغل أو إعدادات .env خاطئة

**الحل:**
1. شغّل XAMPP وفعّل MySQL
2. تحقق من `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=formio_backend
DB_USERNAME=root
DB_PASSWORD=
```

---

## فحص Frontend

### 1. تحقق من Console
افتح Developer Tools (F12) وابحث عن أخطاء:

**✅ Good:**
```
✓ Token loaded
✓ User authenticated
✓ Request sent to /api/v1/admin/users
```

**❌ Bad:**
```
✗ apiClient.get is not a function
✗ 401 Unauthorized
✗ Network Error
```

### 2. تحقق من Network Tab
- هل الـ request يُرسل؟
- ما هو الـ status code؟
- هل الـ response موجود؟

### 3. تحقق من localStorage
```javascript
// في Console
localStorage.getItem('api_token')
localStorage.getItem('user_data')
```

---

## خطوات التشخيص الكاملة

### الخطوة 1: تحقق من Backend
```bash
# 1. Backend يعمل؟
curl http://localhost:8000/api/v1/health

# 2. Database متصل؟
php artisan migrate:status

# 3. Admin موجود؟
php artisan tinker
>>> App\Models\User::where('role', 'admin')->count()
```

### الخطوة 2: تحقق من Frontend
```bash
# 1. Dependencies مثبتة؟
npm install

# 2. Frontend يعمل؟
npm run dev

# 3. API URL صحيح؟
# تحقق من src/services/apiClient.ts
```

### الخطوة 3: تحقق من الاتصال
```bash
# من Frontend console
fetch('http://localhost:8000/api/v1/health')
  .then(r => r.json())
  .then(d => console.log(d))
```

---

## الحل السريع (Quick Fix)

إذا لم يعمل شيء، جرب:

```bash
# Backend
cd c:\xampp\htdocs\form\formio-backend
php artisan cache:clear
php artisan config:clear
php artisan route:clear
composer dump-autoload
php artisan serve

# Frontend (في نافذة أخرى)
cd c:\xampp\htdocs\form\formio
npm install
npm run dev

# Database
php artisan migrate:fresh --seed
php artisan db:seed --class=AdminUserSeeder
```

---

## جهات الاتصال للدعم

- **Backend Logs**: `storage/logs/laravel.log`
- **Frontend Console**: F12 → Console
- **Network Requests**: F12 → Network

---

## ✅ التأكد من النجاح

عندما يعمل كل شيء بشكل صحيح:

1. ✅ Backend يستجيب على `http://localhost:8000`
2. ✅ Frontend يعمل على `http://localhost:5173`
3. ✅ يمكن تسجيل الدخول كـ admin
4. ✅ صفحة "إدارة المستخدمين" تظهر
5. ✅ جدول المستخدمين يُحمّل بنجاح
6. ✅ جميع الأزرار تعمل

**إذا رأيت كل ذلك → النظام يعمل بشكل مثالي! 🎉**

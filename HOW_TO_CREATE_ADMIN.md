# كيفية إنشاء مستخدم Admin

## ⚠️ هام جداً
**لا يمكن إنشاء مستخدم Admin من صفحة التسجيل!**  
جميع التسجيلات الجديدة تكون كمستخدم عادي (user) فقط.

---

## 🎯 الطرق الصحيحة لإنشاء Admin

### الطريقة 1: استخدام Seeder (مُوصى بها) ⭐

```bash
# في مجلد المشروع
php artisan db:seed --class=AdminUserSeeder
```

**النتيجة:**
```
✅ Admin user created successfully!
Email: admin@formio.com
Password: admin123456

✅ Arabic admin user created successfully!
Email: admin.ar@formio.com
Password: admin123456
```

---

### الطريقة 2: استخدام Laravel Tinker

```bash
# فتح Tinker
php artisan tinker

# إنشاء admin جديد
>>> $admin = new App\Models\User();
>>> $admin->name = 'Admin Name';
>>> $admin->email = 'admin@example.com';
>>> $admin->password = Hash::make('your-secure-password');
>>> $admin->role = 'admin';
>>> $admin->language = 'ar';
>>> $admin->is_active = true;
>>> $admin->save();

# أو ترقية مستخدم موجود
>>> $user = App\Models\User::find(1);
>>> $user->role = 'admin';
>>> $user->save();
```

---

### الطريقة 3: من خلال Admin موجود (Admin Panel)

```http
PUT http://localhost:8000/api/v1/admin/users/{user_id}
Authorization: Bearer {admin_token}
Content-Type: application/json

{
  "role": "admin"
}
```

**ملاحظة:** يجب أن يكون لديك admin موجود مسبقاً لاستخدام هذه الطريقة.

---

### الطريقة 4: Database مباشرة (للطوارئ فقط)

```sql
-- الاتصال بقاعدة البيانات
mysql -u root -p

-- اختيار قاعدة البيانات
USE formio_backend;

-- ترقية مستخدم موجود
UPDATE users SET role = 'admin' WHERE id = 1;

-- أو إنشاء admin جديد
INSERT INTO users (name, email, password, role, language, is_active, created_at, updated_at) 
VALUES (
  'Admin User',
  'admin@example.com',
  '$2y$12$...',  -- استخدم Hash::make() من Laravel
  'admin',
  'ar',
  1,
  NOW(),
  NOW()
);
```

---

## 🔒 الحسابات التجريبية

بعد تشغيل Seeder:

| الاسم | البريد | كلمة المرور | اللغة |
|------|--------|-------------|-------|
| Admin User | admin@formio.com | admin123456 | English |
| مدير النظام | admin.ar@formio.com | admin123456 | العربية |

**⚠️ هام:** غيّر كلمة المرور فوراً بعد أول تسجيل دخول!

---

## ❌ ما لا يجب فعله

### ✗ محاولة التسجيل كـ Admin من Frontend
```javascript
// ❌ هذا لن يعمل أبداً!
const data = {
  name: "Admin",
  email: "admin@test.com",
  password: "pass123",
  role: "admin"  // ← سيتم تجاهله تماماً
}
```

النظام محمي بـ 4 طبقات أمان، وسيتم:
- تجاهل `role` في Backend
- فرض `role = 'user'` دائماً
- تسجيل المحاولة في logs

---

## 📋 التحقق من نجاح العملية

### من خلال API:
```bash
# تسجيل الدخول
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@formio.com",
    "password": "admin123456"
  }'

# التحقق من البيانات
# يجب أن يكون role: "admin"
```

### من خلال Database:
```sql
SELECT id, name, email, role FROM users WHERE role = 'admin';
```

---

## 🛠️ استكشاف الأخطاء

### المشكلة: "Admin already exists"
```bash
# الحل: تخطي أو حذف الموجود
php artisan tinker
>>> App\Models\User::where('role', 'admin')->delete();
>>> exit

# ثم تشغيل Seeder مرة أخرى
php artisan db:seed --class=AdminUserSeeder
```

### المشكلة: "Class AdminUserSeeder not found"
```bash
# إعادة بناء autoload
composer dump-autoload

# ثم المحاولة مرة أخرى
php artisan db:seed --class=AdminUserSeeder
```

---

## 📖 المزيد من المعلومات

راجع `REGISTRATION_SECURITY.md` لفهم كامل نظام الحماية.

---

## ✅ ملخص سريع

```bash
# الطريقة الأسرع والأسهل:
php artisan db:seed --class=AdminUserSeeder

# تسجيل الدخول:
Email: admin@formio.com
Password: admin123456

# ✅ جاهز للاستخدام!
```

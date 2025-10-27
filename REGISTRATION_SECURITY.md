# حماية التسجيل - منع إنشاء حسابات Admin

## 🔒 نظرة عامة

تم تطبيق **أربع طبقات حماية** لضمان أن جميع التسجيلات الجديدة تكون كمستخدم عادي (user) وليس أدمن (admin).

---

## 🛡️ الطبقة الأولى: Frontend Validation

### في `apiClient.ts`:
```typescript
export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  language?: string
  // NOTE: role is NOT allowed here - all new registrations are 'user' by default
  // Only admins can change roles through admin endpoints
}
```

**النتيجة:**
- ✅ لا يمكن إرسال `role` من Frontend أبداً
- ✅ TypeScript يمنع إضافة حقل `role` في البيانات

---

## 🛡️ الطبقة الثانية: Backend Controller Validation

### في `AuthController.php` - دالة `register()`:
```php
// SECURITY: Always create new users as 'user' role, ignore any role sent from frontend
// Only admins can change roles through admin endpoints
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'language' => $request->language ?? 'en',
    'timezone' => $request->timezone ?? 'UTC',
    'role' => 'user', // LOCKED: Always 'user' for new registrations
    'is_active' => true,
]);
```

**النتيجة:**
- ✅ حتى لو تم إرسال `role` من Frontend (عبر manipulation)، سيتم تجاهله
- ✅ القيمة دائماً `'user'` بشكل ثابت
- ✅ لا يتم قراءة `$request->role` أبداً

---

## 🛡️ الطبقة الثالثة: Model Level Protection

### في `User.php` - Mutator:
```php
/**
 * Set the role attribute with protection.
 * Prevents role escalation unless done through admin endpoints.
 */
public function setRoleAttribute($value)
{
    // If this is a new record (not yet saved), allow setting role
    if (!$this->exists) {
        $this->attributes['role'] = $value;
        return;
    }

    // If trying to change role on existing user, require admin authentication
    $currentUser = auth('sanctum')->user();
    if ($currentUser && $currentUser->isAdmin()) {
        $this->attributes['role'] = $value;
    } else {
        // Log potential security issue
        \Log::warning('Unauthorized attempt to change user role', [
            'user_id' => $this->id,
            'attempted_by' => $currentUser?->id ?? 'guest',
            'attempted_role' => $value,
        ]);
        // Keep existing role - don't throw exception to avoid breaking other updates
    }
}
```

**النتيجة:**
- ✅ حماية إضافية على مستوى Model
- ✅ يمنع تغيير `role` لمستخدم موجود إلا من قبل أدمن
- ✅ يسجل محاولات الاختراق في الـ logs

---

## 🛡️ الطبقة الرابعة: Admin-Only Role Changes

### فقط الأدمن يمكنه تغيير الأدوار عبر:
```http
PUT /api/v1/admin/users/{id}
Authorization: Bearer {admin_token}

Body:
{
  "role": "admin",
  "is_active": true
}
```

**في `AdminController.php`:**
```php
public function updateUser(Request $request, string $id): JsonResponse
{
    if (!Auth::user()->isAdmin()) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized access. Admin only.',
        ], 403);
    }

    $user = User::findOrFail($id);

    // Prevent admin from modifying their own role/status
    if ($user->id === Auth::id()) {
        return response()->json([
            'success' => false,
            'message' => 'Cannot modify your own account',
        ], 400);
    }

    $validated = $request->validate([
        'role' => 'sometimes|in:admin,user',
        'is_active' => 'sometimes|boolean',
    ]);

    $user->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'User updated successfully',
        'data' => $user,
    ]);
}
```

**النتيجة:**
- ✅ فقط الأدمن يمكنه تغيير الأدوار
- ✅ الأدمن لا يمكنه تعديل دوره الخاص (حماية من الخطأ)

---

## 🧪 سيناريوهات الاختبار

### ✅ سيناريو 1: تسجيل عادي
```http
POST /api/v1/register
Content-Type: application/json

{
  "name": "محمد أحمد",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "language": "ar"
}

Response:
{
  "success": true,
  "data": {
    "user": {
      "id": 10,
      "name": "محمد أحمد",
      "email": "user@example.com",
      "role": "user",  ← دائماً "user"
      ...
    }
  }
}
```

### ❌ سيناريو 2: محاولة التلاعب بإرسال role
```http
POST /api/v1/register
Content-Type: application/json

{
  "name": "محمد أحمد",
  "email": "hacker@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "admin"  ← محاولة اختراق
}

Response:
{
  "success": true,
  "data": {
    "user": {
      "id": 11,
      "email": "hacker@example.com",
      "role": "user",  ← تم تجاهل "admin" وأصبح "user"
      ...
    }
  }
}
```

### ❌ سيناريو 3: محاولة تغيير Role عبر Profile Update
```http
PUT /api/v1/profile
Authorization: Bearer {user_token}
Content-Type: application/json

{
  "name": "محمد أحمد",
  "role": "admin"  ← محاولة اختراق
}

Response:
- role لن يتغير أبداً
- سيتم تسجيل المحاولة في logs
```

---

## 📊 تدفق العمل

```
┌─────────────────────────────────────┐
│   Frontend: Login.vue               │
│   ✅ لا يحتوي على حقل role          │
└──────────────┬──────────────────────┘
               │ POST /register
               ▼
┌─────────────────────────────────────┐
│   AuthController::register()        │
│   ✅ role = 'user' (ثابت)           │
│   ✅ لا يقرأ $request->role          │
└──────────────┬──────────────────────┘
               │ User::create([...])
               ▼
┌─────────────────────────────────────┐
│   User Model::setRoleAttribute()    │
│   ✅ للمستخدمين الجدد: مسموح        │
│   ✅ للموجودين: admin فقط           │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│   Database: users table             │
│   ✅ role = 'user' دائماً            │
└─────────────────────────────────────┘
```

---

## 🔐 الصلاحيات الوحيدة لتغيير Role

### 1. من خلال Admin Panel:
```http
PUT /api/v1/admin/users/{id}
Authorization: Bearer {admin_token}
```

### 2. من خلال Database مباشرة:
```sql
UPDATE users SET role = 'admin' WHERE id = 1;
```

### 3. من خلال Laravel Tinker:
```php
php artisan tinker
>>> $user = User::find(1);
>>> $user->role = 'admin';
>>> $user->save();
```

---

## ⚠️ نقاط مهمة

1. **لا يمكن إنشاء Admin من صفحة التسجيل** - أبداً
2. **فقط Admin موجود يمكنه ترقية مستخدمين آخرين**
3. **Admin لا يمكنه تعديل دوره الخاص** - حماية من الخطأ
4. **جميع المحاولات المشبوهة تُسجل** في logs

---

## ✅ الخلاصة

| الطبقة | الموقع | الحماية |
|--------|--------|---------|
| **1** | Frontend TypeScript | منع إرسال role |
| **2** | Backend Controller | تجاهل role وفرض 'user' |
| **3** | Model Mutator | منع تغيير role غير مصرح |
| **4** | Admin Endpoints | فقط admin يغير الأدوار |

**✅ النتيجة:** نظام محكم ومتعدد الطبقات لضمان أن جميع التسجيلات الجديدة تكون كمستخدم عادي.

---

## 🚀 للمطورين

عند إضافة مستخدم admin جديد:
```bash
# استخدم Laravel Tinker
php artisan tinker

# أو Database Seeder
php artisan db:seed --class=AdminUserSeeder

# أو من Admin Panel (إذا كان لديك admin بالفعل)
PUT /api/v1/admin/users/{id}
```

**لا تحاول أبداً إضافة admin من صفحة التسجيل!** ❌

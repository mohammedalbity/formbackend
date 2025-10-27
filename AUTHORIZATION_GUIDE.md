# دليل نظام الصلاحيات والفلترة | Authorization & Filtering Guide

## نظرة عامة | Overview

تم تطبيق نظام صلاحيات متقدم يفصل بين صلاحيات **المستخدم العادي** و**الأدمن**.

---

## 🔐 الأدوار | Roles

### 1. **Admin (مدير)**
- **الصلاحيات الكاملة:**
  - عرض جميع النماذج والإرسالات
  - تعديل وحذف أي نموذج أو إرسال
  - إدارة المستخدمين
  - عرض الإحصائيات الشاملة
  - إدارة المكونات

### 2. **User (مستخدم)**
- **الصلاحيات المحدودة:**
  - عرض وتعديل نماذجه الخاصة فقط
  - عرض إرساله الخاصة
  - عرض الإرسالات الواردة لنماذجه
  - لا يمكنه رؤية بيانات المستخدمين الآخرين

---

## 📊 نظام الفلترة | Filtering System

### Forms (النماذج)

#### **GET /api/v1/forms**
```php
// المستخدم العادي:
- يرى نماذجه فقط (user_id = Auth::id())

// الأدمن:
- يرى جميع النماذج
```

**مثال:**
```bash
# مستخدم عادي
curl -H "Authorization: Bearer {token}" \
  http://localhost:8000/api/v1/forms

# الرد: فقط النماذج التي user_id = المستخدم الحالي
```

#### **GET /api/v1/forms/{id}**
```php
// المستخدم العادي:
- يمكنه رؤية نموذجه
- أو النماذج العامة (is_public = true)

// الأدمن:
- يرى أي نموذج
```

---

### Submissions (الإرسالات)

#### **GET /api/v1/submissions**
```php
// المستخدم العادي:
- إرساله الخاصة (user_id = Auth::id())
- أو الإرسالات الواردة لنماذجه (form.user_id = Auth::id())

// الأدمن:
- جميع الإرسالات
```

**منطق الفلترة:**
```php
if (!$user->isAdmin()) {
    $query->where(function ($q) use ($user) {
        // إرسالات نماذج المستخدم
        $q->whereHas('form', function ($formQuery) use ($user) {
            $formQuery->where('user_id', $user->id);
        })
        // أو إرسالات المستخدم نفسه
        ->orWhere('user_id', $user->id);
    });
}
```

#### **GET /api/v1/forms/{formId}/submissions**
```php
// المستخدم العادي:
- يجب أن يكون مالك النموذج
- إن لم يكن المالك، يرى إرساله فقط

// الأدمن:
- جميع إرسالات النموذج
```

---

## 🛡️ Policies (السياسات)

### FormPolicy
تحكم في صلاحيات النماذج:

| الوظيفة | Admin | User (Owner) | User (Other) |
|---------|-------|--------------|--------------|
| view | ✅ | ✅ | ✅ (public only) |
| create | ✅ | ✅ | ✅ |
| update | ✅ | ✅ | ❌ |
| delete | ✅ | ✅ | ❌ |
| viewSubmissions | ✅ | ✅ | ❌ |

### FormSubmissionPolicy
تحكم في صلاحيات الإرسالات:

| الوظيفة | Admin | Form Owner | Submitter |
|---------|-------|------------|-----------|
| view | ✅ | ✅ | ✅ (own only) |
| create | ✅ | ✅ | ✅ |
| update | ✅ | ✅ | ❌ |
| delete | ✅ | ✅ | ❌ |
| review | ✅ | ✅ | ❌ |
| approve | ✅ | ✅ | ❌ |
| reject | ✅ | ✅ | ❌ |

---

## 📈 Admin Endpoints

### 1. **إحصائيات شاملة**
```http
GET /api/v1/admin/statistics
Authorization: Bearer {admin_token}

Response:
{
  "success": true,
  "data": {
    "total_users": 150,
    "total_forms": 45,
    "total_submissions": 320,
    "forms_by_status": [...],
    "submissions_by_status": [...],
    "recent_forms": [...],
    "recent_submissions": [...],
    "popular_forms": [...]
  }
}
```

### 2. **إدارة المستخدمين**
```http
GET /api/v1/admin/users
Authorization: Bearer {admin_token}

Query Parameters:
- role: admin|user
- is_active: true|false
- search: string

Response:
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "محمد أحمد",
        "email": "user@example.com",
        "role": "user",
        "forms_count": 5,
        "submissions_count": 12
      }
    ]
  }
}
```

### 3. **جميع النماذج (Admin)**
```http
GET /api/v1/admin/forms
Authorization: Bearer {admin_token}

Query Parameters:
- status: draft|published|archived
- user_id: number
- search: string
```

### 4. **جميع الإرسالات (Admin)**
```http
GET /api/v1/admin/submissions
Authorization: Bearer {admin_token}

Query Parameters:
- status: submitted|reviewed|approved|rejected
- form_id: number
- user_id: number
- from_date: YYYY-MM-DD
- to_date: YYYY-MM-DD
```

### 5. **تعديل المستخدم**
```http
PUT /api/v1/admin/users/{id}
Authorization: Bearer {admin_token}

Body:
{
  "role": "admin",
  "is_active": true
}
```

---

## 🔧 تطبيق الصلاحيات | Implementation

### في Controllers:

```php
// FormController.php
public function index(Request $request): JsonResponse
{
    $query = Form::with('user');

    // Filter by user if not admin
    if (!Auth::user()->isAdmin()) {
        $query->where('user_id', Auth::id());
    }

    $forms = $query->latest()->paginate(15);
    
    return response()->json([
        'success' => true,
        'data' => $forms,
    ]);
}
```

### في Models:

```php
// User.php
public function isAdmin(): bool
{
    return $this->role === 'admin';
}

public function isActive(): bool
{
    return $this->is_active;
}
```

---

## 🧪 أمثلة الاختبار | Testing Examples

### 1. **مستخدم عادي يحاول الوصول لنماذج الآخرين:**
```bash
curl -H "Authorization: Bearer {user_token}" \
  http://localhost:8000/api/v1/forms

# النتيجة: فقط نماذجه
```

### 2. **أدمن يحصل على كل البيانات:**
```bash
curl -H "Authorization: Bearer {admin_token}" \
  http://localhost:8000/api/v1/forms

# النتيجة: جميع النماذج لكل المستخدمين
```

### 3. **مستخدم يحاول حذف نموذج ليس له:**
```bash
curl -X DELETE \
  -H "Authorization: Bearer {user_token}" \
  http://localhost:8000/api/v1/forms/{other_user_form_id}

# النتيجة: 403 Unauthorized
```

---

## ⚠️ ملاحظات أمنية | Security Notes

1. **Token Validation**: جميع الـ routes المحمية تتطلب token صالح
2. **Role Check**: يتم التحقق من الدور في كل request
3. **Owner Validation**: يتم التحقق من الملكية قبل أي عملية
4. **Public Forms**: النماذج العامة يمكن للجميع رؤيتها
5. **Admin Separation**: جميع endpoints الأدمن في prefix منفصل

---

## 📝 التحديثات المطبقة | Applied Changes

### الملفات الجديدة:
- ✅ `app/Http/Middleware/CheckFormOwnership.php`
- ✅ `app/Policies/FormPolicy.php`
- ✅ `app/Policies/FormSubmissionPolicy.php`
- ✅ `app/Http/Controllers/Api/AdminController.php`

### الملفات المعدلة:
- ✅ `app/Http/Controllers/Api/FormController.php`
- ✅ `app/Http/Controllers/Api/FormSubmissionController.php`
- ✅ `routes/api.php`

---

## 🚀 الاستخدام في Frontend

```javascript
// في auth store
const isAdmin = computed(() => user.value?.role === 'admin')

// في API calls
const getForms = async () => {
  // المستخدم العادي: سيحصل فقط على نماذجه
  // الأدمن: سيحصل على كل النماذج
  const response = await apiClient.get('/forms')
  return response.data
}

// Admin endpoints
const getStatistics = async () => {
  if (!isAdmin.value) return
  const response = await apiClient.get('/admin/statistics')
  return response.data
}
```

---

## ✅ الخلاصة | Summary

- ✅ فلترة تلقائية حسب صلاحيات المستخدم
- ✅ المستخدم العادي يرى بياناته فقط
- ✅ الأدمن له وصول كامل لكل البيانات
- ✅ حماية على مستوى Controller و Policy
- ✅ Endpoints خاصة للأدمن للإدارة والإحصائيات
- ✅ نظام آمن ومتماسك

**جاهز للاستخدام مباشرة!** 🎉

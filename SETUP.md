# إعداد قاعدة البيانات | Database Setup Guide

## خيارات قاعدة البيانات | Database Options

### 1. MySQL (الموصى به للإنتاج | Recommended for Production)

#### تثبيت MySQL | Install MySQL
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install mysql-server

# CentOS/RHEL
sudo yum install mysql-server

# macOS (using Homebrew)
brew install mysql
```

#### إعداد MySQL | Configure MySQL
```bash
# بدء خدمة MySQL | Start MySQL service
sudo systemctl start mysql
sudo systemctl enable mysql

# تشغيل إعداد الأمان | Run security setup
sudo mysql_secure_installation

# إنشاء قاعدة البيانات | Create database
sudo mysql -u root -p
```

```sql
CREATE DATABASE formio_backend CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'formio_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON formio_backend.* TO 'formio_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### تحديث ملف .env | Update .env file
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=formio_backend
DB_USERNAME=formio_user
DB_PASSWORD=your_secure_password
```

### 2. SQLite (للتطوير السريع | For Quick Development)

#### تثبيت SQLite | Install SQLite
```bash
# Ubuntu/Debian
sudo apt install sqlite3 php-sqlite3

# CentOS/RHEL
sudo yum install sqlite php-sqlite3

# macOS (usually pre-installed)
# If needed: brew install sqlite
```

#### إعداد SQLite | Configure SQLite
```bash
# إنشاء ملف قاعدة البيانات | Create database file
touch database/database.sqlite
```

#### تحديث ملف .env | Update .env file
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/your/project/database/database.sqlite
```

### 3. PostgreSQL (للمشاريع الكبيرة | For Large Projects)

#### تثبيت PostgreSQL | Install PostgreSQL
```bash
# Ubuntu/Debian
sudo apt install postgresql postgresql-contrib php-pgsql

# CentOS/RHEL
sudo yum install postgresql-server postgresql-contrib php-pgsql

# macOS
brew install postgresql
```

#### إعداد PostgreSQL | Configure PostgreSQL
```bash
# بدء الخدمة | Start service
sudo systemctl start postgresql
sudo systemctl enable postgresql

# إنشاء قاعدة البيانات | Create database
sudo -u postgres psql
```

```sql
CREATE DATABASE formio_backend;
CREATE USER formio_user WITH PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE formio_backend TO formio_user;
\q
```

#### تحديث ملف .env | Update .env file
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=formio_backend
DB_USERNAME=formio_user
DB_PASSWORD=your_secure_password
```

## تشغيل Migrations | Run Migrations

بعد إعداد قاعدة البيانات، قم بتشغيل الأوامر التالية:

```bash
# تشغيل Migrations | Run migrations
php artisan migrate

# إضافة البيانات التجريبية | Seed sample data
php artisan db:seed

# تشغيل الخادم | Start server
php artisan serve
```

## اختبار الاتصال | Test Connection

```bash
# اختبار اتصال قاعدة البيانات | Test database connection
php artisan tinker
```

```php
// في Tinker | In Tinker
DB::connection()->getPdo();
// يجب أن يعرض معلومات الاتصال | Should display connection info
```

## حل المشاكل الشائعة | Troubleshooting

### مشكلة SQLite Driver
```bash
# تحقق من تثبيت SQLite | Check SQLite installation
php -m | grep sqlite

# إذا لم يكن مثبتاً | If not installed
sudo apt install php-sqlite3
sudo systemctl restart apache2  # or nginx
```

### مشكلة MySQL Connection
```bash
# تحقق من حالة الخدمة | Check service status
sudo systemctl status mysql

# إعادة تشغيل الخدمة | Restart service
sudo systemctl restart mysql
```

### مشكلة الصلاحيات | Permission Issues
```bash
# تحقق من صلاحيات المجلد | Check folder permissions
ls -la database/
chmod 755 database/
chmod 644 database/database.sqlite  # for SQLite
```

## متطلبات PHP | PHP Requirements

تأكد من تثبيت الامتدادات المطلوبة:

```bash
# تحقق من امتدادات PHP | Check PHP extensions
php -m | grep -E "(pdo|mysql|sqlite|pgsql)"
```

الامتدادات المطلوبة:
- PDO
- pdo_mysql (للـ MySQL)
- pdo_sqlite (للـ SQLite)  
- pdo_pgsql (للـ PostgreSQL)

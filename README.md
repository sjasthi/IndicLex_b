# 📖 IndicLex — English–Telugu–Hindi Dictionary

A multilingual web dictionary built with PHP, MySQL, and Bootstrap 5.
Supports full CRUD, bulk import from Excel, REST API, autocomplete search,
admin dashboard with reports, and deployment on Bluehost.

---

## Tech Stack

| Layer       | Technology              |
|-------------|-------------------------|
| Backend     | PHP 8.2                 |
| Database    | MySQL 8.0               |
| Frontend    | Bootstrap 5.3.2         |
| Tables      | jQuery DataTables 1.13.7|
| Charts      | Chart.js 4.4.0          |
| Excel import| PhpSpreadsheet ^2.1     |
| Local dev   | XAMPP                   |
| Hosting     | Bluehost                |
| Version control | Git + GitHub        |

---

## Local Setup (XAMPP — Windows)

### Prerequisites
- [XAMPP](https://www.apachefriends.org) with PHP 8.2 and MySQL
- [Composer](https://getcomposer.org/download) (Windows installer)
- [Git](https://git-scm.com)

### 1. Clone the repository
```bash
cd C:\xampp\htdocs
git clone https://github.com/your-team/IndicLex_b.git
cd IndicLex_b
```

### 2. Install PHP dependencies
```bash
composer install --ignore-platform-reqs
```
> **Windows shortcut:** double-click `setup.bat`

### 3. Start XAMPP
Open XAMPP Control Panel → Start **Apache** and **MySQL**

### 4. Create the database
1. Open [localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **New** → Name: `indiclex_db` → Collation: `utf8mb4_unicode_ci` → **Create**
3. Click the **SQL** tab → paste contents of `schema.sql` → **Go**

### 5. Configure the database connection
Edit `includes/db.php`:
```php
DEFINE('DATABASE_HOST',     'localhost');
DEFINE('DATABASE_DATABASE', 'indiclex_db');
DEFINE('DATABASE_USER',     'root');
DEFINE('DATABASE_PASSWORD', '');   // blank for default XAMPP
```

### 6. Create the admin account
Upload `fix_admin.php` to the project root then visit:
```
http://localhost/IndicLex_b/fix_admin.php
```
**Delete `fix_admin.php` immediately after running it.**

Default credentials set by `fix_admin.php`:
- Username: `admin`
- Password: `admin123` ← change this

### 7. Import dictionary data
1. Log in at `index.php?page=login`
2. Go to **Import** → upload `dictionaries_two.xlsx`
3. ~8,300 entries will be imported

### 8. Verify everything works
```
http://localhost/IndicLex_b/test_db.php
```
**Delete `test_db.php` after verifying.**

---

## Bluehost Deployment

### 1. Create the database
1. Log in to Bluehost cPanel
2. Go to **MySQL Databases**
3. Create database (e.g. `user_indiclex`)
4. Create a user → assign **All Privileges**
5. Note the host, DB name, username, and password

### 2. Run the schema
1. Open **phpMyAdmin** from cPanel
2. Select your database → **SQL** tab
3. Paste `schema.sql` → **Go**

### 3. Upload files via FTP
Use FileZilla or cPanel File Manager. Upload everything **except**:
```
vendor/        ← install via Composer on server
uploads/       ← created automatically
test_db.php    ← never deploy
fix_admin.php  ← never deploy
```

### 4. Install Composer on Bluehost (SSH)
```bash
ssh username@yoursite.com
cd public_html/IndicLex_b
composer install --no-dev --optimize-autoloader --ignore-platform-reqs
```

### 5. Update db.php for production
```php
DEFINE('DATABASE_HOST',     'localhost');
DEFINE('DATABASE_DATABASE', 'user_indiclex');   // your Bluehost DB name
DEFINE('DATABASE_USER',     'user_dbuser');     // your Bluehost DB user
DEFINE('DATABASE_PASSWORD', 'YourPassword');
```

### 6. Set file permissions
```bash
chmod 755 uploads/
chmod 644 .htaccess
```

### 7. Verify routing
Visit `/api/search?q=water` — if it returns JSON, `.htaccess` routing is working.

### 8. Check PHP limits
The `.htaccess` already sets these — verify them on Bluehost:
```
upload_max_filesize = 20M
post_max_size       = 22M
max_execution_time  = 120
memory_limit        = 256M
display_errors      = Off
```
If imports fail, ask Bluehost support to increase these in `php.ini`.

---

## Project Structure

```
IndicLex_b/
├── index.php                  ← Main router
├── composer.json              ← PHP dependencies
├── schema.sql                 ← Database schema (run once)
├── .htaccess                  ← URL routing + PHP config
├── .gitignore
├── setup.bat                  ← Windows Composer installer
├── api/
│   └── search.php             ← GET /api/search REST endpoint
├── includes/                  ← Shared PHP (never access directly)
│   ├── db.php
│   ├── auth.php
│   ├── header.php / footer.php
│   ├── admin_header.php / admin_footer.php
│   ├── preferences_helper.php
│   ├── dictionary_search.php
│   ├── admin_stats_data.php
│   └── admin_stats_cards.php
├── pages/                     ← One file per page
├── assets/
│   ├── css/ (style.css, admin.css)
│   └── JS/  (theme.js, main.js, search-autocomplete.js, admin_crud.js)
├── uploads/                   ← Auto-created, not committed
└── vendor/                    ← Auto-generated, not committed
```

---

## REST API

**Endpoint:** `GET /api/search`

| Parameter | Required | Values | Default |
|-----------|----------|--------|---------|
| `q`       | ✅ Yes   | any string | — |
| `dict`    | No       | dictionary ID or `all` | `all` |
| `mode`    | No       | exact, prefix, suffix, substring | `substring` |
| `limit`   | No       | 1–100 | 50 |
| `offset`  | No       | 0+    | 0  |

**Example:**
```
GET /api/search?q=water&mode=substring&dict=all
```

**HTTP Status Codes:**
- `200` — results found
- `400` — bad request (missing/invalid parameter)
- `404` — no results or dictionary not found
- `405` — method not allowed
- `500` — server error

---

## Git Workflow

### Never commit these files
```
vendor/          # run composer install locally
uploads/         # user-uploaded files
test_db.php      # exposes database credentials
fix_admin.php    # modifies database directly
```

### Daily workflow
```bash
git pull origin main
# make changes
git add .
git commit -m "your message"
git push origin main
```

### After a teammate pushes
```bash
git pull origin main
# if composer.json changed:
composer install --ignore-platform-reqs
```

---

## Adding a New Admin Page

1. Create `pages/admin_yourpage.php`
2. Add `'admin_yourpage'` to `$admin_pages` in `index.php`
3. Add a link in `includes/admin_header.php`
4. **Do not** call `require_admin()` inside the page — `index.php` handles it

---

## Common Issues

| Problem | Fix |
|---------|-----|
| MySQL won't start | Check port 3306 isn't in use. Try port 3307 in XAMPP config. |
| `vendor/` missing | Run `composer install --ignore-platform-reqs` |
| Telugu not displaying | Ensure DB is `utf8mb4` and `$db->set_charset('utf8mb4')` is in `db.php` |
| Session keeps expiring | Check `session_start()` is only called once at the top of `index.php` |
| Import fails on Bluehost | Check `upload_max_filesize` in `.htaccess` is at least `20M` |
| Buttons redirect to home | Page is missing from `$admin_pages` or `$allowed` in `index.php` |

---

## Team

ICS 499 — DictionaryHub Project  
Built with PHP · MySQL · Bootstrap · Chart.js · PhpSpreadsheet
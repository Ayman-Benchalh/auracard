# AURA CARD — Managed cPanel Deployment Guide

## 1. Production Architecture Principles

AURA CARD is architected specifically to run efficiently and securely on **standard cPanel shared and business hosting environments** without requiring:
- Root access
- Docker or container orchestration
- Redis or Memcached
- Supervisor or systemd daemons
- Node.js runtime on production

---

## 2. Server Requirements & PHP Configuration

In cPanel **MultiPHP Manager** or **Select PHP Version**:
- **PHP Version:** PHP >= 8.3 (e.g. `ea-php83` or `alt-php83`).
- **Memory Limit:** `256M` or higher.
- **Max Execution Time:** `120s` or higher.
- **Upload Max Filesize / Post Max Size:** `16M`.
- **Required Extensions:**
  - `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd` or `imagick`, `intl`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`.

---

## 3. Directory Layout & Isolation (Strict Security)

**CRITICAL:** Never upload the entire Laravel repository directly into `public_html`. Doing so risks exposing `.env`, SQLite files, storage logs, and source code.

```
/home/username/
├── aura_app/                               <-- Non-public Laravel core (outside web root)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/                            <-- Writable logs, private proofs, cache
│   │   ├── app/
│   │   │   └── private/payment_proofs/     <-- Private receipts (never exposed publicly)
│   │   ├── framework/
│   │   └── logs/
│   ├── vendor/
│   ├── .env                                <-- Environment credentials
│   └── artisan
│
└── public_html/                            <-- Public document root
    ├── build/                              <-- Compiled React/Vite assets from npm run build
    ├── storage/                            <-- Symlink to aura_app/storage/app/public
    ├── .htaccess                           <-- Security & routing rules
    ├── index.php                           <-- Entry point
    ├── robots.txt
    └── favicon.ico
```

### 3.1 Public `index.php` Entrypoint
In `/home/username/public_html/index.php`:
```php
<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Maintenance mode
if (file_exists($maintenance = __DIR__.'/../aura_app/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader & Application Bootstrap
require __DIR__.'/../aura_app/vendor/autoload.php';
$app = require_once __DIR__.'/../aura_app/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

### 3.2 Storage Symlink
Run via cPanel Terminal or SSH:
```bash
ln -s /home/username/aura_app/storage/app/public /home/username/public_html/storage
```
*If `symlink` is disabled by your host, create a temporary PHP script in `public_html` calling `symlink('/home/username/aura_app/storage/app/public', 'storage');` or contact host support.*

---

## 4. Production `.env` Settings

Configure `/home/username/aura_app/.env`:

```ini
APP_NAME="AURA CARD"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.ma

# Database Connection (MySQL / MariaDB via cPanel MySQL Databases)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=username_auracard
DB_USERNAME=username_auracard_user
DB_PASSWORD=SecurePasswordHere!

# Database Drivers (Zero Redis Requirement)
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Private & Public Storage
FILESYSTEM_DISK=local

# Mail Settings (cPanel Webmail or Brevo/SendGrid SMTP)
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.ma
MAIL_PORT=465
MAIL_USERNAME=notifications@yourdomain.ma
MAIL_PASSWORD=MailPasswordHere!
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="notifications@yourdomain.ma"
MAIL_FROM_NAME="AURA CARD"
```

---

## 5. Web Server Security (`public_html/.htaccess`)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Enforce HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

    # Block access to hidden/sensitive files
    RewriteRule ^(\.env|\.git|\.editorconfig|composer\.(json|lock)|package\.(json|lock)) - [F,L]

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
```

---

## 6. cPanel Cron Jobs Configuration

Under cPanel **Cron Jobs**, register the two scheduled commands:

### Cron 1: Laravel Task Scheduler (Every 5 minutes)
```bash
*/5 * * * * /usr/local/bin/ea-php83 /home/username/aura_app/artisan schedule:run >/dev/null 2>&1
```

### Cron 2: Queue Worker (Every 5 minutes, self-terminating)
```bash
*/5 * * * * /usr/local/bin/ea-php83 /home/username/aura_app/artisan queue:work --stop-when-empty --max-time=280 --memory=128 --rest=1 >/dev/null 2>&1
```
*Note: `--stop-when-empty` ensures no hanging processes on cPanel while reliably executing pending email notifications, lead emails, and analytics aggregations.*

---

## 7. Deployment Routine (Release Checklist)

1. **Build Assets Locally / CI:**
   ```bash
   npm ci
   npm run build
   ```
2. **Upload Repository:** Upload code to `/home/username/aura_app/` (exclude `node_modules`).
3. **Upload Static Build:** Upload compiled `public/build/` to `/home/username/public_html/build/`.
4. **Run Database Migrations & Caching:**
   ```bash
   cd /home/username/aura_app
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan event:cache
   ```

# Blues Marketplace — Setup & Developer Guide

Blues Marketplace is a Laravel 12 digital accounts marketplace with wallet, Paystack checkout, LogsPlug virtual numbers, admin panel, user dashboards, support tickets, notifications, and announcements.

---

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (PHP 8.2) |
| Database (primary) | PostgreSQL (Replit managed) |
| Database (admin GUI) | Adminer 4.8.1 |
| Database (optional) | MySQL 8.0 |
| CSS | Tailwind CSS (CDN) |
| Payments | Paystack |
| Virtual Numbers | LogsPlug API |

---

## Quick Start (Replit)

The app starts automatically. Click **Run** and it will be available on port **5000**.

Admin panel: `/adminlogin`  
Default admin: `admin@blues.com` / `admin123`

---

## Environment Variables (`.env`)

Set these in Replit Secrets or `.env`:

```env
# App
APP_KEY=           # Generate with: php artisan key:generate
APP_URL=           # Your Replit URL

# Database (PostgreSQL — default)
DB_CONNECTION=pgsql
DB_HOST=helium
DB_PORT=5432
DB_DATABASE=heliumdb
DB_USERNAME=postgres
DB_PASSWORD=password

# Paystack
PAYSTACK_PUBLIC_KEY=pk_live_...
PAYSTACK_SECRET_KEY=sk_live_...
PAYSTACK_WEBHOOK_SECRET=...

# LogsPlug Virtual Numbers
LOGSPLUG_API_KEY=...
LOGSPLUG_API_URL=https://logsplug.com/api

# Mail (configure for real email delivery)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=yourpassword
MAIL_FROM_ADDRESS=noreply@bluesmarketplace.com
MAIL_FROM_NAME="Blues Marketplace"
```

Configure all keys in the Admin → **Settings** panel.

---

## Adminer — Database Admin GUI

Adminer is a browser-based database management interface included in `backend/adminer/`.

### Start Adminer

In the Replit terminal:

```bash
bash blues-laravel/backend/adminer/start.sh
```

Or use the **"Adminer DB Admin"** workflow in Replit — it runs on **port 8080**.

> **Important:** Adminer is a PHP application. It must be served via PHP's built-in server (`php -S`), not Python's `http.server` (which only serves static files). The `start.sh` script handles this correctly.

### Connect to PostgreSQL (default)

| Field | Value |
|---|---|
| System | PostgreSQL |
| Server | `helium:5432` |
| Username | `postgres` |
| Password | `password` |
| Database | `heliumdb` |

The login form is pre-filled from your environment variables automatically.

### Connect to MySQL (if migrated)

| Field | Value |
|---|---|
| System | MySQL |
| Server | `localhost:3306` |
| Username | `blues` |
| Password | `blues_secret` |
| Database | `blues_marketplace` |

---

## MySQL Setup (Optional — migrate from PostgreSQL)

MySQL 8.0 is installed in this environment. Follow these steps in the Replit terminal to migrate.

### Step 1 — Initialize MySQL (first time only)

```bash
mkdir -p /tmp/mysql-data
mysqld --initialize-insecure --datadir=/tmp/mysql-data --user=$(whoami)
```

### Step 2 — Start MySQL server

```bash
mysqld \
  --datadir=/tmp/mysql-data \
  --port=3306 \
  --socket=/tmp/mysql.sock \
  --pid-file=/tmp/mysql.pid \
  --daemonize \
  --user=$(whoami)
```

### Step 3 — Create database and user

```bash
mysql --socket=/tmp/mysql.sock -u root --password='' -e "
  CREATE DATABASE IF NOT EXISTS blues_marketplace;
  CREATE USER IF NOT EXISTS 'blues'@'localhost' IDENTIFIED BY 'blues_secret';
  GRANT ALL PRIVILEGES ON blues_marketplace.* TO 'blues'@'localhost';
  FLUSH PRIVILEGES;
"
```

### Step 4 — Update Laravel `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blues_marketplace
DB_USERNAME=blues
DB_PASSWORD=blues_secret
DB_SOCKET=/tmp/mysql.sock
```

### Step 5 — Run Laravel migrations on MySQL

```bash
cd blues-laravel
php artisan migrate:fresh
```

### Step 6 — Migrate data from PostgreSQL to MySQL

Run the migration helper script:

```bash
bash blues-laravel/backend/migrate-pg-to-mysql.sh
```

This script exports each PostgreSQL table to CSV and imports it into MySQL.

### Step 7 — Restart the app

```bash
# Stop MySQL gracefully when done working
mysqladmin --socket=/tmp/mysql.sock -u root shutdown
```

> **Note:** MySQL runs in `/tmp/mysql-data` which is ephemeral on Replit free tier. Data will persist within the same session. For permanent storage, use Replit's PostgreSQL integration or a managed MySQL service.

---

## Running Migrations

```bash
cd blues-laravel
php artisan migrate          # Run new migrations
php artisan migrate:status   # See migration status
php artisan migrate:fresh    # Drop all tables and re-run (destructive!)
```

---

## Admin Panel Features

| Feature | URL |
|---|---|
| Dashboard | `/admin` |
| Users | `/admin/users` |
| Listings | `/admin/listings` |
| Categories | `/admin/categories` |
| Moderators | `/admin/moderators` |
| Transactions | `/admin/transactions` |
| Support Tickets | `/admin/tickets` |
| Virtual Numbers | `/admin/virtual-numbers` |
| **Announcements** | `/admin/announcements` |
| Audit Log | `/admin/audit` |
| Settings | `/admin/settings` |

---

## Notifications System

In-app notifications are automatically triggered for:

- **Wallet funded** — when Paystack payment is verified
- **Purchase completed** — when a listing is bought
- **Support ticket reply** — when admin replies to a ticket
- **Announcements** — when admin sends a broadcast

Users see unread count as a badge on the sidebar **Notifications** link. Visiting the page marks all as read.

---

## Admin Announcements

Go to **Admin → Announcements** to send a broadcast to all users:

1. Enter a title and message
2. Choose a type: Info / Success / Warning / Alert
3. Optionally tick **"Also send via email"** to also deliver via email
4. Click **Send to All Users**

All past announcements are logged in the history table below the form.

For email delivery, configure SMTP in Admin → **Settings** (or `.env` `MAIL_*` variables).

---

## WhatsApp Support Button

Set your WhatsApp number in Admin → **Settings → WhatsApp Support**.

Format: country code + number, no `+` or spaces.  
Example: `2348012345678` for a Nigerian number.

The floating green button appears on all user-facing pages when a number is configured.

---

## Paystack Integration

1. Get keys from [dashboard.paystack.com](https://dashboard.paystack.com)
2. Enter in Admin → **Settings → Paystack**
3. Set webhook URL in Paystack dashboard: `https://your-domain.com/paystack/webhook`

---

## LogsPlug Virtual Numbers

1. Get API key from LogsPlug
2. Enter in Admin → **Settings → Virtual Numbers**
3. Toggle "Enable Virtual Numbers" to show/hide the feature for users

---

## File Structure

```
blues-laravel/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/          # Admin panel controllers
│   │   └── User/           # User dashboard controllers
│   └── Models/             # Eloquent models
├── backend/
│   └── adminer/
│       ├── adminer.php     # Adminer 4.8.1 (single-file DB admin)
│       ├── index.php       # Entry point with auto-fill from env
│       └── start.sh        # Launch script (port 8080)
├── database/migrations/    # All Laravel migrations
├── resources/views/
│   ├── admin/              # Admin panel views
│   ├── dashboard/          # User dashboard views
│   └── layouts/            # Shared layouts
└── routes/web.php          # All routes
```

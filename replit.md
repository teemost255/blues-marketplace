# Blues Marketplace

A digital goods and services marketplace built with Laravel 12 and PHP 8.2. Users can buy digital accounts, rent virtual numbers, and manage a wallet funded via Paystack.

## Dev → cPanel Deployment Workflow

**Replit is the development environment. cPanel is production.**

### Making and pushing changes

1. Edit code here on Replit — the app auto-reloads in the preview pane
2. When ready to deploy, push your changes to GitHub via the Git panel (or shell):
   ```bash
   git add -A
   git commit -m "your message"
   git push origin main
   ```

### Deploying to cPanel

SSH into your cPanel server and run:
```bash
cd /path/to/your/app/blues-laravel
git pull origin main
bash deploy.sh
```

`deploy.sh` handles: composer install, npm build, migrations, and cache refresh automatically.

### Environment variables

- **Replit development** preserves the imported SQLite database. On first run, `start.sh` copies `blues-laravel/heliumdb` to the ignored `blues-laravel/database/database.sqlite`; the original is not modified. PostgreSQL is available but switching or migrating data requires an explicit decision.
- **cPanel** needs its own `.env` file with its MySQL/PostgreSQL credentials, `APP_KEY`, and API keys.

### Admin panel

- URL: `/adminlogin`
- Default credentials: `admin@blues.com` / `admin123`
- API keys (Paystack, LogsPlug, etc.) are managed inside the admin panel under Settings — no need to set them in `.env`
- Virtual number Server 3 uses PVADeals. Save its API key in Admin → Virtual Number Settings, test the connection, enable Server 3, then register the displayed webhook URL. This integration exposes PVADeals 20-minute short-term rentals; long-term plans and number reuse are not included.

## Running the imported project on Replit

- Click **Run** to start the **Start application** workflow (`bash start.sh`), serving Laravel on port **5000**.
- The first run creates an ignored `blues-laravel/.env` from `.env.example`, installs locked dependencies if needed, builds frontend assets, generates an app key only if absent, and applies outstanding migrations.
- Subsequent runs preserve the app key and database. The Laravel scheduler runs alongside the web server.
- Manual checks: `cd blues-laravel && php artisan test`; frontend build: `npm run build`.
- The imported database contains categories but no listings or admin accounts. The default admin credentials above are historical documentation, not a provisioned account in this import.
- Payment and supplier services require real credentials in Settings before use. Email defaults to the log driver and does not send messages; configure SMTP before relying on verification or password-reset email.
- This setup is for development only. The existing cPanel production workflow is unchanged.

## Project Structure

```
blues-laravel/          # Laravel app
├── app/
│   ├── Http/Controllers/Admin/   # Admin panel controllers
│   ├── Http/Controllers/User/    # User dashboard controllers
│   ├── Models/                   # Eloquent models
│   └── Services/                 # External API integrations
├── database/migrations/          # All DB migrations
├── resources/views/              # Blade templates
│   ├── admin/                    # Admin views
│   ├── dashboard/                # User dashboard
│   └── marketplace/              # Public marketplace
├── routes/web.php                # All routes
└── deploy.sh                     # cPanel deployment script
start.sh                          # Replit startup script
```

## User Preferences

- Use Replit as development environment, deploy to cPanel via GitHub
- Production database: MySQL on cPanel; the imported SQLite data is retained for this Replit setup.

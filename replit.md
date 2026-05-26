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

- **Replit** uses the PostgreSQL database built into this environment. The `.env` file is auto-configured on startup.
- **cPanel** needs its own `.env` file with its MySQL/PostgreSQL credentials, `APP_KEY`, and API keys.

### Admin panel

- URL: `/adminlogin`
- Default credentials: `admin@blues.com` / `admin123`
- API keys (Paystack, LogsPlug, etc.) are managed inside the admin panel under Settings — no need to set them in `.env`

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
- Database: PostgreSQL on Replit, MySQL on cPanel

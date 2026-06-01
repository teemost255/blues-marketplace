#!/bin/bash
set -e

cd blues-laravel

# Inject the correct APP_URL from Replit domain
if [ -n "$REPLIT_DEV_DOMAIN" ]; then
  grep -q "^APP_URL=" .env && sed -i "s|^APP_URL=.*|APP_URL=https://${REPLIT_DEV_DOMAIN}|" .env || echo "APP_URL=https://${REPLIT_DEV_DOMAIN}" >> .env
fi

# Inject PostgreSQL connection from Replit environment into .env
if [ -n "$PGHOST" ]; then
  sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|" .env
  grep -q "^DB_HOST=" .env && sed -i "s|^DB_HOST=.*|DB_HOST=${PGHOST}|" .env || echo "DB_HOST=${PGHOST}" >> .env
  grep -q "^DB_PORT=" .env && sed -i "s|^DB_PORT=.*|DB_PORT=${PGPORT:-5432}|" .env || echo "DB_PORT=${PGPORT:-5432}" >> .env
  grep -q "^DB_DATABASE=" .env && sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${PGDATABASE}|" .env || echo "DB_DATABASE=${PGDATABASE}" >> .env
  grep -q "^DB_USERNAME=" .env && sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${PGUSER}|" .env || echo "DB_USERNAME=${PGUSER}" >> .env
  grep -q "^DB_PASSWORD=" .env && sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${PGPASSWORD}|" .env || echo "DB_PASSWORD=${PGPASSWORD}" >> .env
  sed -i "/^DB_URL=/d" .env
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
  php artisan key:generate --ansi
fi

# Ensure file-based session, cache and sync queue (no Redis needed on Replit)
grep -q "^SESSION_DRIVER=" .env && sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=file|" .env || echo "SESSION_DRIVER=file" >> .env
grep -q "^CACHE_STORE=" .env && sed -i "s|^CACHE_STORE=.*|CACHE_STORE=file|" .env || echo "CACHE_STORE=file" >> .env
grep -q "^QUEUE_CONNECTION=" .env && sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=sync|" .env || echo "QUEUE_CONNECTION=sync" >> .env
grep -q "^APP_ENV=" .env && sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env || echo "APP_ENV=production" >> .env

# Set up storage directories
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs
mkdir -p public/uploads/listings
mkdir -p public/uploads/categories
chmod -R 755 storage bootstrap/cache public/uploads

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Start the Laravel task scheduler in the background
php artisan schedule:work &

# Start the server
php artisan serve --host=0.0.0.0 --port=5000

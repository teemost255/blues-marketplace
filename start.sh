#!/bin/bash
set -e

cd blues-laravel

# Inject PostgreSQL connection from Replit environment into .env
if [ -n "$DATABASE_URL" ]; then
  # Parse DATABASE_URL: postgresql://user:password@host/dbname?params
  DB_URL_STRIPPED="${DATABASE_URL#postgresql://}"
  DB_URL_STRIPPED="${DB_URL_STRIPPED#postgres://}"
  DB_USER=$(echo "$DB_URL_STRIPPED" | cut -d: -f1)
  DB_PASS=$(echo "$DB_URL_STRIPPED" | cut -d: -f2 | cut -d@ -f1)
  DB_HOST=$(echo "$DB_URL_STRIPPED" | cut -d@ -f2 | cut -d/ -f1 | cut -d: -f1)
  DB_PORT_RAW=$(echo "$DB_URL_STRIPPED" | cut -d@ -f2 | cut -d/ -f1 | cut -s -d: -f2)
  DB_PORT=${DB_PORT_RAW:-5432}
  DB_NAME=$(echo "$DB_URL_STRIPPED" | cut -d/ -f2 | cut -d? -f1)

  sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|" .env
  grep -q "^DB_HOST=" .env && sed -i "s|^DB_HOST=.*|DB_HOST=${DB_HOST}|" .env || echo "DB_HOST=${DB_HOST}" >> .env
  grep -q "^DB_PORT=" .env && sed -i "s|^DB_PORT=.*|DB_PORT=${DB_PORT}|" .env || echo "DB_PORT=${DB_PORT}" >> .env
  grep -q "^DB_DATABASE=" .env && sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|" .env || echo "DB_DATABASE=${DB_NAME}" >> .env
  grep -q "^DB_USERNAME=" .env && sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${DB_USER}|" .env || echo "DB_USERNAME=${DB_USER}" >> .env
  grep -q "^DB_PASSWORD=" .env && sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|" .env || echo "DB_PASSWORD=${DB_PASS}" >> .env
  # Remove DB_URL line if present to avoid conflict
  sed -i "/^DB_URL=/d" .env
fi

# Inject the correct APP_URL from Replit domain
if [ -n "$REPLIT_DEV_DOMAIN" ]; then
  grep -q "^APP_URL=" .env && sed -i "s|^APP_URL=.*|APP_URL=https://${REPLIT_DEV_DOMAIN}|" .env || echo "APP_URL=https://${REPLIT_DEV_DOMAIN}" >> .env
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
  php artisan key:generate --ansi
fi

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

# Start the server
php artisan serve --host=0.0.0.0 --port=5000

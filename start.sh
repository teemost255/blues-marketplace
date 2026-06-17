#!/bin/bash
set -e

cd blues-laravel

# Inject the correct APP_URL from Replit domain
if [ -n "$REPLIT_DEV_DOMAIN" ]; then
  grep -q "^APP_URL=" .env && sed -i "s|^APP_URL=.*|APP_URL=https://${REPLIT_DEV_DOMAIN}|" .env || echo "APP_URL=https://${REPLIT_DEV_DOMAIN}" >> .env
fi

# Inject PostgreSQL connection from Replit environment into .env
if [ -n "$PGHOST" ]; then
  grep -q "^DB_HOST=" .env && sed -i "s|^DB_HOST=.*|DB_HOST=${PGHOST}|" .env || echo "DB_HOST=${PGHOST}" >> .env
  grep -q "^DB_PORT=" .env && sed -i "s|^DB_PORT=.*|DB_PORT=${PGPORT:-5432}|" .env || echo "DB_PORT=${PGPORT:-5432}" >> .env
  grep -q "^DB_DATABASE=" .env && sed -i "s|^DB_DATABASE=.*|DB_DATABASE=${PGDATABASE}|" .env || echo "DB_DATABASE=${PGDATABASE}" >> .env
  grep -q "^DB_USERNAME=" .env && sed -i "s|^DB_USERNAME=.*|DB_USERNAME=${PGUSER}|" .env || echo "DB_USERNAME=${PGUSER}" >> .env
  grep -q "^DB_PASSWORD=" .env && sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${PGPASSWORD}|" .env || echo "DB_PASSWORD=${PGPASSWORD}" >> .env
  sed -i "/^DB_URL=/d" .env
fi

# Install PHP dependencies if vendor is missing
if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

# Set up storage directories
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p public/uploads/listings
mkdir -p public/uploads/categories
chmod -R 755 storage bootstrap/cache public/uploads

# Clear bootstrap cache so fresh config is loaded
php artisan config:clear 2>/dev/null || true

# Run migrations
php artisan migrate --force

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Start the Laravel task scheduler in the background
php artisan schedule:work &

# Start the server
php artisan serve --host=0.0.0.0 --port=5000

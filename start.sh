#!/bin/bash
set -euo pipefail

cd "$(dirname "$0")/blues-laravel"
umask 077

# Bootstrap local configuration without replacing an existing environment.
if [ ! -f .env ]; then
  cp .env.example .env
fi

# Use the preview URL without writing runtime-managed values into .env.
if [ -n "${REPLIT_DEV_DOMAIN:-}" ]; then
  export APP_URL="https://${REPLIT_DEV_DOMAIN}"
fi

# PostgreSQL is opt-in; preserve the imported SQLite database by default.
if [ "${DB_CONNECTION:-}" = "pgsql" ] && [ -n "${DATABASE_URL:-}" ]; then
  export DB_URL="$DATABASE_URL"
fi
if [ ! -f database/database.sqlite ] && grep -q '^DB_CONNECTION=sqlite$' .env && [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
  if [ -f heliumdb ]; then
    cp heliumdb database/database.sqlite
  else
    touch database/database.sqlite
  fi
fi

# Install PHP dependencies if vendor is missing
if [ ! -f vendor/autoload.php ]; then
  composer install --no-interaction --prefer-dist
fi

# Build frontend assets if not present
if [ ! -f public/build/manifest.json ]; then
  npm ci
  npm run build
fi

# Set up storage directories
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache/data
mkdir -p storage/logs
mkdir -p public/uploads/listings
mkdir -p public/uploads/categories
chmod -R 755 storage bootstrap/cache public/uploads

# Clear stale config before checking the key. Generate it only once.
php artisan config:clear
if php -r 'require "vendor/autoload.php"; Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad(); exit(empty($_ENV["APP_KEY"]) && !getenv("APP_KEY") ? 0 : 1);'; then
  php artisan key:generate --force
fi

# Run migrations
php artisan migrate --force

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Start the Laravel task scheduler in the background
php artisan schedule:work &
scheduler_pid=$!
trap 'kill "$scheduler_pid" 2>/dev/null || true' EXIT

# Start the server
php artisan serve --host=0.0.0.0 --port=5000

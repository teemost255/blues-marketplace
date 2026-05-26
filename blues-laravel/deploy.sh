#!/bin/bash
# ─────────────────────────────────────────────────────────────
# Blues Marketplace — cPanel Post-Deployment Script
# Run this once after every git pull on your cPanel server:
#   bash deploy.sh
# ─────────────────────────────────────────────────────────────
set -e

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Installing Node dependencies & building assets..."
npm install
npm run build

echo "==> Generating app key (skipped if already set)..."
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
  php artisan key:generate --ansi
fi

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Creating uploads directories..."
mkdir -p public/uploads/listings
mkdir -p public/uploads/categories
chmod -R 755 public/uploads

echo "==> Clearing all caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "==> Caching for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✓ Deployment complete!"

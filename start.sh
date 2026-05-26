#!/bin/bash
set -e

cd blues-laravel

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
  php artisan key:generate --ansi
fi

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Start the server
php artisan serve --host=0.0.0.0 --port=5000

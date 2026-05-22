# Backend (Laravel)

This folder is reserved for a separate Laravel backend repository.

The Laravel application lives in `backend/app`.

Recommended approaches to install and run Laravel here:

1) Create a fresh Laravel project using Composer locally:

```bash
# from repository root
cd backend/app
composer create-project laravel/laravel .
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --host=0.0.0.0 --port=8000
```

2) Use Docker Composer (no local PHP/Composer required):

```bash
cd backend
# create project using composer container
docker run --rm -v "$PWD":/app -w /app composer:2 create-project laravel/laravel .
# start services
docker-compose up -d
# run migrations
docker-compose run --rm app php artisan migrate
```

3) Use Adminer for database inspection:

```bash
docker-compose up -d adminer
```

Open http://localhost:8080 and connect with:
- System: MySQL
- Server: db
- Username: laravel
- Password: secret
- Database: laravel

4) Use Laravel Sail (Docker wrapper):

```bash
cd backend
composer require laravel/sail --dev
php artisan sail:install
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

Notes:
- After scaffolding, move your API endpoints (existing `src/server.ts` routes) into `routes/api.php` and implement controllers.
- Keep database credentials in `.env` and do not commit secrets.

If you want, I can: create the Laravel project here using Docker composer, or prepare a git history-friendly split and initialize a new repository for `backend`.

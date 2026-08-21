# Softpro POS

A point-of-sale system built on Laravel 12 — multi-store inventory, sales, purchasing, accounting, and reporting, with an offline-capable POS screen installable as a desktop PWA.

## Documentation

- **[Setup Guide](docs/SETUP.md)** — installing dependencies, configuring the database, and how the app runs as persistent Windows services (Apache + MySQL) rather than a temporary dev server.
- **[User Guide](docs/USER_GUIDE.md)** — roles, shifts, running the POS screen, receipt printer/scanner hardware notes, store branding.
- **[Architecture](docs/ARCHITECTURE.md)** — the multi-store data model, stock movement and accounting services, the tax engine, and frontend conventions, for anyone extending the codebase.

## Quick start

```powershell
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

See [docs/SETUP.md](docs/SETUP.md) for full setup instructions, demo login credentials, and how the app is actually served day-to-day (not `php artisan serve`).

## Stack

PHP 8.2+, Laravel 12, MySQL, Tailwind CSS v3, Alpine.js, `spatie/laravel-permission`, `spatie/laravel-activitylog`. Server-rendered — no separate frontend framework or API layer.

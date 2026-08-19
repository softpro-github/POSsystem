# Setup Guide — Softpro POS

This is a Laravel 12 point-of-sale application (PHP 8.2+, MySQL, Tailwind CSS, Alpine.js), built to run on XAMPP. It's a traditional server-rendered web app — no separate API/frontend split — installed as a PWA so it opens like a desktop app.

## Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.2+)
- [Node.js](https://nodejs.org/) 18+ (for building CSS/JS assets)
- Composer

## First-time install

From the project root (`c:\xampp\htdocs\GadgetStorePOSsystem`):

```powershell
composer install
npm install

copy .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials (defaults match a fresh XAMPP MySQL install — `DB_USERNAME=root`, `DB_PASSWORD=` empty). Create the database first (via phpMyAdmin or `mysql -u root -e "CREATE DATABASE gadgetstore_pos"`).

```powershell
php artisan migrate --seed
npm run build
```

`--seed` runs all seeders: roles/permissions, demo users, a demo catalog, adjustment/return/cash-mismatch reason codes, and the chart of accounts. **Only run `migrate:fresh --seed` (not plain `migrate --seed`) if you want to wipe existing data** — `migrate:fresh` drops every table first.

## Demo accounts

Seeded by `DemoUserSeeder`, all with password `password`:

| Email | Role | Can access |
|---|---|---|
| `admin@gadgetstore.test` | Admin | Everything |
| `manager@gadgetstore.test` | Manager | Everything except Users, Roles, System Health, Tax Settings |
| `cashier@gadgetstore.test` | Cashier | Dashboard, POS, Sales History, Customers, Shift History |

See `database/seeders/RoleSeeder.php` for the exact permission list per role.

## Running it

### Local development (hot-reload, for making code changes)

```powershell
composer run dev
```

This runs the Laravel dev server, queue listener, log viewer, and Vite's hot-reload dev server together. **This is a foreground developer convenience command — it dies the moment the terminal closes.** Don't use it as the actual way the app runs day-to-day.

### How the app actually runs (persistent, survives reboots)

The delivered setup does **not** use `php artisan serve` at all — that's a temporary dev-only tool with no auto-restart. Instead, Apache and MySQL run as Windows Services, so the app is available the moment the PC boots, with no terminal window and nothing to remember to start.

This was set up once as follows (already done on this machine — only needed again on a *new* machine):

1. **`C:\xampp\apache\conf\httpd.conf`** — added `Listen 8000` alongside the default `Listen 80`.
2. **`C:\xampp\apache\conf\extra\httpd-vhosts.conf`** — added a VirtualHost on port 8000 pointing at this project's `public/` folder:
   ```apache
   <VirtualHost *:8000>
       DocumentRoot "C:/xampp/htdocs/GadgetStorePOSsystem/public"
       ServerName 127.0.0.1
       <Directory "C:/xampp/htdocs/GadgetStorePOSsystem/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
   Port 8000 (not the Apache default 80) was chosen deliberately to match the origin the PWA was already installed against — changing it would break the installed app shortcut (PWA scope is tied to origin).
3. **Installed as Windows services** (requires an elevated/admin PowerShell):
   ```powershell
   & "C:\xampp\apache\bin\httpd.exe" -k install -n "ApacheSoftproPOS"
   & "C:\xampp\mysql\bin\mysqld.exe" --install MySQLSoftproPOS --defaults-file="C:\xampp\mysql\bin\my.ini"
   Set-Service ApacheSoftproPOS -StartupType Automatic
   Set-Service MySQLSoftproPOS -StartupType Automatic
   Start-Service ApacheSoftproPOS
   Start-Service MySQLSoftproPOS
   ```
4. **`.env`**: `APP_URL=http://127.0.0.1:8000`.

To check status / restart if needed:

```powershell
Get-Service ApacheSoftproPOS, MySQLSoftproPOS
Restart-Service ApacheSoftproPOS
```

The app is reachable at **http://127.0.0.1:8000** any time the PC is on.

## Installing the app as a desktop shortcut (PWA)

Once logged in, click the orange download-icon button in the top-right of the header ("Install app"). This only appears when the browser detects the app is installable (needs `public/manifest.json`, already present, and the service worker to be registered). Installing creates a standalone app window with no browser address bar — it should be launched from its own Start Menu/desktop icon, not by opening Chrome/Edge separately first.

## Rebuilding assets after a code change

Any change to `resources/css/`, `resources/js/`, or any Blade view that introduces a *new* Tailwind utility class requires a rebuild — Tailwind only generates CSS for classes it can find via static analysis at build time:

```powershell
npm run build
```

Forgetting this step is a common cause of "the page looks broken" after an edit — the HTML/JS is correct but the CSS for new classes doesn't exist yet.

## Generating additional translation files

The language switcher's translations (`lang/{locale}/nav.php`) were generated once via a free/unofficial Google Translate endpoint, as a one-time authoring step — not a runtime dependency:

```powershell
php artisan i18n:generate            # regenerate all 20 languages (skips ones that already exist)
php artisan i18n:generate --only=es,fr --force   # regenerate specific locales, overwriting
```

Only re-run this if new strings are added to `lang/en/nav.php` (the source file) and need translating. `lang/{locale}/nav.php` files are machine-generated — don't hand-edit them, edits will be overwritten on the next regeneration.

## Known limitations to be aware of

- **`APP_DEBUG=true`** in `.env` — fine for development, but means unhandled errors show a full stack trace instead of a clean error page. Set to `false` before handing this off to a client for real use, otherwise internal file paths and code get exposed on any bug.
- **`barryvdh/laravel-dompdf`** (used for PDF receipts/reports) is listed under `require-dev` in `composer.json`, not `require`. A production `composer install --no-dev` would break PDF generation — move it to `require` before a from-scratch production deploy that uses `--no-dev`.
- Printer/scanner hardware (thermal receipt printers, barcode scanners) need their own Windows drivers installed separately — see the receipt printing notes in `docs/USER_GUIDE.md`. This is outside the application itself.

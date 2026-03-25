# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Structure

```
mobileApp/
├── my-app/          # The Laravel + NativePHP application
├── SETUP.md         # Full setup guide and troubleshooting for Windows
└── CLAUDE.md        # This file
```

The actual app lives in `my-app/`. Always `cd my-app` before running any commands.

> `my-app/CLAUDE.md` is auto-managed by Laravel Boost — do not edit it manually.

## Running the App

All three servers run together with one command:
```bash
cd my-app
composer run dev
```

This concurrently starts `php artisan serve` (port 8000), `npm run dev` (Vite, port 5173), and `php artisan queue:listen`. Open **http://localhost:8000**.

Or run manually in two terminals:
```bash
php artisan serve       # Terminal 1
npm run dev             # Terminal 2
```

## First-Time Setup

```bash
cd my-app
composer run setup
```

This installs dependencies, creates `.env`, generates app key, runs migrations, and builds assets.

## Key Commands

```bash
composer run test          # Run full test suite
php artisan test --filter=TestName   # Run a single test
vendor/bin/pint --dirty    # Fix code style (run before finalizing changes)
php artisan migrate        # Run pending migrations
php artisan native:jump    # Launch app via the Jump mobile app (no Xcode/Android Studio needed)
php artisan native:run     # Build and run on device (requires Xcode or Android Studio)
```

## Stack

- **Laravel 12** — backend, routing, Eloquent ORM
- **NativePHP Mobile v3** — packages the Laravel app as a native iOS/Android app
- **Blade** — templating (views in `my-app/resources/views/`)
- **Tailwind CSS v4** — utility-first CSS (CSS-first config via `@theme`, no `tailwind.config.js`)
- **Vite 7** — asset bundling
- **SQLite** — default database (`my-app/database/database.sqlite`)
- **Pest v4** — testing

## NativePHP-Specific

- Native device APIs are accessed via the `#nativephp` import alias (maps to `vendor/nativephp/mobile/resources/dist/native.js`)
- Mobile-specific configuration lives in `my-app/nativephp/`
- The Jump app is the fastest way to preview on a real device — no native tooling required

## Windows PHP Requirements

The following must be enabled in `C:\php\php.ini`:
```ini
extension=curl
extension=gd
extension=pdo_sqlite
extension=sqlite3
```

After editing `php.ini`, kill all `php.exe` processes for changes to take effect.

## Known Fixes (do these on every new project)

**1. `.env` broken APP_URL** — the starter kit generates a double port. Fix it:
```
APP_URL=http://localhost:8000
```

**2. `URL::forceHttps()` crashes NativePHP on mobile** — remove it from `app/Providers/AppServiceProvider.php::boot()` immediately after creating a new project.

## Previewing on Phone (Jump)

```bash
php artisan native:jump
# Select: android
# Select: your WiFi IP (192.168.x.x) — not the 172.x virtual adapter
```
Then open `http://localhost:3000/jump/qr` and scan with the Jump app. Phone must be on the same WiFi.

# NativePHP Mobile App — Setup Guide (Windows)

## Prerequisites to install once

### 1. PHP
Download from https://php.net and install. Then enable these extensions in `C:\php\php.ini` (uncomment by removing `;`):
```ini
extension=curl
extension=gd
extension=pdo_sqlite
extension=sqlite3
```
After any change to `php.ini`, kill all `php.exe` processes for changes to take effect.

### 2. Composer
Download installer from https://getcomposer.org/download

### 3. Laravel Installer
```bash
composer global require laravel/installer
```
Make sure `C:\Users\<you>\AppData\Roaming\Composer\vendor\bin` is in your system PATH.

### 4. Node.js (v20.19+ or v22+)
Download from https://nodejs.org — get the LTS version (v22.x).

---

## Creating a new NativePHP mobile app

### Option A — Fresh app with NativePHP starter kit
```bash
laravel new my-app --using=nativephp/mobile-starter
cd my-app
```

### Option B — Add to existing Laravel app
```bash
composer require nativephp/mobile
php artisan native:install
```

---

## Running the app (every time)

Open **two terminals** and run one command in each:

**Terminal 1 — Laravel PHP server:**
```bash
cd my-app
php artisan migrate   # only needed first time
php artisan serve
```

**Terminal 2 — Vite asset server:**
```bash
cd my-app
npm install           # only needed first time
npm run dev
```

Then open **http://localhost:8000** in your browser.

---

## Previewing on a real phone (Jump app)

No Xcode or Android Studio needed.

### 1. Fix AppServiceProvider before running Jump
The NativePHP mobile starter includes `URL::forceHttps()` in `app/Providers/AppServiceProvider.php` which **crashes the app on mobile**. Remove it immediately after creating a new project:

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    // Remove URL::forceHttps() — crashes NativePHP on mobile
}
```

### 2. Install the Jump app
Search **"Jump NativePHP"** on Google Play (Android) or App Store (iOS).

### 3. Run the Jump server
Make sure your phone is on the **same WiFi** as your PC, then run:
```bash
cd my-app
php artisan native:jump
# Select: android (or ios)
# Select: your local WiFi IP (e.g. 192.168.0.x) — NOT 172.x.x.x (that's a virtual adapter)
```

### 4. Scan the QR code
Open `http://localhost:3000/jump/qr` in your browser and scan with the Jump app.

---

## Problems we solved & fixes

| Problem | Fix |
|---|---|
| `ext-curl` missing | Uncomment `extension=curl` in `C:\php\php.ini` |
| `ext-gd` missing (QR code error) | Uncomment `extension=gd` in `C:\php\php.ini` |
| `pdo_sqlite` missing | Uncomment `extension=pdo_sqlite` and `extension=sqlite3` in `C:\php\php.ini` |
| `APP_URL=http://localhost:8000:8000` | Fix `.env` → `APP_URL=http://localhost:8000` |
| SQLite error after fixing php.ini | Kill all `php.exe` processes and restart `php artisan serve` |
| Node.js too old for Vite | Upgrade to Node.js v22+ from nodejs.org |
| `laravel new` fails (folder exists) | Use `composer create-project laravel/laravel my-app` instead |
| `UrlGenerator` crash on phone after scan | Remove `URL::forceHttps()` from `AppServiceProvider::boot()` |

---

## Quick start checklist (next time)
- [ ] PHP extensions enabled (`curl`, `gd`, `pdo_sqlite`, `sqlite3`)
- [ ] `.env` has correct `APP_URL=http://localhost:8000`
- [ ] `URL::forceHttps()` removed from `AppServiceProvider`
- [ ] Run `php artisan migrate` on first run
- [ ] Two terminals: `php artisan serve` + `npm run dev`
- [ ] Open http://localhost:8000
- [ ] To preview on phone: `php artisan native:jump` → select WiFi IP → scan QR at http://localhost:3000/jump/qr

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

### 5. Android Studio (for installing on a real Android device)
Download from https://developer.android.com/studio

During first launch, let it install the **Android SDK** automatically.

Then install the **Google USB Driver** via SDK Manager:
- Tools → SDK Manager → SDK Tools tab → check **Google USB Driver** → Apply

Add `adb` to PATH permanently (run in PowerShell):
```powershell
[Environment]::SetEnvironmentVariable("Path", $env:Path + ";C:\Users\<you>\AppData\Local\Android\Sdk\platform-tools", "User")
```

Also set ANDROID_HOME permanently:
```powershell
[Environment]::SetEnvironmentVariable("ANDROID_HOME", "C:\Users\<you>\AppData\Local\Android\Sdk", "User")
```

Restart your terminal after setting these. Verify with `adb devices`.

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

> **Jump limitation:** Data does not persist between Jump sessions. The SQLite database lives on your PC, not the phone. Every new Jump session starts fresh. For persistent data you need a real native install (see below).

---

## Installing as a real native app on Android

This gives you a proper install on your phone with persistent data, just like any app from the Play Store.

### Step 1 — Enable Developer Mode on your phone
- **Samsung:** Settings → About Phone → Software Information → tap **Build Number** 7 times
- **Xiaomi:** Settings → About Phone → tap **MIUI version** 7 times
- **Google Pixel:** Settings → About Phone → tap **Build Number** 7 times
- **OnePlus:** Settings → About Device → tap **Build Number** 7 times

Then go back to Settings → **Developer Options** and enable **USB Debugging**.

### Step 2 — Connect your phone

**Option A: USB cable (simplest when it works)**
1. Plug in via USB
2. Pull down notification bar → tap USB notification → select **File Transfer (MTP)**
3. Tap **Allow** on the "Allow USB Debugging?" popup on your phone
4. Run `adb devices` — your phone should appear

> **Xiaomi note:** USB detection often fails even with the correct drivers. Use Wireless ADB (Option B) instead — it's more reliable.

**Option B: Wireless ADB (recommended for Xiaomi and when USB fails)**
1. Go to **Settings → Developer Options → Wireless Debugging** → turn ON
2. Tap **Wireless Debugging** to open it
3. Tap **"Pair device with pairing code"**
4. Note the IP:PORT and 6-digit code shown on your phone
5. In PowerShell:
```powershell
adb pair <IP:PORT>
# Enter the 6-digit code when prompted
```
6. Then connect using the main IP:PORT shown on the Wireless Debugging screen (different from the pairing port):
```powershell
adb connect <IP:PORT>
adb devices   # should show your device
```

> **Important:** Wireless ADB disconnects when you leave the Wireless Debugging screen or restart. Re-run `adb connect <IP:PORT>` at the start of each session.

### Step 3 — Fix the NativePHP build timeout

The NativePHP build runs `composer install` internally with a 300-second timeout which is too short. Increase it:

Edit `vendor/nativephp/mobile/src/Traits/PreparesBuild.php` line ~247:
```php
// Change this:
->timeout(300)
// To this:
->timeout(900)
```

> This edit is in the vendor directory and will be lost after `composer update`. Re-apply if the timeout error returns.

### Step 4 — Fix the Android SDK path

The NativePHP build generates `nativephp/android/local.properties` with an empty `sdk.dir`. After the first build attempt creates this file, set it:

```
sdk.dir=C\:\\Users\\<you>\\AppData\\Local\\Android\\Sdk
```

Note the escaped backslashes — this is required by the Java properties format.

### Step 5 — Build and install

Make sure `adb devices` shows your phone, then:
```bash
cd my-app
php artisan native:run android
```

First build takes 10–20 minutes (Gradle downloads dependencies). Subsequent builds are much faster.

If the build asks "Select an emulator to launch" instead of installing on your phone, your phone is not visible to adb. Re-run `adb connect <IP:PORT>` and try again.

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
| `adb` not recognized in terminal | Add `C:\Users\<you>\AppData\Local\Android\Sdk\platform-tools` to PATH |
| `adb devices` shows empty list | Switch USB mode to File Transfer, or use Wireless ADB instead |
| Xiaomi phone not detected via USB | Xiaomi USB detection is unreliable on Windows — use Wireless ADB |
| `adb connect` fails but `adb devices` shows device | Connection is already active (via mDNS). This is normal — proceed with build. |
| `libphp.a not found` during Gradle build | NativePHP places static libs at `nativephp/android/app/src/main/cpp/staticLibs/arm64-v8a/` but CMakeLists.txt expects `nativephp/android/app/src/main/staticLibs/arm64-v8a/`. Copy all `.a` files from `cpp/staticLibs/` up to `main/staticLibs/` (one level up). |
| composer install timeout during build | Increase timeout in `PreparesBuild.php` from 300 to 900 |
| `sdk.dir` empty in local.properties | Set it manually after first build attempt (see Step 4 above) |
| Build asks to select emulator instead of phone | Phone not visible to adb — run `adb connect <IP:PORT>` first |
| Jump session loses all data | Expected — Jump streams from PC. Use `native:run android` for real install |

---

## Quick start checklist (next time)

**Browser preview:**
- [ ] PHP extensions enabled (`curl`, `gd`, `pdo_sqlite`, `sqlite3`)
- [ ] `.env` has correct `APP_URL=http://localhost:8000`
- [ ] `URL::forceHttps()` removed from `AppServiceProvider`
- [ ] Run `php artisan migrate` on first run
- [ ] Two terminals: `php artisan serve` + `npm run dev`
- [ ] Open http://localhost:8000

**Jump preview:**
- [ ] Phone on same WiFi as PC
- [ ] `php artisan native:jump` → select WiFi IP → scan QR at http://localhost:3000/jump/qr

**Real Android install:**
- [ ] `adb connect <IP:PORT>` (Wireless ADB) or USB cable connected
- [ ] `adb devices` shows your phone (may show mDNS name, not IP:PORT — this is OK)
- [ ] `vendor/nativephp/mobile/src/Traits/PreparesBuild.php` timeout set to 900
- [ ] `nativephp/android/local.properties` has correct `sdk.dir`
- [ ] **Before build:** Copy PHP static libs from `nativephp/android/app/src/main/cpp/staticLibs/arm64-v8a/*.a` to `nativephp/android/app/src/main/staticLibs/arm64-v8a/` (if they exist in cpp/)
- [ ] `php artisan native:run android`

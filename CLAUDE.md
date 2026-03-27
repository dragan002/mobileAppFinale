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

```bash
cd my-app
composer run dev
```

Starts `php artisan serve` (port 8000), `npm run dev` (Vite, port 5173), and `php artisan queue:listen` concurrently. Open **http://localhost:8000**.

## Key Commands

```bash
composer run setup             # First-time: install deps, create .env, migrate, build assets
composer run test              # Run full test suite
php artisan test --filter=TestName   # Run a single test
vendor/bin/pint --dirty        # Fix code style (run before finalizing changes)
php artisan migrate            # Run pending migrations
php artisan native:jump        # Preview on phone via Jump app (no Xcode/Android Studio needed)
php artisan native:run         # Build and run on device (requires Xcode or Android Studio)
```

## App Architecture

**AtomicMe** is a personal habits app based on Atomic Habits principles. It is a **single-page application** served from one Blade view (`welcome.blade.php`), backed by a small Laravel JSON API.

### Frontend (`resources/views/welcome.blade.php`)

The entire UI lives in a single file — all HTML, CSS, and JavaScript inline. It is a client-side SPA with named screens toggled via `showScreen(id)`:

| Screen ID | Purpose |
|---|---|
| `screen-onboarding` | First-run identity selection + name |
| `screen-home` | Today's habits, progress card, daily quote |
| `screen-add` | 4-step habit creation (Atomic Habits 4 Laws) |
| `screen-stats` | Streaks, weekly grid, compound chart, per-habit breakdown |
| `screen-habit-detail` | 12-week heatmap, streak hero, insight message, Your Setup card, reminder toggle, complete button |
| `milestone-overlay` | Full-screen celebration at 7/14/21/30/60/90/100 day streaks |
| `profile-sheet` | Slide-up sheet from avatar tap: identity dashboard, edit name/identity, reset data |
| `weekly-review-overlay` | Sunday/7-day reflection prompt with weekly completion summary |

**State management:** A single `state` object `{ user, habits, completions, streaks, bestStreaks }` is kept in memory, persisted to `localStorage` as a fast cache, and synced to the backend via `fetch`. All UI mutations are optimistic — the UI updates instantly, then a background `api()` call syncs to the server.

**Key JS functions to know:**
- `init()` — loads from localStorage then refreshes from `/api/state`; calls `maybeShowWeeklyReview()` after sync
- `toggleHabit(id)` — optimistic completion toggle + POST `/api/completions/toggle`
- `showHabitDetail(id)` — renders detail screen including Your Setup card (4-Laws data) and reminder toggle
- `saveHabit()` — creates (POST) or updates (PUT) a habit; checks `editingHabitId` to determine mode
- `showEditHabit(id)` — pre-populates the 4-step add form for editing an existing habit
- `showMilestone(days)` — triggered by server response when a streak milestone is hit
- `openProfileSheet()` — computes identity stats (total votes = total completions) and opens profile sheet
- `maybeShowWeeklyReview()` — shows weekly reflection overlay on Sundays or after 7 days since last review

**localStorage keys:** `atomicme_state` (full state cache), `atomicme_reminders` (per-habit notification toggles), `atomicme_last_reviewed_week` (last weekly review date)

### Backend

All API routes live in `routes/web.php` (not `api.php`) so they share the web middleware stack (session, CSRF). CSRF token is embedded in the page via `<meta name="csrf-token">` and sent as `X-CSRF-TOKEN` on every fetch.

**API endpoints:**

| Method | Path | Controller | Purpose |
|---|---|---|---|
| GET | `/api/state` | `Api\StateController` | Full app state: user, habits, completions (last 90 days), current + best streaks |
| POST | `/api/setup` | `Api\SetupController` | Create/update user profile (upsert at id=1) |
| POST | `/api/habits` | `Api\HabitController` | Create a habit |
| DELETE | `/api/habits/{habit}` | `Api\HabitController` | Delete a habit (cascades completions) |
| PUT | `/api/habits/{habit}` | `Api\HabitController` | Update a habit (never touches completions or streaks) |
| POST | `/api/completions/toggle` | `Api\CompletionController` | Toggle today's completion; returns `{ completed, streak, milestone }` |
| POST | `/api/reflections` | `Api\ReflectionController` | Upsert weekly reflection by `week_of` date |
| DELETE | `/api/reset` | `Api\ResetController` | Wipe all user data (completions → habits → user_profile in FK order) |

### Models

- **`UserProfile`** — single row (id=1), stores name + identity choice. Table: `user_profile`.
- **`Habit`** — stores all 4-Law fields from the add form. Has `calculateStreak()` and `calculateBestStreak()` methods, plus `toApiArray()` that maps DB column names to the camelCase JS field names the frontend expects.
- **`HabitCompletion`** — `habit_id` + `completed_date` (unique together). Cascade-deleted when a habit is deleted.
- **`WeeklyReflection`** — `user_profile_id` + `week_of` (date, unique per user per week) + `note` (nullable text). Stores Sunday reflections.

### Streak logic

`Habit::calculateStreak()` walks backwards from today through ordered completions with a **one grace day tolerance** — a single missed day within a streak does not reset the count; two consecutive missed days do. The grace day flag is not reusable within the same streak walk. `calculateBestStreak()` uses the same tolerance. `CompletionController::toggle()` checks if the new streak hits a milestone (7, 14, 21, 30, 60, 90, 100) and returns it so the frontend triggers the celebration overlay. The frontend shows "grace day active" in purple when a streak is alive but today and yesterday are both incomplete.

## Database

SQLite at `database/database.sqlite`. App tables: `user_profile`, `habits`, `habit_completions`, `weekly_reflections`. The standard Laravel `users` table also exists but is unused by the app.

## Stack

- **Laravel 12** with **PHP 8.4**
- **NativePHP Mobile v3** — packages the app as a native iOS/Android app
- **Pest v4** — tests in `tests/Feature/` and `tests/Unit/`
- **Tailwind CSS v4** — CSS-first config via `@theme` in `resources/css/app.css`, no `tailwind.config.js`
- **Vite 7** — asset bundling (though `welcome.blade.php` uses inline styles, not Vite-built CSS)

## NativePHP-Specific

- Native device APIs: `#nativephp` import alias → `vendor/nativephp/mobile/resources/dist/native.js`
- Mobile config: `my-app/nativephp/` (intentionally gitignored — generated at build time)
- The `<native:bottom-nav>` component in `layouts/app.blade.php` renders a platform-native tab bar, but `welcome.blade.php` does not use the shared layout — it has its own inline bottom nav for the browser preview

## Building for Android (native install on phone)

Requires Android Studio, 7-Zip, and `ANDROID_HOME` + `adb` in PATH. Full setup details in `SETUP.md`.

**Critical build issues on Windows:**

1. **`sdk.dir` resets to empty** — every `native:install` regenerates `nativephp/android/local.properties` with an empty `sdk.dir`. Fix it after each install, or set `$env:ANDROID_HOME` in the session instead.

2. **Composer timeout during build** — `vendor/nativephp/mobile/src/Traits/PreparesBuild.php:247` has a 300s timeout that's too short. Change to `->timeout(900)`. This resets after `composer update`.

3. **PHP binaries must be downloaded separately** — `native:install` downloads them from `bin.nativephp.com`, but the fetch can fail silently. If `libphp.a` is missing in `nativephp/android/app/src/main/cpp/staticLibs/arm64-v8a/`, manually download from `https://bin.nativephp.com/main/versions.json` and extract with 7-Zip into `nativephp/android/app/src/main/`.

4. **Wireless ADB recommended** — USB detection is unreliable (especially Xiaomi). Use `adb pair <IP:PORT>` then `adb connect <IP:PORT>` via Developer Options → Wireless Debugging.

```bash
$env:ANDROID_HOME = "C:\Users\pclogiklabs\AppData\Local\Android\Sdk"
php artisan native:run android
```

## Windows PHP Requirements

Enable in `C:\php\php.ini`:
```ini
extension=curl
extension=gd
extension=pdo_sqlite
extension=sqlite3
```
Kill all `php.exe` processes after editing `php.ini`.

## Known Fixes (apply on every new project)

**`.env` double port** — starter kit generates a broken `APP_URL`. Fix:
```
APP_URL=http://localhost:8000
```

**`URL::forceHttps()` crashes NativePHP** — remove from `app/Providers/AppServiceProvider.php::boot()` immediately.

## Previewing on Phone (Jump)

```bash
php artisan native:jump
# Select: android
# Select: your WiFi IP (192.168.x.x) — not the 172.x virtual adapter
```
Open `http://localhost:3000/jump/qr` and scan with the Jump app. Phone must be on the same WiFi. Changes to PHP/Blade require a pull-to-refresh in Jump; JS/CSS changes hot-reload via Vite HMR.

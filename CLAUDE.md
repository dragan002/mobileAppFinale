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
| `screen-habit-detail` | 12-week heatmap, streak hero, insight message, complete button |
| `milestone-overlay` | Full-screen celebration at 7/14/21/30/60/90/100 day streaks |

**State management:** A single `state` object `{ user, habits, completions, streaks, bestStreaks }` is kept in memory, persisted to `localStorage` as a fast cache, and synced to the backend via `fetch`. All UI mutations are optimistic — the UI updates instantly, then a background `api()` call syncs to the server.

**Key JS functions to know:**
- `init()` — loads from localStorage then refreshes from `/api/state`
- `toggleHabit(id)` — optimistic completion toggle + POST `/api/completions/toggle`
- `showHabitDetail(id)` — renders the detail screen from in-memory state
- `saveHabit()` — POSTs to `/api/habits`, replaces temp ID with server ID on response
- `showMilestone(days)` — triggered by server response when a streak milestone is hit

### Backend

All API routes live in `routes/web.php` (not `api.php`) so they share the web middleware stack (session, CSRF). CSRF token is embedded in the page via `<meta name="csrf-token">` and sent as `X-CSRF-TOKEN` on every fetch.

**API endpoints:**

| Method | Path | Controller | Purpose |
|---|---|---|---|
| GET | `/api/state` | `Api\StateController` | Full app state: user, habits, completions (last 90 days), current + best streaks |
| POST | `/api/setup` | `Api\SetupController` | Create/update user profile (upsert at id=1) |
| POST | `/api/habits` | `Api\HabitController` | Create a habit |
| DELETE | `/api/habits/{habit}` | `Api\HabitController` | Delete a habit (cascades completions) |
| POST | `/api/completions/toggle` | `Api\CompletionController` | Toggle today's completion; returns `{ completed, streak, milestone }` |

### Models

- **`UserProfile`** — single row (id=1), stores name + identity choice. Table: `user_profile`.
- **`Habit`** — stores all 4-Law fields from the add form. Has `calculateStreak()` and `calculateBestStreak()` methods, plus `toApiArray()` that maps DB column names to the camelCase JS field names the frontend expects.
- **`HabitCompletion`** — `habit_id` + `completed_date` (unique together). Cascade-deleted when a habit is deleted.

### Streak logic

`Habit::calculateStreak()` walks backwards from today through ordered completions, counting consecutive days. If today is not yet completed, it still counts the streak up to yesterday (no penalty for an incomplete day). `CompletionController::toggle()` checks if the new streak is exactly a milestone value (7, 14, 21, 30, 60, 90, 100) and returns it so the frontend can trigger the celebration overlay.

## Database

SQLite at `database/database.sqlite`. Three app tables: `user_profile`, `habits`, `habit_completions`. The standard Laravel `users` table also exists but is unused by the app.

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

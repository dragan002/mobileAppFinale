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

### Frontend Architecture (Refactored to Modular)

**Status:** Recently refactored (March 2026) into 15 modular ES6 modules + centralized services.

The frontend lives in `resources/views/welcome.blade.php` (HTML structure + orchestration) and is powered by modular JavaScript in `resources/js/`:

```
resources/js/
├── app.js                 # Integration layer — imports all 15 modules, exposes window.App
├── state/
│   └── reducer.js        # Pure state mutations (initialState, updateUser, addHabit, toggleCompletion, etc.)
├── services/
│   └── api.js            # Centralized API calls (getState, setupUser, createHabit, toggleCompletion, saveReflection, resetAll)
├── screens/
│   ├── onboarding.js     # Identity selection + name entry
│   ├── home.js           # Today's habits, progress card, daily quote
│   ├── add.js            # 4-step habit creation form
│   ├── stats.js          # Streaks, weekly grid, habit breakdown
│   ├── growth.js         # Consistency score, weekly/monthly charts, habit consistency
│   └── habitDetail.js    # 12-week heatmap, streak hero, Your Setup card
├── overlays/
│   ├── milestone.js      # Streak celebration with confetti
│   ├── profileSheet.js   # Identity stats, edit name/identity, reset data
│   ├── weeklyReview.js   # Weekly reflection prompt
│   └── noteSheet.js      # Add completion notes
├── components/
│   ├── chart.js          # Shared chart rendering (compound, rate, projection)
│   ├── heatmap.js        # 12-week heatmap rendering
│   ├── habitCard.js      # Reusable habit item component
│   ├── streakCard.js     # Streak badge component
│   └── statRow.js        # Stats table row component
└── utils/
    ├── dom.js            # DOM helpers (getElement, on, addClass, etc.)
    └── forms.js          # Form helpers (getFieldValue, clearFields, etc.)
```

**Module Pattern:** Each screen/overlay exports:
- `render(state)` — returns HTML string
- `attachListeners(state, dispatch)` — wires up event handlers
- `cleanup()` — removes event listeners (prevents memory leaks)

**State Management:** Redux-inspired reducer pattern:
- `reducer.js` contains pure functions: `initialState()`, `updateUser(state, data)`, `addHabit(state, habit)`, etc.
- No side effects in reducer — all API calls happen in `app.js` orchestration layer
- Global `state` object: `{ user, habits, completions, streaks, bestStreaks, achievements }`
- Persisted to `localStorage` under `atomicme_v3` for fast cache

**API Service:** Centralized in `services/api.js`:
- All `fetch()` calls in one place
- CSRF token read from `<meta name="csrf-token">` automatically
- Standard response envelope: `{ ok, data, error }`
- Methods: `getState()`, `setupUser()`, `createHabit()`, `toggleCompletion()`, `saveReflection()`, `resetAll()`

### Frontend Screens

The UI is organized into named screens toggled via `showScreen(id)`. Each screen is a module in `resources/js/screens/`:

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

**Key orchestration functions in `welcome.blade.php`:**
- `init()` — boots app: loads localStorage cache, syncs from `/api/state`, triggers weekly review if needed
- `dispatch(action)` — applies state mutations via `reducer.js`, triggers UI re-render
- `showScreen(id)` — activates a screen module, calls its `render()` and `attachListeners()`
- `toggleHabit(id)` — optimistic completion toggle, posts to `/api/completions/toggle`
- `saveHabit()` — creates or updates habit, checks `editingHabitId` for mode
- `showEditHabit(id)` — loads habit into add-form for editing
- `sync()` — background loop that periodically syncs state to server via `api.getState()`

**localStorage keys:**
- `atomicme_v3` — full state cache (user, habits, completions, streaks, achievements)
- `atomicme_reminders` — per-habit notification toggle states
- `atomicme_last_reviewed_week` — date of last weekly reflection, used to show weekly review overlay once per week

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
- **`Habit`** — stores all 4-Law fields from the add form. Has `calculateStreakData()` and `calculateBestStreakData()` methods (accept optional preloaded dates to avoid N+1 queries), plus `toApiArray()` that maps DB column names to the camelCase JS field names the frontend expects. `calculatePhase()` accepts optional precomputed streak and completion count to avoid extra queries.
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

3. **PHP binaries must be downloaded separately** — `native:install` downloads them from `bin.nativephp.com`, but the fetch can fail silently. If `libphp.a` is missing in `nativephp/android/app/src/main/staticLibs/arm64-v8a/`, copy from the cached `staticLibs/arm64-v8a/` in the project root. The `cpp/staticLibs/` path is wrong — CMakeLists.txt looks for `cpp/../staticLibs/` which resolves to `main/staticLibs/`.

4. **`gradlew.bat` not found during `native:run`** — `native:run android` often fails with "'gradlew.bat' is not recognized". Workaround: after `native:run` fails at the Gradle step, fix `sdk.dir` and run Gradle directly:
   ```bash
   echo "sdk.dir=C:/Users/pclogiklabs/AppData/Local/Android/Sdk" > nativephp/android/local.properties
   cd nativephp/android && ./gradlew.bat assembleDebug
   adb install -r nativephp/android/app/build/outputs/apk/debug/app-debug.apk
   ```

5. **Wireless ADB recommended** — USB detection is unreliable (especially Xiaomi). Use `adb pair <IP:PORT>` then `adb connect <IP:PORT>` via Developer Options → Wireless Debugging.

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

## Common Development Tasks

### Adding a New Screen

1. Create `resources/js/screens/myScreen.js` with pattern:
   ```javascript
   export function render(state) { return `<div>...</div>`; }
   export function attachListeners(state, dispatch) { /* wire up events */ }
   export function cleanup() { /* remove listeners */ }
   ```

2. Add import to `resources/js/app.js` and expose via `window.App.screens.myScreen`

3. Add HTML structure to `welcome.blade.php` with id matching your screen

4. In `showScreen()`, call `App.screens.myScreen.render(state)` and `attachListeners()`

### Modifying State

Use pure reducer functions from `resources/js/state/reducer.js`. Example:
```javascript
const newState = App.reducer.updateHabit(state, habitId, updatedData);
dispatch({ type: 'UPDATE_HABIT', payload: { habitId, data: updatedData } });
```

### Adding API Calls

Add method to `resources/js/services/api.js`, then call from orchestration layer in `welcome.blade.php`:
```javascript
const result = await App.api.myNewEndpoint(params);
if (result.ok) { dispatch({ type: 'MY_ACTION', payload: result.data }); }
```

### Testing

The modular architecture is fully tested with Pest v4. Run:
```bash
php artisan test                    # All 123 tests
php artisan test --filter=HabitTest  # Specific test class
npm run build                       # Verify JS/CSS build succeeds
```

## Performance Notes

- **N+1 queries eliminated** — `StateController` preloads all completion dates in one bulk query, then passes them to `calculateStreakData()`, `calculateBestStreakData()`, and `calculatePhase()` via optional parameters. `/api/state` fires a fixed 5 queries regardless of habit count.
- **Session/cache use file driver** — not database. Avoids SQLite writes on every request (important for on-device performance).
- **Font loading is non-blocking** — uses `rel="preload"` with `onload` swap pattern, not a render-blocking `<link rel="stylesheet">`.
- **Splash screen** — branded `splash.xml` drawable in `nativephp/android/app/src/main/res/drawable/`. Shows atom + checkmark design during PHP runtime boot (~3-5s, inherent to NativePHP).
- **Version must be set** — `NATIVEPHP_APP_VERSION` in `.env` must be a real semver (e.g. `1.0.0`), not `DEBUG`. `DEBUG` forces 97MB ZIP re-extraction on every cold start.
- **Cold-start retry** — `init()` in `welcome.blade.php` retries the `/api/state` fetch once (800ms delay) if the first attempt fails. On NativePHP Android, the embedded PHP server may not be ready when the WebView first renders. Without the retry, the app falls through to onboarding even when a user profile exists in the DB.
- **Identity-specific daily quotes** — `home.js` selects motivational prompts based on the user's chosen identity (athlete, learner, creator, etc.) rather than showing generic quotes.

## Play Store

- **Bundle ID:** `com.atomicme.app` (set in `.env` as `NATIVEPHP_APP_ID`)
- **Privacy policy:** `https://dragan002.github.io/mobileAppFinale/docs/privacy-policy.html`
- **Contact email:** `draganvujic29@gmail.com`
- **Category:** Health & Fitness
- **Content rating:** Everyone

## Project Status

**April 2026 Updates:**
- All 123 tests passing (354 assertions)
- Performance optimized: N+1 queries eliminated, non-blocking font, file-based session/cache
- Custom app icon and branded splash screen
- Play Store readiness: Privacy policy created, critical blockers resolved

See `PLAYSTORE_CRITICAL_BLOCKERS_STATUS.md` and `LAYOUT_ISSUES_AND_FIXES.md` for recent fixes and compliance status.

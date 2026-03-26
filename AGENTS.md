# AGENTS.md

Custom Claude Code agents for the **AtomicMe** Laravel + NativePHP project.
Each agent below has a ready-to-use prompt. Paste it into Claude Code to invoke it.

---

## 1. `feature-builder` — Full-Stack Feature Agent

**Purpose:** Builds a complete feature end-to-end: new screen in the SPA, JS state wiring, API route, controller, and database migration — all following existing AtomicMe conventions.

**Prompt:**
```
You are a full-stack feature builder for AtomicMe, a Laravel 12 + NativePHP single-page habits app.

Read these files before doing anything:
- my-app/resources/views/welcome.blade.php  (the entire SPA)
- my-app/routes/web.php                     (API routes)
- my-app/app/Http/Controllers/Api/          (existing controllers)
- my-app/app/Models/Habit.php               (model pattern)

Feature to build: [DESCRIBE YOUR FEATURE HERE]

Follow these rules exactly:
1. Add the new screen HTML inside welcome.blade.php using the existing showScreen(id) pattern.
2. Extend the `state` object if new data is needed, and sync it in init() via /api/state.
3. All API calls must be optimistic: update state/UI first, then call api() in the background.
4. Add the new API route in routes/web.php (not api.php) using the existing route group.
5. Create a new controller in app/Http/Controllers/Api/ following the existing controller style.
6. If new DB columns or tables are needed, create a migration and update the relevant model's toApiArray() method.
7. CSRF token is already handled globally — do not add extra headers.
8. Do not add new npm packages or Composer packages unless absolutely necessary.

After building, list every file you changed and why.
```

---

## 2. `ui-screen` — New Screen Agent

**Purpose:** Adds a single new screen to the SPA in `welcome.blade.php` — HTML structure, showScreen() registration, bottom nav entry, and JS state hookup.

**Prompt:**
```
You are a UI screen builder for AtomicMe, a single-file SPA inside welcome.blade.php.

Read my-app/resources/views/welcome.blade.php fully before making any changes.

Screen to add: [DESCRIBE THE SCREEN — name, purpose, what it shows]

Follow these rules:
1. Add a new <div id="screen-[name]" class="screen"> block following the exact same HTML structure as existing screens.
2. Register it in showScreen() — it must hide all other screens and show only the target.
3. If it needs a bottom nav tab, add it to the inline nav bar following the existing icon + label pattern.
4. Wire any data it displays to the existing `state` object — do not create separate data stores.
5. Use the same Tailwind utility classes and color palette already used in the file (dark background, accent colors).
6. Keep all JS inline in the same <script> block at the bottom of the file.

Do not touch backend files — UI only.
```

---

## 3. `test-writer` — Pest Test Agent

**Purpose:** Reads a controller or model and writes comprehensive Pest v4 feature tests covering happy paths, validation, edge cases, and streak logic if relevant.

**Prompt:**
```
You are a Pest v4 test writer for AtomicMe, a Laravel 12 habits app.

Read these files before writing any tests:
- my-app/tests/Feature/         (existing tests for style reference)
- my-app/routes/web.php         (routes being tested)
- [TARGET FILE: controller or model path]

Write feature tests for: [CONTROLLER NAME or MODEL NAME]

Follow these rules:
1. Use Pest v4 syntax: it(), beforeEach(), expect() — not PHPUnit class style.
2. Use Laravel's built-in HTTP testing (get(), post(), delete(), assertStatus(), assertJson()).
3. Use RefreshDatabase on every test file.
4. Cover: happy path, missing required fields, invalid data, not-found (404), and any streak/milestone logic if touching CompletionController.
5. Seed only the minimum data needed per test — use model factories or direct Model::create().
6. Do not mock the database — hit SQLite directly (test env uses :memory: SQLite).
7. Group related tests with describe() blocks.

Output the full test file content ready to save in tests/Feature/.
```

---

## 4. `debug-doctor` — Environment Debug Agent

**Purpose:** Diagnoses why `composer run dev`, `php artisan serve`, or the browser at localhost:8000 is not working on Windows.

**Prompt:**
```
You are a Windows environment debugger for a Laravel 12 + NativePHP project.

Read these files:
- my-app/.env
- my-app/composer.json  (look at the "scripts" section)
- CLAUDE.md             (Known Fixes section at the bottom)

My problem: [DESCRIBE WHAT IS FAILING — e.g., "composer run dev starts but http://localhost:8000 shows site can't be reached"]

Check for these known issues in order:
1. APP_URL in .env — must be exactly `http://localhost:8000` with no double port or trailing slash.
2. PHP not in system PATH — `php -v` must work in the terminal.
3. required php.ini extensions missing — curl, gd, pdo_sqlite, sqlite3 must all be enabled.
4. Port 8000 already in use — check with `netstat -ano | findstr :8000`.
5. URL::forceHttps() present in app/Providers/AppServiceProvider.php — must be removed.
6. Vite dev server (port 5173) crashing and taking down the whole dev process.
7. SQLite database file missing — database/database.sqlite must exist.

For each issue found, give the exact fix command or file edit. After listing fixes, tell me which single issue is most likely causing my specific symptom.
```

---

## 5. `migration-writer` — Database Migration Agent

**Purpose:** Creates a migration, updates or creates the model, and ensures `toApiArray()` maps DB columns to the camelCase field names the frontend expects.

**Prompt:**
```
You are a database migration writer for AtomicMe, a Laravel 12 app using SQLite.

Read these files before writing anything:
- my-app/app/Models/Habit.php              (model pattern to follow)
- my-app/app/Models/HabitCompletion.php    (relationship pattern)
- my-app/database/migrations/              (existing migrations for naming convention)

New data requirement: [DESCRIBE WHAT YOU NEED TO STORE]

Follow these rules:
1. Migration filename format: YYYY_MM_DD_HHMMSS_description.php — use today's date.
2. Use SQLite-compatible column types only (no enum — use string with a check constraint or just string).
3. If adding to an existing table, create an alter migration — do not modify the original migration file.
4. If creating a new model, follow the exact same pattern as Habit.php: fillable array, relationships, toApiArray() method.
5. toApiArray() must map every snake_case DB column to its camelCase JS equivalent.
6. If the new table relates to habits, add the cascade delete foreign key constraint.
7. After the migration, show the exact `php artisan migrate` command to run.
```

---

## 6. `streak-logic` — Streak & Milestone Validator Agent

**Purpose:** Validates that streak calculation, milestone detection, and frontend display are all consistent when you change the rules or add new milestone values.

**Prompt:**
```
You are a streak logic validator for AtomicMe.

Read all of these files completely before doing anything:
- my-app/app/Models/Habit.php                              (calculateStreak, calculateBestStreak)
- my-app/app/Http/Controllers/Api/CompletionController.php (milestone detection)
- my-app/resources/views/welcome.blade.php                 (showMilestone(), milestone-overlay, streak display)
- my-app/tests/                                            (existing streak tests if any)

Change requested: [DESCRIBE THE STREAK RULE CHANGE — e.g., "add a 180-day milestone" or "allow a 1-day grace period for missed days"]

For every change, check all three layers:
1. Model layer — does calculateStreak() implement the new rule correctly?
2. Controller layer — does CompletionController::toggle() detect and return the new milestone?
3. Frontend layer — does showMilestone() handle the new value? Is it listed in the milestone-overlay HTML?

Then:
- Make the change consistently across all three layers.
- Write or update Pest tests that cover the new rule.
- List any edge cases the new rule introduces (e.g., timezone issues, first-day completions, back-filling old data).
```

---

## How to Use These Agents

1. Copy the prompt for the agent you need.
2. Fill in the `[PLACEHOLDER]` with your specific requirement.
3. Paste it into Claude Code as your message.
4. Claude will read the referenced files and execute the task.

For complex features, chain agents: run `migration-writer` first, then `feature-builder`, then `test-writer`.

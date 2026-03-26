# AtomicMe — Product Plan

This file is the source of truth for planned features. When starting a coding session, read this file first. Work features in priority order unless told otherwise.

---

## Feature Priority Order

1. Show 4-Laws Data on Habit Detail Screen ← **start here (easiest win)**
2. Edit a Habit
3. Bad Day Recovery — Streak Grace Day
4. Habit Reminders / Push Notifications
5. Weekly Review / Reflection Prompt

---

## Feature 1 — Show 4-Laws Data on Habit Detail Screen

**Status:** Not started

**What:** The habit detail screen (`screen-habit-detail`) currently shows only the streak hero, 12-week heatmap, and a complete button. When a user creates a habit they fill in rich 4-Laws data — their why, 2-minute version, habit stack, temptation bundle, and reward — but none of it is shown after creation. Surface this data on the detail screen so users are reminded of their motivation and strategy every time they open it.

**Why:** The data already exists in the `Habit` model and is returned by `toApiArray()` and the `/api/state` endpoint. This is a pure frontend change. It makes the creation flow retroactively valuable and reinforces the habit identity (Make it Attractive + Make it Easy).

**Files to touch:**
- `my-app/resources/views/welcome.blade.php` — add a "Your Setup" card inside `screen-habit-detail`, populated from the in-memory `state.habits` array

**Fields to display (from `state.habits[i]`):**
- `why` — "Your Why"
- `twoMinVersion` — "2-Minute Version"
- `stack` — "Habit Stack" (e.g. "After I brush my teeth...")
- `bundle` — "Temptation Bundle" (e.g. "While I do X, I get Y")
- `reward` — "Your Reward"

**Acceptance criteria:**
- All five fields appear on the habit detail screen below the heatmap
- Fields with empty values are hidden (not shown as blank)
- Styling matches the existing dark card aesthetic

---

## Feature 2 — Edit a Habit

**Status:** Not started

**What:** Users can currently create or delete a habit, but cannot edit one. Deleting and recreating destroys streak and completion history. Add the ability to edit a habit's name, emoji, and all 4-Laws fields without touching completions.

**Why:** Anyone who uses the app for more than a week will want to fix a typo, update their 2-minute version, or adjust their habit stack. Without edit, the only option destroys progress.

**Files to touch:**
- `my-app/routes/web.php` — add `PUT /api/habits/{habit}` route
- `my-app/app/Http/Controllers/Api/HabitController.php` — add `update()` method (validate + update fields, do not touch completions)
- `my-app/resources/views/welcome.blade.php` — reuse the existing 4-step `screen-add` form in "edit mode": pre-populate fields, change the submit button label to "Save Changes", and call `PUT /api/habits/{id}` instead of `POST /api/habits`

**Constraints:**
- Do NOT delete or reset completions when a habit is edited
- Streak and best streak are computed from completions, so they are unaffected automatically
- The habit `id` must not change

**Acceptance criteria:**
- An "Edit" button appears on the habit detail screen
- Tapping it opens the 4-step form pre-populated with current values
- Saving updates the habit in state and re-renders the detail screen
- Completions and streaks are unchanged after an edit

---

## Feature 3 — Bad Day Recovery (Streak Grace Day)

**Status:** Not started

**What:** The current streak logic resets to zero if a single day is missed. Implement a "never miss twice" rule: allow one missed day within a streak without breaking it. The streak still counts as active if the only gap is yesterday (one day).

**Why:** Losing a long streak due to one bad day is the most common reason users abandon habits apps. James Clear's rule is: one missed day is an accident, two in a row is the start of a new bad habit. A grace day keeps users engaged through inevitable slip-ups.

**Files to touch:**
- `my-app/app/Models/Habit.php` — update `calculateStreak()` to tolerate a single missed day within the streak walk. If the walk encounters exactly one missing date and the next date is completed, continue counting; if it encounters two or more consecutive missing dates, stop.
- `my-app/resources/views/welcome.blade.php` — optionally show a small "grace day active" indicator on the home screen when a streak is running but today is not yet completed and yesterday was missed

**Constraints:**
- `calculateBestStreak()` should use the same tolerance so best streaks are consistent
- The grace day does NOT auto-complete the missed day — it just doesn't break the count
- Unit tests for `calculateStreak()` must be updated to cover the grace day case

**Acceptance criteria:**
- Missing one day within a streak does not reset the count
- Missing two consecutive days resets the streak to zero
- The grace day behavior is covered by at least two unit tests in `tests/Unit/`

---

## Feature 4 — Habit Reminders / Push Notifications

**Status:** Not started

**What:** Each habit has a `time_of_day` field captured at creation but never used. Send a daily push notification for each habit at its scheduled time, prompting the user to complete it.

**Why:** Without reminders, the app only works when the user remembers to open it. Notifications are the single biggest driver of daily retention in habit apps. This is the highest-leverage feature for long-term engagement.

**Files to touch:**
- `my-app/resources/views/welcome.blade.php` — request notification permission on onboarding completion; show a toggle per habit on the habit detail screen to enable/disable its reminder
- NativePHP notification API — schedule a local notification per habit using the `time_of_day` value. Check NativePHP Mobile v3 docs for the correct API (`\Native\Mobile\Notifications` or equivalent)
- `my-app/app/Http/Controllers/Api/HabitController.php` — may need to return `time_of_day` in the response if not already included
- `my-app/app/Models/Habit.php` — confirm `time_of_day` is included in `toApiArray()`

**Constraints:**
- Notifications must be local (scheduled on-device), not server-push — there is no push notification server
- If the user has not granted notification permission, fail silently and show a prompt
- Respect the per-habit toggle: if a habit has no reminder enabled, do not schedule one

**Acceptance criteria:**
- User is asked for notification permission during or after onboarding
- Each habit shows a reminder toggle on its detail screen
- Enabling the toggle schedules a daily notification at `time_of_day`
- Disabling the toggle cancels the scheduled notification

---

## Feature 5 — Weekly Review / Reflection Prompt

**Status:** Not started

**What:** On Sunday evenings (or after 7 days of app use), show a full-screen weekly review overlay. It displays the week's habit completion grid, highlights streaks, and asks one reflection question: "What worked this week? What was hard?" The user can type a short note and save it.

**Why:** Atomic Habits emphasizes periodic review as the mechanism for long-term improvement. A weekly reflection gives users a reason to engage with their data and feel good about progress. It also seeds future insight features.

**Files to touch:**
- `my-app/database/migrations/` — new migration: `weekly_reflections` table (`id`, `user_profile_id`, `week_of` date, `note` text, `created_at`)
- `my-app/app/Models/WeeklyReflection.php` — new model
- `my-app/routes/web.php` — add `POST /api/reflections` route
- `my-app/app/Http/Controllers/Api/ReflectionController.php` — new controller, store a reflection for the current week (upsert by week_of)
- `my-app/resources/views/welcome.blade.php` — new `weekly-review-overlay` (similar structure to `milestone-overlay`), triggered on Sunday or after 7-day interval, dismissed after saving or skipping

**Acceptance criteria:**
- Overlay appears on Sunday or after 7 days since last review (whichever comes first)
- Displays weekly completion summary (which habits were done, which were missed)
- User can type a note and save it, or skip
- Saved reflections are stored in the database
- The overlay does not appear again until the next week

---

## Technical Notes for the Coding Agent

- All API routes go in `routes/web.php` (not `api.php`) — shares session/CSRF middleware
- CSRF token is sent as `X-CSRF-TOKEN` header on every fetch — do not remove this
- Frontend state lives in the `state` object and is persisted to `localStorage` as a cache — always update `state` in memory and re-render after API calls
- UI updates are optimistic: update `state` and re-render first, then sync to the server in the background
- SQLite database at `database/database.sqlite`
- Run `vendor/bin/pint --dirty` before finalizing any PHP changes
- Run `composer run test` to verify nothing is broken before considering a feature done
- The single Blade file is large — search for existing screen IDs and JS functions before adding new ones to avoid duplication

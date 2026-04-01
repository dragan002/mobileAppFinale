# Performance Audit Report — Xiaomi 14c

Audit date: 2026-03-30
Auditor: Code review (static analysis + live server probe)

---

## Baseline Measurements

| Metric | Value | Target | Status |
|---|---|---|---|
| JS bundle (Vite production build) | 81 KB | < 200 KB | OK |
| CSS bundle (Vite production build) | 18 KB | < 50 KB | OK |
| `/api/state` response time (local, 7 habits, 13 completions) | ~443 ms | < 200 ms | OVER TARGET |
| APK size | Not yet built (requires Android Studio) | < 150 MB | Unknown |
| Animations constantly running | `shimmer` on progress card (all-done state) — `3s linear infinite` | None when idle | Potential issue |

> The 443 ms `/api/state` figure was measured against the dev server with only 7 habits and 13 completions. On a production app with 50+ habits and hundreds of completions, this will scale badly. The budget is 200 ms.

---

## Bottlenecks Identified

### 1. Critical — N+1 Query Storm in `toApiArray()` (every `/api/state` load)

**File:** `app/Models/Habit.php`, `app/Http/Controllers/Api/StateController.php`

`StateController::index()` calls `$habits->map->toApiArray()` on every habit. `toApiArray()` internally calls three methods — `calculatePhase()`, `calculateStreakData()`, and `calculateBestStreakData()` — each of which issues its own `SELECT` against `habit_completions`:

```
calculateStreakData()     → SELECT completed_date FROM habit_completions WHERE habit_id = ? ORDER BY completed_date DESC
calculateBestStreakData() → SELECT completed_date FROM habit_completions WHERE habit_id = ? ORDER BY completed_date ASC
calculatePhase()          → calculateStreak() → calculateStreakData() again (redundant call)
                         + SELECT COUNT(*) FROM habit_completions WHERE habit_id = ? AND completed_date >= ?
```

That is **4 queries per habit** in `toApiArray()`, plus `StateController` also calls `calculateStreakData()` and `calculateBestStreakData()` a second time in its own loop (lines 49–56), duplicating 2 of those 4 queries per habit.

**Total queries for `/api/state` with N habits:**
- 1 `UserProfile::first()`
- 1 `HabitCompletion` bulk fetch (90 days)
- 1 `Category` fetch
- 1 `UserAchievement` with `->with('achievement')`
- N × `calculateStreakData()` (StateController loop)
- N × `calculateBestStreakData()` (StateController loop)
- N × `calculatePhase()` → calls `calculateStreak()` → `calculateStreakData()` again + 1 extra count query
- N × `calculateStreakData()` (inside `toApiArray()`)
- N × `calculateBestStreakData()` (inside `toApiArray()`)

With 7 habits that is already **~35 SQL calls**. With 20 habits it reaches ~100. With 50 habits it exceeds 200.

**Why it is slow:** `calculateStreakData()` and `calculateBestStreakData()` each do a raw `$this->completions()->orderBy(...)->pluck(...)` — they do not reuse the bulk completion data already fetched by `StateController`. Every call hits SQLite independently.

---

### 2. Critical — `AchievementEvaluator` Issues Its Own N+1 Queries on Every Completion Toggle

**File:** `app/Services/AchievementEvaluator.php`, `app/Http/Controllers/Api/CompletionController.php`

On every `POST /api/completions/toggle` (every time the user ticks a habit), `AchievementEvaluator::evaluate()` runs up to 7 check functions. The expensive ones:

- `checkPerfectWeek()` — loops over every day of the current week, and for each day issues a separate `HabitCompletion::where('completed_date', $day)->pluck('habit_id')`. That is **up to 7 queries** just for this one check.
- `checkOnePercentClub()` — calls `$habit->calculateStreak()` in a loop over all habits. With N habits that is **N streak calculations**, each querying the DB.
- `checkAtomicIdentity()` — calls `$habit->calculatePhase()` in a loop. `calculatePhase()` calls `calculateStreak()` (DB query) plus a count query. **2N queries**.

Total worst-case queries added to a single toggle: 7 (perfect_week days) + N×2 (one_percent_club) + N×2 (atomic_identity) = **4N + 7** extra queries. With 7 habits that is ~35 extra queries on top of the toggle itself. The user tapping a habit checkbox triggers this entire chain synchronously before the response is returned.

---

### 3. High — `Habit::toApiArray()` Calls `calculatePhase()` Which Redundantly Re-Invokes `calculateStreakData()`

**File:** `app/Models/Habit.php` lines 309–311

```php
$phase = $this->calculatePhase();         // calls calculateStreak() → calculateStreakData() (1 DB query)
$streakData = $this->calculateStreakData(); // same query, again
$bestStreakData = $this->calculateBestStreakData(); // again
```

`calculatePhase()` calls `$this->calculateStreak()` which calls `$this->calculateStreakData()` — that is the same DB query that `toApiArray()` then calls again on the very next line. The result is discarded and recomputed.

---

### 4. High — `AnalyticsController` Issues N+1 Queries Across 12 Month-Loops

**File:** `app/Http/Controllers/Api/AnalyticsController.php`

`calculateMonthlyRates()` loops 12 months. For each month it fetches all habits (`Habit::where(...)->get()`), then for each habit within that month it issues a separate `HabitCompletion::where('habit_id', ...)->whereBetween(...)` count. With N habits and 12 months that is **12 × N queries** just for monthly rates. The weekly variant adds another 5 × N. With 7 habits that is already ~119 queries for one `/api/analytics` call. The growth screen triggers this every time it is opened.

---

### 5. High — `stats.js` and `growth.js` Use O(D×H) Nested Loops With `.some()` on Every Date Iteration

**File:** `resources/js/screens/stats.js`, `resources/js/screens/growth.js`

Multiple calculation functions iterate over every date in `state.completions` and for each date call `.filter(id => habits.some(h => String(h.id) === String(id)))`. This is an O(D × H) scan where D is the number of dates (up to 90) and H is the number of habits.

The most impactful example is `calcAllTimeScore()` in `growth.js` (line 114):

```js
Object.keys(state.completions).forEach(date => {
    const done = (state.completions[date] || [])
        .filter(id => state.habits.some(h => String(h.id) === String(id))).length;
    // ...
});
```

`calcAllTimeScore`, `calcWeeklyScore`, `calcMonthlyScore`, `calcDailyScore`, and `renderHabitConsistencyHtml` all perform similar scans. `updateGrowthScreen()` calls all four score functions plus `renderHabitConsistencyHtml` every time the growth screen is opened. Each of those creates `new Date()` objects inside loops, parses strings, and calls `.map(String)` repeatedly.

`calcCompletionRate()` in `stats.js` (line 56) iterates `days` times (up to 30) and for each iteration creates a `new Date()`, calls `.toISOString()`, and runs `.map(String).includes(...)`. This function is called once per habit in `renderHabitBreakdownHtml`, so opening stats with 10 habits = 300 Date constructions + 300 string conversions + 300 array scans.

---

### 6. High — `shimmer` CSS Animation Runs Continuously on the Progress Card

**File:** `resources/views/welcome.blade.php` lines 85–93

```css
.progress-card.all-done {
  animation: shimmer 3s linear infinite;
}
```

The `shimmer` animation animates `background-position` — a non-composited property. On a mid-range GPU like the Xiaomi 14c's Mali-G57, non-composited animations force a paint and composite on every frame (60 fps = 60 repaints/s on an element with a gradient background). This runs continuously as long as all habits are done. `background-position` cannot be GPU-accelerated via the compositor thread; it runs on the main thread.

---

### 7. Medium — `showScreen()` Calls `document.querySelectorAll('.screen')` on Every Navigation

**File:** `resources/views/welcome.blade.php` line 1355

```js
document.querySelectorAll('.screen').forEach(s => s.classList.remove('active', 'slide-left'));
```

This traverses the entire DOM subtree on every tab tap. There are 7+ screen divs. Not catastrophic but it is unnecessary churn on every navigation.

---

### 8. Medium — `toggleHabitFromDetail()` Uses `setTimeout(() => showHabitDetail(...), 400)`

**File:** `resources/views/welcome.blade.php` line 1643

Every tap of the complete button from the detail screen waits 400 ms then re-runs `showHabitDetail()`, which calls `updateDetailScreen()`, which calls `renderHeatmapHtml()` — a function that iterates 12 × 7 = 84 days, creating 84 Date objects and 84 string lookups in `state.completions`. On a slow device the 400 ms delay hides the computation but the render still happens synchronously and can cause a visible stutter.

---

### 9. Medium — `renderHeatmapHtml()` Creates 84 Date Objects Per Render With No Caching

**File:** `resources/js/screens/habitDetail.js` lines 214–247

The heatmap is re-rendered from scratch every time `showHabitDetail()` is called. 84 `new Date()` objects are created, `.setDate()` called on each, `.toISOString().slice(0, 10)` called on each. No caching between renders of the same habit. This is harmless for a single open but becomes noticeable when `toggleHabitFromDetail()` re-renders the detail screen on every toggle.

---

### 10. Low — `getStreakEmoji` and `todayKey` are Duplicated Across 4 Modules

**Files:** `home.js`, `stats.js`, `habitDetail.js`, `growth.js`, `welcome.blade.php`

Each module has its own copy of `todayKey()` and `getStreakEmoji()`. Not a performance issue but creates maintenance risk — a bug fix in one copy will not propagate to others.

---

### 11. Low — No `habit_completions` Index on `completed_date` Alone

**Migration:** `2026_03_25_000003_create_habit_completions_table.php`

The composite unique index `(habit_id, completed_date)` exists. However, `StateController` queries `WHERE completed_date >= ?` without a `habit_id` filter. On that query SQLite will use the composite index if `habit_id` is the leading column, which it is — but the leading column is not constrained, so SQLite may do a full index scan. With 90+ days × N habits this is a partial scan. Currently with 13 rows it is irrelevant, but will matter at 1000+ rows.

---

### 12. Low — External Font Load Blocks Render

**File:** `resources/views/welcome.blade.php` line 10

```html
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
```

This is a synchronous render-blocking stylesheet loaded from an external CDN. On a fresh NativePHP cold start with no network cache, this DNS + TLS + HTTP request must complete before the first paint. There is no `font-display: swap` fallback and no `preload` attribute. On a device with marginal connectivity this alone can add 300–800 ms to first paint.

---

## Root Causes Summary

| Root Cause | Impact |
|---|---|
| `toApiArray()` re-runs streak queries already computed in `StateController` | N×2 redundant DB queries per `/api/state` |
| `calculatePhase()` calls `calculateStreakData()` internally, `toApiArray()` calls it again | N redundant DB queries per `/api/state` |
| `AchievementEvaluator` does N×4 + 7 queries per toggle | Toggle latency scales with habit count |
| `AnalyticsController` loops 12 months × N habits with per-habit queries | 119+ queries per growth screen open |
| `.some()` in nested loops in stats/growth JS | O(D×H) JS work on every screen open |
| `shimmer` on non-composited CSS property | Continuous GPU paint on completed state |
| External font without `font-display: swap` | First-paint delay on cold start |

---

## Recommended Fixes (Prioritised by Impact)

### Phase 1 — Quick Wins (estimated: 2–3 hours)

**Fix 1: Eliminate the double streak computation in `StateController` + `toApiArray()`**

`StateController` already calls `calculateStreakData()` and `calculateBestStreakData()` in its own loop and stores the results. It then calls `$habits->map->toApiArray()` which calls those same methods again. Pass the pre-computed streak data into `toApiArray()` or split `toApiArray()` into a version that accepts pre-computed values. This eliminates N×2 redundant queries.

**Fix 2: Fix `calculatePhase()` to accept a pre-computed streak instead of re-querying**

`calculatePhase()` calls `$this->calculateStreak()` internally. Change it to accept `int $streak` as a parameter. `toApiArray()` can then pass in the already-computed streak value. Eliminates N redundant queries.

**Fix 3: Replace the `shimmer` animation with a compositor-friendly version**

Replace `background-position` animation with `opacity` or `transform` animation, which can run on the GPU compositor thread without triggering layout or paint:

```css
/* Before: causes paint every frame */
animation: shimmer 3s linear infinite;  /* animates background-position */

/* After: compositor-only, no paint */
.progress-card.all-done::after {
  animation: shimmer-overlay 3s linear infinite; /* animates opacity on a pseudo-element */
}
```

Alternatively, remove the shimmer entirely — a subtle `opacity` pulse costs almost nothing.

**Fix 4: Add `font-display: swap` and convert to self-hosted font or system font fallback**

Either add `&display=swap` to the Bunny Fonts URL, or replace the external font entirely with system-ui which is zero-cost on Android. This removes a render-blocking network dependency from cold start.

---

### Phase 2 — Medium Effort (estimated: 3–4 hours)

**Fix 5: Rewrite `AchievementEvaluator` to work with pre-loaded data**

Refactor `evaluate()` to accept the completions collection and habits collection as parameters rather than re-querying. `checkPerfectWeek()` should load all completions for the week in one query and filter in PHP. `checkOnePercentClub()` and `checkAtomicIdentity()` should reuse the streak data already computed for the toggle response. This collapses ~35 queries (with 7 habits) down to 2–3.

**Fix 6: Rewrite `AnalyticsController` to use aggregated queries**

Replace the per-habit loop with a single aggregated SQL query per time period:

```sql
SELECT habit_id, COUNT(*) as completions
FROM habit_completions
WHERE completed_date BETWEEN ? AND ?
GROUP BY habit_id
```

This collapses N queries per period into 1 query per period. Monthly rates drop from 12N queries to 12 queries.

**Fix 7: Build habit ID sets in stats.js and growth.js instead of using `.some()`**

Replace repeated `.some(h => String(h.id) === String(id))` with a `Set` built once before the loop:

```js
const habitIdSet = new Set(state.habits.map(h => String(h.id)));
// Then: habitIdSet.has(String(id))  — O(1) instead of O(H)
```

This converts O(D×H) loops to O(D) loops. At 90 days and 10 habits, this is a 10× reduction in comparisons across all stats/growth calculations.

**Fix 8: Cache heatmap output or skip re-render when habit/date has not changed**

In `showHabitDetail()`, compare the `id` and today's date against a simple cache variable. If the same habit is being shown and no completion has been toggled since the last render, skip `renderHeatmapHtml()` and reuse the existing DOM content.

---

### Phase 3 — Structural Improvements (estimated: 6–8 hours)

**Fix 9: Move streak calculations to a dedicated database query with eager loading**

Load all completions for all habits in a single query at the start of streak calculation (already done in `StateController` for the completions map, but not used by `calculateStreakData()`). Pass the pre-loaded completions array into streak calculation methods. This eliminates all per-habit DB queries from streak logic entirely.

**Fix 10: Add a `completed_date` index to `habit_completions`**

```php
$table->index('completed_date');
```

Speeds up `StateController`'s `WHERE completed_date >= ?` query as completion history grows.

**Fix 11: Cache `/api/state` response or individual streak computations in Laravel cache**

Use `Cache::remember('state.' . $user->id, 30, fn () => ...)` for the full state payload. Invalidate on any habit or completion change. This makes all subsequent page loads (navigation between screens, background refreshes) essentially free. Most appropriate once the query count is reduced by Fixes 1–6.

---

## Implementation Roadmap

### Phase 1 — Quick wins (2–3 hours, high impact)
- Fix `calculatePhase()` to accept pre-computed streak (eliminates N DB queries)
- Pass pre-computed streak data into `toApiArray()` (eliminates N×2 DB queries)
- Replace `shimmer` animation with compositor-safe alternative
- Add `&display=swap` to Bunny Fonts URL

Expected improvement: `/api/state` drops from 443 ms to ~100–150 ms (7 habits), scales better with more habits.

### Phase 2 — Medium effort (3–4 hours, high impact)
- Refactor `AchievementEvaluator` to use pre-loaded data
- Rewrite `AnalyticsController` with aggregated queries
- Add `habitIdSet` to all stats/growth JS loops

Expected improvement: Toggle response drops from ~100 ms to ~20 ms. Growth screen opens 5–10× faster with many habits.

### Phase 3 — Structural improvements (6–8 hours, future-proofing)
- Full eager loading for streak calculations
- Add `completed_date` index
- Laravel response caching on `/api/state`

Expected improvement: `/api/state` stays under 50 ms regardless of habit or completion count.

---

## What Is Already Good

- JS bundle size (81 KB) is well within the Xiaomi 14c's capability. Not a bottleneck.
- CSS bundle (18 KB) is minimal.
- The composite unique index on `(habit_id, completed_date)` is present and correct.
- Optimistic updates are correctly implemented — the UI never waits for the network.
- Event delegation is used for habit list clicks — no per-item listeners accumulating.
- Screen transitions use CSS `opacity` + `transform` which are compositable.
- No `setInterval` running continuously in the background.
- The `onboarding.js` module correctly calls `removeEventListener` in its cleanup function.
- `localStorage` sync is synchronous only at save time (not on reads), which is acceptable for payloads of this size.

---

## Files Relevant to Each Fix

| Fix | Files |
|---|---|
| Fix 1 (double streak queries) | `app/Http/Controllers/Api/StateController.php`, `app/Models/Habit.php` |
| Fix 2 (calculatePhase redundancy) | `app/Models/Habit.php` |
| Fix 3 (shimmer animation) | `resources/views/welcome.blade.php` (CSS section ~line 85) |
| Fix 4 (font-display) | `resources/views/welcome.blade.php` (head ~line 10) |
| Fix 5 (AchievementEvaluator) | `app/Services/AchievementEvaluator.php`, `app/Http/Controllers/Api/CompletionController.php` |
| Fix 6 (AnalyticsController) | `app/Http/Controllers/Api/AnalyticsController.php` |
| Fix 7 (habitIdSet) | `resources/js/screens/stats.js`, `resources/js/screens/growth.js` |
| Fix 8 (heatmap cache) | `resources/js/screens/habitDetail.js` |
| Fix 9 (eager load streaks) | `app/Models/Habit.php` |
| Fix 10 (date index) | New migration |
| Fix 11 (response cache) | `app/Http/Controllers/Api/StateController.php` |

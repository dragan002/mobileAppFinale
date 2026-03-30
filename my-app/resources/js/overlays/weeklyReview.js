/**
 * AtomicMe — Weekly Review Overlay Component
 *
 * Encapsulates all logic for the `#weekly-review-overlay`:
 *   - Determining whether the review should show (Sunday or 7+ days since last)
 *   - Building the habit completion summary for the current week
 *   - Opening, skipping, and saving the weekly reflection
 *
 * localStorage keys:
 *   - `atomicme_last_reviewed_week` — ISO date string for the last reviewed week start
 */

// ─────────────────────────────────────────────
//  Week helpers
// ─────────────────────────────────────────────

/**
 * Return the ISO date string for the most recent Monday (start of current week).
 *
 * @returns {string}  ISO date string e.g. `"2026-03-30"`.
 */
export function currentWeekOf() {
    const d   = new Date();
    const day = d.getDay(); // 0 = Sunday
    const diff = (day === 0) ? -6 : 1 - day;
    d.setDate(d.getDate() + diff);
    return d.toISOString().slice(0, 10);
}

// ─────────────────────────────────────────────
//  Persistence helpers
// ─────────────────────────────────────────────

/**
 * Load the last reviewed week ISO date from localStorage.
 *
 * @returns {string|null}
 */
export function loadLastReviewedWeek() {
    try {
        return localStorage.getItem('atomicme_last_reviewed_week') || null;
    } catch (e) {
        return null;
    }
}

/**
 * Persist the reviewed week to localStorage.
 *
 * @param {string} weekOf  ISO date string for the week start.
 * @returns {void}
 */
export function saveLastReviewedWeek(weekOf) {
    try {
        localStorage.setItem('atomicme_last_reviewed_week', weekOf);
    } catch (e) { /* silently ignore quota errors */ }
}

// ─────────────────────────────────────────────
//  Visibility check
// ─────────────────────────────────────────────

/**
 * Determine whether the weekly review overlay should be shown.
 * Conditions:
 *   - User exists and has at least one habit
 *   - This week has not already been reviewed/skipped
 *   - Today is Sunday (dayOfWeek === 0) OR 7+ days have passed since last review
 *
 * @param {Object} state  Global application state.
 * @returns {boolean}
 */
export function shouldShowWeeklyReview(state) {
    if (!state.user || state.habits.length === 0) { return false; }

    const thisWeek    = currentWeekOf();
    const lastReviewed = loadLastReviewedWeek();

    if (lastReviewed === thisWeek) { return false; }

    const now        = new Date();
    const dayOfWeek  = now.getDay();

    if (dayOfWeek === 0) { return true; }

    if (lastReviewed) {
        const lastDate  = new Date(lastReviewed);
        const daysSince = Math.floor((now - lastDate) / 86400000);
        if (daysSince >= 7) { return true; }
    }

    return false;
}

// ─────────────────────────────────────────────
//  Habit list HTML
// ─────────────────────────────────────────────

/**
 * Build the habit completion summary rows HTML for the current week.
 *
 * @param {Object} state  Global application state.
 * @returns {string} HTML string of .wr-habit-row divs.
 */
export function renderWeeklyHabitSummaryHtml(state) {
    const weekOf    = currentWeekOf();
    const weekStart = new Date(weekOf);
    const days      = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        days.push(d.toISOString().slice(0, 10));
    }

    return state.habits.map(habit => {
        const doneCount = days.filter(d =>
            (state.completions[d] || []).some(id => String(id) === String(habit.id))
        ).length;
        const pct      = Math.round((doneCount / 7) * 100);
        const pctClass = pct >= 71 ? 'good' : pct >= 43 ? 'ok' : 'low';
        return `<div class="wr-habit-row">
            <div class="wr-habit-left"><span>${habit.emoji}</span><span>${habit.name}</span></div>
            <div class="wr-habit-pct ${pctClass}">${doneCount}/7 days</div>
        </div>`;
    }).join('');
}

// ─────────────────────────────────────────────
//  Open / close / skip / save
// ─────────────────────────────────────────────

/**
 * Open the weekly review overlay and populate the habit summary.
 *
 * @param {Object} state  Global application state.
 * @returns {void}
 */
export function openWeeklyReview(state) {
    if (!state.user) { return; }

    const listEl = document.getElementById('wr-habit-list');
    if (listEl) { listEl.innerHTML = renderWeeklyHabitSummaryHtml(state); }

    const noteEl = document.getElementById('wr-note');
    if (noteEl) { noteEl.value = ''; }

    document.getElementById('weekly-review-overlay')?.classList.add('show');
}

/**
 * Close the weekly review overlay without saving.
 *
 * @returns {void}
 */
export function closeWeeklyReview() {
    document.getElementById('weekly-review-overlay')?.classList.remove('show');
}

/**
 * Skip the weekly review: mark this week as reviewed and close.
 *
 * @returns {void}
 */
export function skipWeeklyReview() {
    saveLastReviewedWeek(currentWeekOf());
    closeWeeklyReview();
}

/**
 * Read the reflection note from the textarea.
 *
 * @returns {string}
 */
export function readReviewNote() {
    return (document.getElementById('wr-note')?.value || '').trim();
}

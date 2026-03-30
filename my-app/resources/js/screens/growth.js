/**
 * AtomicMe — Growth Screen Component
 *
 * Encapsulates all rendering logic for `#screen-growth`:
 *   - Consistency scores (daily / weekly / monthly / all-time)
 *   - Week-vs-week and month-vs-month bar charts (from /api/analytics)
 *   - Per-habit consistency bars
 *
 * Exported functions mirror the structure of `renderGrowth()` in welcome.blade.php
 * so they can be composed by the blade file or called independently.
 */

// ─────────────────────────────────────────────
//  Private helpers
// ─────────────────────────────────────────────

/**
 * @returns {string} Today's ISO date string.
 */
function todayKey() {
    return new Date().toISOString().slice(0, 10);
}

// ─────────────────────────────────────────────
//  Consistency score calculations
// ─────────────────────────────────────────────

/**
 * Calculate the daily completion percentage for today.
 *
 * @param {Object} state  Global application state.
 * @returns {number}  Integer 0–100.
 */
export function calcDailyScore(state) {
    const dateKey          = todayKey();
    const completedTodayIds = (state.completions[dateKey] || []).map(String);
    const total             = state.habits.length;
    const done              = completedTodayIds.filter(id => state.habits.some(h => String(h.id) === id)).length;
    return total ? Math.round((done / total) * 100) : 0;
}

/**
 * Calculate the weekly completion percentage (Sun–Sat of current week).
 *
 * @param {Object} state  Global application state.
 * @returns {number}  Integer 0–100.
 */
export function calcWeeklyScore(state) {
    const total     = state.habits.length;
    if (!total) { return 0; }

    const weekStart = new Date();
    weekStart.setDate(weekStart.getDate() - weekStart.getDay());
    weekStart.setHours(0, 0, 0, 0);

    let completions = 0;
    let possible    = 0;

    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        const key  = d.toISOString().slice(0, 10);
        const done = (state.completions[key] || [])
            .filter(id => state.habits.some(h => String(h.id) === String(id))).length;
        completions += done;
        possible    += total;
    }

    return possible ? Math.round((completions / possible) * 100) : 0;
}

/**
 * Calculate the monthly completion percentage (1st of month to today).
 *
 * @param {Object} state  Global application state.
 * @returns {number}  Integer 0–100.
 */
export function calcMonthlyScore(state) {
    const total = state.habits.length;
    if (!total) { return 0; }

    const monthStart  = new Date();
    monthStart.setDate(1);
    const todayDate   = new Date().getDate();
    let completions   = 0;
    let possible      = 0;

    for (let i = 0; i < todayDate; i++) {
        const d = new Date(monthStart);
        d.setDate(monthStart.getDate() + i);
        const key  = d.toISOString().slice(0, 10);
        const done = (state.completions[key] || [])
            .filter(id => state.habits.some(h => String(h.id) === String(id))).length;
        completions += done;
        possible    += total;
    }

    return possible ? Math.round((completions / possible) * 100) : 0;
}

/**
 * Calculate the all-time completion percentage across all recorded dates.
 *
 * @param {Object} state  Global application state.
 * @returns {number}  Integer 0–100.
 */
export function calcAllTimeScore(state) {
    const total = state.habits.length;
    if (!total) { return 0; }

    let completions = 0;
    let possible    = 0;

    Object.keys(state.completions).forEach(date => {
        const done = (state.completions[date] || [])
            .filter(id => state.habits.some(h => String(h.id) === String(id))).length;
        completions += done;
        possible    += total;
    });

    return possible ? Math.round((completions / possible) * 100) : 0;
}

// ─────────────────────────────────────────────
//  Analytics charts HTML (week-vs-week, month-vs-month)
// ─────────────────────────────────────────────

/**
 * Build the HTML for a generic rate bar chart from an array of rates.
 *
 * @param {number[]} rates     Array of percentage values (0–100).
 * @returns {string} HTML string of .compound-bar divs.
 */
export function renderRateChartHtml(rates) {
    const maxRate = Math.max(...rates, 1);
    return rates.map(rate => {
        const height = maxRate > 0 ? Math.max(4, (rate / maxRate) * 100) : 4;
        return `<div class="compound-bar" style="height:${height}%"></div>`;
    }).join('');
}

// ─────────────────────────────────────────────
//  Per-habit consistency HTML
// ─────────────────────────────────────────────

/**
 * Build the HTML for the per-habit consistency list.
 *
 * @param {Object} state  Global application state.
 * @returns {string} HTML string.
 */
export function renderHabitConsistencyHtml(state) {
    if (state.habits.length === 0) {
        return '<p style="color:#5A6180;font-size:.8rem;padding:.5rem 0;">Add habits to track consistency.</p>';
    }

    return state.habits.map(h => {
        const allCompletions = Object.values(state.completions)
            .filter(arr => arr.map(String).includes(String(h.id))).length;

        const createdDate  = new Date(h.createdAt || todayKey());
        const todayDate    = new Date(todayKey());
        const msPerDay     = 1000 * 60 * 60 * 24;
        const daysSinceCreated = Math.max(1, Math.round((todayDate - createdDate) / msPerDay) + 1);

        const habitRate = Math.min(100, Math.round((allCompletions / daysSinceCreated) * 100));

        return `<div class="identity-item">
            <div class="identity-icon">${h.emoji}</div>
            <div class="identity-info">
                <div class="identity-name">${h.name}</div>
                <div class="identity-votes">${habitRate}% consistency</div>
                <div class="identity-bar"><div class="identity-bar-fill" style="width:${habitRate}%"></div></div>
            </div>
        </div>`;
    }).join('');
}

// ─────────────────────────────────────────────
//  Main update function
// ─────────────────────────────────────────────

/**
 * Update all DOM elements in #screen-growth from the provided state.
 * Mirrors `renderGrowth()` in welcome.blade.php but is broken into
 * smaller, testable pieces.
 *
 * The analytics fetch (week/month charts) is handled separately so this
 * function can be called synchronously. Pass `analyticsData` if available,
 * or null to skip those charts.
 *
 * @param {Object}      state          Global application state.
 * @param {Object|null} analyticsData  { weeklyRates, monthlyRates } from /api/analytics or null.
 * @returns {void}
 */
export function updateGrowthScreen(state, analyticsData) {
    if (!state.user) { return; }

    const csDaily   = calcDailyScore(state);
    const csWeekly  = calcWeeklyScore(state);
    const csMonthly = calcMonthlyScore(state);
    const csAllTime = calcAllTimeScore(state);

    const set = (id, val) => { const el = document.getElementById(id); if (el) { el.textContent = val; } };
    const setHtml = (id, val) => { const el = document.getElementById(id); if (el) { el.innerHTML = val; } };

    set('cs-daily',   csDaily   + '%');
    set('cs-weekly',  csWeekly  + '%');
    set('cs-monthly', csMonthly + '%');
    set('cs-alltime', csAllTime + '%');

    // Analytics charts
    if (analyticsData) {
        const weeklyRates  = analyticsData.weeklyRates  || [];
        const monthlyRates = analyticsData.monthlyRates || [];

        setHtml('growth-weekly-chart',  renderRateChartHtml(weeklyRates));
        setHtml('growth-monthly-chart', renderRateChartHtml(monthlyRates));

        const weekLabelsEl = document.getElementById('growth-weekly-labels');
        if (weekLabelsEl) {
            weekLabelsEl.innerHTML = ['4w ago', '3w ago', '2w ago', 'Last w', 'This w']
                .map(l => `<span>${l}</span>`).join('');
        }
    }

    // Per-habit consistency
    setHtml('growth-habits-list', renderHabitConsistencyHtml(state));
}

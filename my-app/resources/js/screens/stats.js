/**
 * AtomicMe — Stats Screen Component
 *
 * Encapsulates all rendering logic for `#screen-stats`: top stats row,
 * compound growth chart, weekly grid, per-habit breakdown, and identity votes panel.
 *
 * This module reads the global `state` object defined in welcome.blade.php.
 * All functions are pure: given state, they produce DOM mutations or HTML strings
 * with no side effects beyond the DOM.
 */

// ─────────────────────────────────────────────
//  Constants
// ─────────────────────────────────────────────

const IDENTITY_MAP = {
    athlete: { label: 'The Athlete',  icon: '🏃' },
    learner: { label: 'The Learner',  icon: '📚' },
    creator: { label: 'The Creator',  icon: '🎨' },
    mindful: { label: 'The Mindful',  icon: '🧘' },
    leader:  { label: 'The Leader',   icon: '🚀' },
    healthy: { label: 'The Healthy',  icon: '🥗' },
};

// ─────────────────────────────────────────────
//  Private helpers
// ─────────────────────────────────────────────

/**
 * @returns {string} Today's ISO date string.
 */
function todayKey() {
    return new Date().toISOString().slice(0, 10);
}

/**
 * @param {number} streak
 * @returns {string}
 */
function getStreakEmoji(streak) {
    if (streak >= 100) { return '💥🔥💥'; }
    if (streak >= 60)  { return '⚡🔥⚡'; }
    if (streak >= 30)  { return '🔥🔥🔥'; }
    if (streak >= 14)  { return '🔥🔥'; }
    return '🔥';
}

/**
 * Calculate the completion rate for a habit over the last `days` days (0–100).
 *
 * @param {Object}       state   Global state.
 * @param {string|number} id     Habit ID.
 * @param {number}        days   Number of days to look back.
 * @returns {number}  Integer percentage 0–100.
 */
function calcCompletionRate(state, id, days) {
    let count = 0;
    for (let i = 0; i < days; i++) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        const key = d.toISOString().slice(0, 10);
        if ((state.completions[key] || []).map(String).includes(String(id))) { count++; }
    }
    return Math.round((count / days) * 100);
}

/**
 * Calculate the overall "any habit" streak: consecutive days on which at least
 * one habit was completed, walking backwards from today.
 *
 * @param {Object} state  Global application state.
 * @returns {number}
 */
function calcOverallStreak(state) {
    let streak = 0;
    const d = new Date();
    const todayStr = todayKey();
    while (true) {
        const key  = d.toISOString().slice(0, 10);
        const comp = (state.completions[key] || []).filter(id =>
            state.habits.some(h => String(h.id) === String(id))
        );
        if (comp.length > 0) {
            streak++;
            d.setDate(d.getDate() - 1);
        } else if (key === todayStr) {
            break;
        } else {
            break;
        }
    }
    return streak;
}

// ─────────────────────────────────────────────
//  Compound growth section HTML
// ─────────────────────────────────────────────

/**
 * Derive the best streak across all habits in state.
 *
 * @param {Object} state  Global application state.
 * @returns {number}  Best streak in days.
 */
function getBestStreak(state) {
    if (!state.bestStreaks) { return 0; }
    return Math.max(0, ...Object.values(state.bestStreaks).map(Number));
}

/**
 * Build the HTML for the full compound growth section: intro copy, bar chart
 * with multiplier labels and a "you are here" marker, axis labels, and a
 * personalised footer line.
 *
 * Delegates bar rendering to `chart.js` via the global `App.components.chart`
 * namespace to avoid duplicating the function.
 *
 * @param {Object} state  Global application state.
 * @returns {string}  HTML string for the compound-chart container and surrounding copy.
 */
export function renderCompoundSectionHtml(state) {
    const bestDay = getBestStreak(state);

    // Segment boundaries that match the chart bars: [0,30), [30,90), [90,180), [180,365), [365+)
    const segments = [0, 30, 90, 180, 365];
    let activeSegmentIndex = 0;
    for (let i = segments.length - 1; i >= 0; i--) {
        if (bestDay >= segments[i]) { activeSegmentIndex = i; break; }
    }

    const multipliers = ['1x', '1.3x', '2.5x', '6x', '37x'];
    const axisLabels  = ['Today', '1 month', '3 months', '6 months', '1 year'];
    const maxVal      = Math.pow(1.01, 365);

    const bars = segments.map((day, i) => {
        const val    = day === 0 ? 1 : Math.pow(1.01, day);
        const height = Math.max(4, (val / maxVal) * 100);
        const isActive = i === activeSegmentIndex;

        return `<div class="compound-bar-wrap">
            <div class="compound-bar-multiplier${isActive ? ' compound-bar-multiplier--active' : ''}">${multipliers[i]}</div>
            ${isActive ? '<div class="compound-bar-you">YOU</div>' : ''}
            <div class="compound-bar${isActive ? ' compound-bar--active' : ''}" style="height:${height}%"></div>
        </div>`;
    }).join('');

    const axisHtml = axisLabels.map(l => `<span>${l}</span>`).join('');

    // Personalised footer: tell the user where they are and what's next
    let footerText;
    if (bestDay === 0) {
        footerText = 'Every habit you complete today is your first 1%. Start the curve.';
    } else if (bestDay < 30) {
        footerText = `Your best streak is <strong>${bestDay} day${bestDay !== 1 ? 's' : ''}</strong>. Hit 30 days and you'll already be 1.3x better.`;
    } else if (bestDay < 90) {
        footerText = `Your best streak is <strong>${bestDay} days</strong>. Keep going to 90 days and you reach 2.5x.`;
    } else if (bestDay < 180) {
        footerText = `<strong>${bestDay} days</strong> — you're at 2.5x. Six months in and you'll be 6x better.`;
    } else if (bestDay < 365) {
        footerText = `<strong>${bestDay} days</strong> — you're at 6x. One year of 1% daily = 37x. You're almost there.`;
    } else {
        footerText = `<strong>${bestDay} days</strong> — you've reached 37x. You are the 1% club.`;
    }

    return `<div class="compound-chart compound-chart--labeled" id="compound-chart">${bars}</div>
        <div class="chart-labels">${axisHtml}</div>
        <p class="compound-footer">${footerText}</p>`;
}

// ─────────────────────────────────────────────
//  Weekly grid HTML
// ─────────────────────────────────────────────

/**
 * Build the HTML for the weekly completion grid.
 *
 * @param {Object} state          Global application state.
 * @param {Array}  activeHabits   Current habits list.
 * @returns {string} HTML string.
 */
export function renderWeeklyGridHtml(state, activeHabits) {
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const total    = activeHabits.length;
    const now      = new Date();

    return dayNames.map((label, i) => {
        const d = new Date(now);
        d.setDate(now.getDate() - now.getDay() + i);
        const key  = d.toISOString().slice(0, 10);
        const comp = (state.completions[key] || []).filter(id =>
            activeHabits.some(h => String(h.id) === String(id))
        ).length;
        const cls = total > 0 && comp >= total ? 'done' : comp > 0 ? 'partial' : '';
        return `<div class="week-day"><div class="week-day-label">${label}</div><div class="week-dot ${cls}"></div></div>`;
    }).join('');
}

// ─────────────────────────────────────────────
//  Per-habit breakdown HTML
// ─────────────────────────────────────────────

/**
 * Build the HTML for the per-habit streak and rate breakdown panel.
 *
 * @param {Object} state          Global application state.
 * @param {Array}  activeHabits   Current habits list.
 * @returns {string} HTML string.
 */
export function renderHabitBreakdownHtml(state, activeHabits) {
    return activeHabits.map(h => {
        const streak  = state.streaks[h.id] || 0;
        const sd      = state.streakData[h.id] || { value: streak, unit: 'days' };
        const rate30  = calcCompletionRate(state, h.id, 30);
        const streakText = sd.value > 0
            ? (sd.unit === 'weeks'
                ? `📅 ${sd.value} week streak · `
                : `${getStreakEmoji(sd.value)} ${sd.value} day streak · `)
            : '';
        return `<div class="identity-item" data-action="show-detail" data-habit-id="${h.id}">
            <div class="identity-icon">${h.emoji}</div>
            <div class="identity-info">
                <div class="identity-name">${h.name}</div>
                <div class="identity-votes">${streakText}${rate30}% this month</div>
                <div class="identity-bar"><div class="identity-bar-fill" style="width:${rate30}%"></div></div>
            </div>
        </div>`;
    }).join('');
}

// ─────────────────────────────────────────────
//  Identity votes HTML
// ─────────────────────────────────────────────

/**
 * Build the HTML for the identity votes panel.
 *
 * @param {Object} state          Global application state.
 * @param {Array}  activeHabits   Current habits list.
 * @returns {string} HTML string.
 */
export function renderIdentityVotesHtml(state, activeHabits) {
    if (activeHabits.length === 0) {
        return '<p style="color:#5A6180;font-size:.8rem;padding:.5rem 0;">Add habits to track identity votes.</p>';
    }

    const u          = state.user;
    let voteCount    = 0;
    Object.values(state.completions).forEach(arr => {
        voteCount += arr.filter(id => activeHabits.some(h => String(h.id) === String(id))).length;
    });

    const identityData = IDENTITY_MAP[u.identity] || { label: u.identityLabel, icon: u.identityIcon };
    const barWidth     = Math.min(100, voteCount);

    return `<div class="identity-item" style="cursor:default;">
        <div class="identity-icon">${identityData.icon}</div>
        <div class="identity-info">
            <div class="identity-name">${identityData.label}</div>
            <div class="identity-votes">${voteCount} vote${voteCount !== 1 ? 's' : ''} cast for your identity</div>
            <div class="identity-bar"><div class="identity-bar-fill" style="width:${barWidth}%"></div></div>
        </div>
    </div>`;
}

// ─────────────────────────────────────────────
//  Main render — updates the stats screen DOM in place
// ─────────────────────────────────────────────

/**
 * Fully update all DOM elements in #screen-stats from the provided state.
 * Mirrors the `renderStats()` function in welcome.blade.php.
 * Exported so welcome.blade.php can delegate to this module when ready.
 *
 * @param {Object} state  Global application state.
 * @returns {void}
 */
export function updateStatsScreen(state) {
    if (!state.user) { return; }

    const dateKey      = todayKey();
    const activeHabits = state.habits;
    const total        = activeHabits.length;
    const todayDone    = (state.completions[dateKey] || [])
        .filter(id => activeHabits.some(h => String(h.id) === String(id))).length;
    const rate         = total ? Math.round((todayDone / total) * 100) : 0;
    const overallStreak = calcOverallStreak(state);

    let totalAllTime = 0;
    Object.values(state.completions).forEach(arr => {
        totalAllTime += arr.filter(id => activeHabits.some(h => String(h.id) === String(id))).length;
    });

    const streakEl = document.getElementById('stat-streak');
    const totalEl  = document.getElementById('stat-total');
    const rateEl   = document.getElementById('stat-rate');

    if (streakEl) { streakEl.textContent = overallStreak; }
    if (totalEl)  { totalEl.textContent  = totalAllTime; }
    if (rateEl)   { rateEl.textContent   = rate + '%'; }

    // Compound growth section (bars + axis labels + personalised footer)
    const compoundSectionEl = document.getElementById('compound-section');
    if (compoundSectionEl) { compoundSectionEl.innerHTML = renderCompoundSectionHtml(state); }

    // Weekly grid
    const weekGridEl = document.getElementById('weekly-grid');
    if (weekGridEl) { weekGridEl.innerHTML = renderWeeklyGridHtml(state, activeHabits); }

    // Per-habit breakdown
    const breakdownCardEl = document.getElementById('habit-breakdown-card');
    const breakdownEl     = document.getElementById('habit-breakdown');
    if (breakdownCardEl && breakdownEl) {
        if (activeHabits.length > 0) {
            breakdownCardEl.style.display = 'block';
            breakdownEl.innerHTML = renderHabitBreakdownHtml(state, activeHabits);
        } else {
            breakdownCardEl.style.display = 'none';
        }
    }

    // Identity votes
    const votesListEl = document.getElementById('identity-votes-list');
    if (votesListEl) {
        votesListEl.innerHTML = renderIdentityVotesHtml(state, activeHabits);
    }
}

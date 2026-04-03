/**
 * AtomicMe — Home Screen Component
 *
 * Encapsulates all rendering logic for `#screen-home`: greeting, progress card,
 * daily quote, and the habits list (including empty state).
 *
 * This module does NOT own `state` — it reads the global `state` object that
 * welcome.blade.php defines. It exports a single `render(state)` function and
 * an `attachListeners(state, callbacks)` function for wiring up event handlers.
 *
 * Design: pure render functions return HTML strings; attachListeners() wires
 * event delegation so cleanup is a single removeEventListener call.
 */

// ─────────────────────────────────────────────
//  Constants (duplicated here so the module is self-contained when imported)
// ─────────────────────────────────────────────

const IDENTITY_MAP = {
    athlete: { label: 'The Athlete',  icon: '🏃' },
    learner: { label: 'The Learner',  icon: '📚' },
    creator: { label: 'The Creator',  icon: '🎨' },
    mindful: { label: 'The Mindful',  icon: '🧘' },
    leader:  { label: 'The Leader',   icon: '🚀' },
    healthy: { label: 'The Healthy',  icon: '🥗' },
};

const QUOTES = [
    "You do not rise to the level of your goals. You fall to the level of your systems.",
    "Every action you take is a vote for the type of person you wish to become.",
    "Habits are the compound interest of self-improvement.",
    "The most effective form of motivation is progress.",
    "Small changes often appear to make no difference until you cross a critical threshold.",
    "Success is the product of daily habits—not once-in-a-lifetime transformations.",
    "The purpose of setting goals is to win the game. The purpose of building systems is to continue playing.",
    "You don't have to be the victim of your environment. You can also be the architect of it.",
    "Be the designer of your world and not merely the consumer of it.",
    "Until you make the unconscious conscious, it will direct your life and you will call it fate.",
];

/**
 * Identity-specific motivational prompts.
 * Each entry is a short reinforcing message for someone building that identity.
 */
const IDENTITY_PROMPTS = {
    athlete:  [
        "Athletes don't decide whether to train — the decision is already made.",
        "Your body keeps score. Show up and earn the points.",
        "Every rep is a vote: I am someone who moves.",
        "Champions aren't built on motivation. They're built on showing up.",
        "Physical strength starts before you lift anything — it starts here.",
    ],
    learner:  [
        "Learners don't wait to feel curious — they read first, then feel it.",
        "Every page is a vote: I am someone who grows.",
        "The best investment you'll ever make is in your own mind.",
        "Knowledge compounds. Show up today and add to your edge.",
        "Curiosity is a skill. You sharpen it every time you open a book.",
    ],
    creator:  [
        "Creators don't wait for inspiration — they show up and inspiration follows.",
        "Every brushstroke, every word, every line is a vote: I am a maker.",
        "Your creative identity is built in the daily act of making something.",
        "The work doesn't have to be perfect today. It has to exist.",
        "Creativity is a habit, not a talent.",
    ],
    mindful:  [
        "Presence is a practice. Every breath is a rep.",
        "Every moment you return to stillness is a vote: I am someone who is calm.",
        "You can't control the weather. You can control how you meet it.",
        "Mindfulness isn't a retreat from life — it's how you fully enter it.",
        "Peace isn't found. It's practiced.",
    ],
    leader:   [
        "Leaders act before they feel ready — that's what makes them leaders.",
        "Every decision with integrity is a vote: I am someone who can be trusted.",
        "Your habits are your reputation. Build it deliberately.",
        "The person others follow is the person who shows up first.",
        "Influence starts with what you do when no one is watching.",
    ],
    healthy:  [
        "Health is built in the ordinary moments, not the extraordinary ones.",
        "Every nourishing choice is a vote: I am someone who takes care of myself.",
        "Your future self is shaped by what you do today — not someday.",
        "Wellness isn't a destination. It's a daily practice.",
        "The body you want is built one habit at a time.",
    ],
};

// ─────────────────────────────────────────────
//  Private helpers
// ─────────────────────────────────────────────

/**
 * @param {string} s
 * @returns {string}
 */
function capitalize(s) {
    return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
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
 * @returns {string} Today's ISO date string.
 */
function todayKey() {
    return new Date().toISOString().slice(0, 10);
}

// ─────────────────────────────────────────────
//  render()
// ─────────────────────────────────────────────

/**
 * Render the full #screen-home HTML string from the provided state snapshot.
 * The returned string replaces the inner content of #screen-home — the outer
 * wrapper div with id="screen-home" remains in the static blade template.
 *
 * Not used in the current integration (welcome.blade.php mutates DOM directly);
 * provided for future full-SPA migration.
 *
 * @param {Object} state  Global application state snapshot.
 * @returns {string}      HTML string for the screen body.
 */
export function render(state) {
    if (!state.user) { return ''; }

    const u               = state.user;
    const dateKey         = todayKey();
    const completedIds    = (state.completions[dateKey] || []).map(String);
    const activeHabits    = state.habits;
    const total           = activeHabits.length;
    const done            = completedIds.filter(id => activeHabits.some(h => String(h.id) === id)).length;
    const pct             = total ? Math.round((done / total) * 100) : 0;
    const hour            = new Date().getHours();
    const greet           = hour < 12 ? 'Good morning' : hour < 17 ? 'Good afternoon' : 'Good evening';
    const identityData    = IDENTITY_MAP[u.identity] || { label: u.identityLabel, icon: u.identityIcon };
    const allDone         = done === total && total > 0;

    let sub = '';
    if (total === 0)        { sub = 'Add your first habit to get started.'; }
    else if (done === 0)    { sub = `${total} habit${total > 1 ? 's' : ''} waiting for you.`; }
    else if (allDone)       { sub = 'All done! Perfect day. Keep the chain going! 🔥'; }
    else                    { sub = `${total - done} habit${(total - done) > 1 ? 's' : ''} to go — you can do this!`; }

    const message      = pickDailyMessage(state);
    const quote        = identityData
        ? `<span class="daily-quote-label">${identityData.icon} ${identityData.label}</span>${message}`
        : `"${message}"`;
    const initials = u.name[0].toUpperCase();

    const habitsHtml = renderHabitsList(state, activeHabits, completedIds);

    return `
        <div class="app-header">
            <div class="header-greeting">
                <h2 id="home-greeting">${greet}, ${u.name} 👋</h2>
                <p id="home-date">${new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' })}</p>
            </div>
            <div class="avatar" id="home-avatar" data-action="open-profile">${initials}</div>
        </div>

        <div class="progress-card${allDone ? ' all-done' : ''}">
            <div class="progress-label">Today's Progress</div>
            <div class="progress-numbers">
                <span id="done-count">${done}</span>
                <span>/ <span id="total-count">${total}</span> habits</span>
            </div>
            <div class="progress-bar-wrap">
                <div class="progress-bar-fill" id="progress-bar" style="width:${pct}%"></div>
            </div>
            <div class="progress-sub" id="progress-sub">${sub}</div>
            <div class="identity-badge" id="identity-badge">${identityData.icon} Becoming ${identityData.label}</div>
        </div>

        <div class="daily-quote" id="daily-quote">${quote}</div>

        <div class="section-header">
            <span class="section-title">Today's Habits</span>
            <span class="section-action" data-action="go-add">+ Add</span>
        </div>

        ${habitsHtml}

        <button class="add-habit-btn" data-action="go-add">
            <span style="font-size:1.1rem">+</span> Add New Habit
        </button>

        <div class="reminder-permission-banner" id="home-reminder-permission-banner" style="margin-top:0.75rem;">
            <div class="reminder-permission-banner-text">
                <strong>Enable reminders</strong>
                Never miss a habit — allow daily notifications.
            </div>
            <button class="btn-allow-notifs" data-action="request-notifications">Allow</button>
        </div>

        <nav class="bottom-nav">
            <div class="nav-item active" data-tab="screen-home"><span class="nav-icon">🏠</span><span>Today</span></div>
            <div class="nav-item" data-tab="screen-stats"><span class="nav-icon">📊</span><span>Stats</span></div>
            <div class="nav-item" data-tab="screen-growth"><span class="nav-icon">📈</span><span>Growth</span></div>
            <div class="nav-item" data-tab="screen-achievements"><span class="nav-icon">🏅</span><span>Badges</span></div>
        </nav>
    `;
}

/**
 * Build the habits list HTML (or empty state HTML) for the given habits array.
 *
 * @param {Object}   state           Global state.
 * @param {Array}    activeHabits    Current habits list.
 * @param {string[]} completedIds    Habit IDs completed today (as strings).
 * @returns {string} HTML string.
 */
export function renderHabitsList(state, activeHabits, completedIds) {
    if (activeHabits.length === 0) {
        return `
            <div id="empty-state" class="empty-state" style="display:flex;">
                <div style="font-size: 4rem; margin-bottom: 1.5rem; text-align: center;">🌱✨</div>
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #EAEDF6; margin-bottom: 0.5rem; text-align: center;">
                    Your first vote for becoming ${state.user ? (state.user.identity || 'your best self') : 'your best self'}
                </h3>
                <p style="font-size: 0.85rem; color: #8B92AB; margin-bottom: 1.5rem; line-height: 1.5; text-align: center;">
                    Start with one tiny habit. Two minutes or less.
                </p>
                <button data-action="go-add" class="btn-primary" style="width: 100%; max-width: 280px; margin: 0 auto; display: block;">
                    Add Your First Habit
                </button>
            </div>
            <div id="habits-list" class="habits-list" style="display:none;"></div>`;
    }

    const yesterdayKey = new Date(Date.now() - 86400000).toISOString().slice(0, 10);

    const itemsHtml = activeHabits.map(h => renderHabitItem(state, h, completedIds, yesterdayKey)).join('');

    return `
        <div id="empty-state" class="empty-state" style="display:none;"></div>
        <div id="habits-list" class="habits-list">${itemsHtml}</div>`;
}

/**
 * Build a single habit list item HTML string.
 *
 * @param {Object}   state          Global state.
 * @param {Object}   habit          Habit object.
 * @param {string[]} completedIds   Habit IDs completed today (as strings).
 * @param {string}   yesterdayKey   ISO date string for yesterday.
 * @returns {string} HTML string.
 */
export function renderHabitItem(state, habit, completedIds, yesterdayKey) {
    const h           = habit;
    const isDone      = completedIds.includes(String(h.id));
    const streak      = state.streaks[h.id] || 0;
    const sd          = (state.streakData || {})[h.id] || { value: streak, unit: 'days', graceDayActive: false };
    const isFrequency = (h.targetDaysPerWeek || 7) < 7;
    const isAtRisk    = streak >= 3 && !isDone && !isFrequency;
    const isGraceDay  = sd.graceDayActive;
    const streakClass = isGraceDay ? 'grace-day-text' : (isAtRisk ? 'at-risk-text' : '');

    let streakHtml = '';
    if (isFrequency) {
        const weekStart = (() => {
            const d = new Date();
            d.setDate(d.getDate() - d.getDay());
            d.setHours(0, 0, 0, 0);
            return d;
        })();
        const weekDone = Object.entries(state.completions)
            .filter(([date]) => new Date(date) >= weekStart)
            .reduce((count, [, ids]) => count + (ids.map(String).includes(String(h.id)) ? 1 : 0), 0);
        const target    = h.targetDaysPerWeek || 7;
        const weekMet   = weekDone >= target;
        const weekSuffix = weekMet ? ' ✓' : '';
        const freqClass  = isGraceDay ? 'grace-day-text' : '';
        const freqStreakPart = sd.value > 0 ? ` · ${sd.value}w streak` : '';
        streakHtml = `<div class="habit-streak ${freqClass}">${weekDone}/${target} this week${weekSuffix}${freqStreakPart}</div>`;
    } else if (streak > 0) {
        const streakSuffix = isGraceDay
            ? ' · grace day active'
            : (isAtRisk ? ' · ⚠️ at risk!' : '');
        streakHtml = `<div class="habit-streak ${streakClass}">${getStreakEmoji(streak)} ${streak} day streak${streakSuffix}</div>`;
    }

    const isStreakHigh = streak >= 7;
    const habitColor   = h.color || '#a78bfa';
    const freqLabel    = isFrequency ? `${h.targetDaysPerWeek}x/wk · ` : '';

    return `
        <div class="habit-item ${isDone ? 'completed' : ''} ${isAtRisk ? 'at-risk' : ''} ${isStreakHigh ? 'streak-high' : ''}"
             id="item-${h.id}"
             style="${isStreakHigh ? '--habit-color:' + habitColor + ';' : ''}"
             data-action="toggle-habit"
             data-habit-id="${h.id}">
            <div class="habit-icon-wrap"
                 style="background:${h.color}"
                 data-action="show-detail"
                 data-habit-id="${h.id}">${h.emoji}</div>
            <div class="habit-info"
                 data-action="show-detail"
                 data-habit-id="${h.id}">
                <div class="habit-name">${h.name}</div>
                <div class="habit-meta">${freqLabel}${h.duration || (isFrequency ? 'Weekly' : 'Daily')} · ${capitalize(h.time)}</div>
                ${streakHtml}
            </div>
            <div class="habit-check"></div>
        </div>`;
}

// ─────────────────────────────────────────────
//  DOM update helpers (used by welcome.blade.php renderHome())
// ─────────────────────────────────────────────

/**
 * Update the progress card DOM elements in place (no full re-render).
 * Mutates the existing static DOM elements by ID.
 *
 * @param {Object} state  Global application state.
 * @returns {void}
 */
export function updateProgressCard(state) {
    if (!state.user) { return; }

    const u            = state.user;
    const dateKey      = todayKey();
    const completedIds = (state.completions[dateKey] || []).map(String);
    const habits       = state.habits;
    const total        = habits.length;
    const done         = completedIds.filter(id => habits.some(h => String(h.id) === id)).length;
    const pct          = total ? Math.round((done / total) * 100) : 0;

    const doneEl = document.getElementById('done-count');
    const totalEl = document.getElementById('total-count');
    const barEl = document.getElementById('progress-bar');
    const subEl = document.getElementById('progress-sub');
    const badgeEl = document.getElementById('identity-badge');
    const cardEl = document.querySelector('.progress-card');

    if (doneEl)  { doneEl.textContent  = done; }
    if (totalEl) { totalEl.textContent = total; }
    if (barEl)   { barEl.style.width   = pct + '%'; }

    if (cardEl) {
        cardEl.classList.toggle('all-done', done === total && total > 0);
    }

    if (subEl) {
        let sub = '';
        if (total === 0)                { sub = 'Add your first habit to get started.'; }
        else if (done === 0)            { sub = `${total} habit${total > 1 ? 's' : ''} waiting for you.`; }
        else if (done === total)        { sub = 'All done! Perfect day. Keep the chain going! 🔥'; }
        else                            { sub = `${total - done} habit${(total - done) > 1 ? 's' : ''} to go — you can do this!`; }
        subEl.textContent = sub;
    }

    if (badgeEl) {
        const identityData = IDENTITY_MAP[u.identity] || { label: u.identityLabel, icon: u.identityIcon };
        badgeEl.textContent = `${identityData.icon} Becoming ${identityData.label}`;
    }
}

/**
 * Update the greeting and avatar elements in the header.
 *
 * @param {Object} state  Global application state.
 * @returns {void}
 */
export function updateGreeting(state) {
    if (!state.user) { return; }

    const u    = state.user;
    const hour = new Date().getHours();
    const greet = hour < 12 ? 'Good morning' : hour < 17 ? 'Good afternoon' : 'Good evening';

    const greetEl = document.getElementById('home-greeting');
    const dateEl  = document.getElementById('home-date');

    if (greetEl) { greetEl.textContent = `${greet}, ${u.name} 👋`; }
    if (dateEl)  { dateEl.textContent  = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }); }

    // Update all avatar elements that display user initials
    ['home-avatar', 'stats-avatar', 'growth-avatar', 'achievements-avatar'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.textContent = u.name[0].toUpperCase(); }
    });
}

/**
 * Pick the daily message for the home screen.
 * Uses identity-specific prompts when available, falls back to the general quote pool.
 *
 * @param {Object|null} state  Global application state (or null if not yet loaded).
 * @returns {string}  The message text (without surrounding quotes).
 */
export function pickDailyMessage(state) {
    const identity = state && state.user && state.user.identity;
    const pool     = (identity && IDENTITY_PROMPTS[identity]) ? IDENTITY_PROMPTS[identity] : QUOTES;
    return pool[new Date().getDate() % pool.length];
}

/**
 * Update the daily quote/motivation element.
 *
 * @param {Object|null} state  Global application state (or null to use fallback quotes).
 * @returns {void}
 */
export function updateDailyQuote(state) {
    const quoteEl = document.getElementById('daily-quote');
    if (!quoteEl) { return; }

    const identity     = state && state.user && state.user.identity;
    const identityData = identity ? (IDENTITY_MAP[identity] || null) : null;
    const message      = pickDailyMessage(state);

    if (identityData) {
        quoteEl.innerHTML = `
            <span class="daily-quote-label">${identityData.icon} ${identityData.label}</span>
            ${message}
        `;
    } else {
        quoteEl.textContent = `"${message}"`;
    }
}

/**
 * Update the habits list DOM. Replaces the innerHTML of #habits-list and
 * toggles the empty state visibility.
 *
 * @param {Object} state  Global application state.
 * @returns {void}
 */
export function updateHabitsList(state) {
    const listEl  = document.getElementById('habits-list');
    const emptyEl = document.getElementById('empty-state');
    if (!listEl || !emptyEl) { return; }

    const dateKey      = todayKey();
    const completedIds = (state.completions[dateKey] || []).map(String);
    const habits       = state.habits;

    if (habits.length === 0) {
        listEl.innerHTML  = '';
        emptyEl.style.display = 'flex';
        emptyEl.innerHTML = `
            <div style="font-size: 4rem; margin-bottom: 1.5rem; text-align: center;">🌱✨</div>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #EAEDF6; margin-bottom: 0.5rem; text-align: center;">
                Your first vote for becoming ${state.user ? (state.user.identity || 'your best self') : 'your best self'}
            </h3>
            <p style="font-size: 0.85rem; color: #8B92AB; margin-bottom: 1.5rem; line-height: 1.5; text-align: center;">
                Start with one tiny habit. Two minutes or less.
            </p>
            <button onclick="showScreen('screen-add')" class="btn-primary" style="width: 100%; max-width: 280px; margin: 0 auto; display: block;">
                Add Your First Habit
            </button>`;
        return;
    }

    emptyEl.style.display = 'none';
    const yesterdayKey = new Date(Date.now() - 86400000).toISOString().slice(0, 10);
    listEl.innerHTML = habits.map(h => renderHabitItem(state, h, completedIds, yesterdayKey)).join('');
}

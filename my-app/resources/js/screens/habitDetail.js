/**
 * AtomicMe — Habit Detail Screen Component
 *
 * Encapsulates all rendering logic for `#screen-habit-detail`:
 *   - Streak hero (fire emoji, count, label, milestone badge)
 *   - Stats row (best streak, total done, 30-day rate)
 *   - Phase card (habit formation phase info)
 *   - 12-week heatmap (daily and frequency variants)
 *   - Insight message card (tailored to streak)
 *   - Notes timeline (last 5 completion notes)
 *   - Your Setup card (4-Laws data)
 *   - Reminder card (toggle + time input)
 *   - Complete button state
 *
 * All functions accept explicit arguments rather than reading global state
 * directly, making them independently testable.
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
 * Calculate the completion rate for a habit over the last `days` days.
 *
 * @param {Object}       state  Global application state.
 * @param {string|number} id    Habit ID.
 * @param {number}        days  Number of days to look back.
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
 * Count total completions for a habit across all recorded dates.
 *
 * @param {Object}       state  Global application state.
 * @param {string|number} id    Habit ID.
 * @returns {number}
 */
function calcTotalCompletions(state, id) {
    let total = 0;
    Object.values(state.completions).forEach(arr => {
        if (arr.map(String).includes(String(id))) { total++; }
    });
    return total;
}

// ─────────────────────────────────────────────
//  Milestone badge
// ─────────────────────────────────────────────

/**
 * Return a milestone badge string for a daily streak, or null.
 *
 * @param {number} streak
 * @returns {string|null}
 */
export function getMilestoneBadge(streak) {
    if (streak >= 100) { return '🌟 Legend — 100 days'; }
    if (streak >= 90)  { return '💎 Diamond — 90 days'; }
    if (streak >= 60)  { return '⚡ Elite — 60 days'; }
    if (streak >= 30)  { return '🏆 Champion — 30 days'; }
    if (streak >= 21)  { return '💪 Committed — 21 days'; }
    if (streak >= 14)  { return '🔥 On Fire — 14 days'; }
    if (streak >= 7)   { return '✨ Consistent — 7 days'; }
    return null;
}

/**
 * Return a milestone badge string for a weekly streak, or null.
 *
 * @param {number} weeks
 * @returns {string|null}
 */
export function getMilestoneBadgeWeekly(weeks) {
    if (weeks >= 52) { return '👑 Year Legend — 52 weeks'; }
    if (weeks >= 26) { return '🏆 Half Year — 26 weeks'; }
    if (weeks >= 13) { return '💎 Quarter — 13 weeks'; }
    if (weeks >= 9)  { return '💪 66-Day Zone — 9 weeks'; }
    if (weeks >= 4)  { return '✨ First Month — 4 weeks'; }
    return null;
}

// ─────────────────────────────────────────────
//  Insight messages
// ─────────────────────────────────────────────

/**
 * Return an HTML insight message for a daily streak.
 *
 * @param {number} streak     Current streak (days).
 * @param {string} habitName  Name of the habit.
 * @returns {string}  HTML string.
 */
export function getInsightMessage(streak, habitName) {
    if (streak === 0) {
        return `<strong>Start today.</strong> "The best time to plant a tree was 20 years ago. The second best time is now." Every master was once a beginner.`;
    }
    if (streak < 7) {
        return `<strong>${streak} day${streak > 1 ? 's' : ''} in.</strong> Your brain is beginning to link the cue, routine, and reward. The habit loop is forming. Keep showing up.`;
    }
    if (streak < 14) {
        return `<strong>One week strong!</strong> You've cast ${streak} votes for your identity. Research shows the first week is the hardest — and you've done it. The automaticity effect has begun.`;
    }
    if (streak < 21) {
        return `<strong>Two weeks of consistency!</strong> Around day 18, automaticity starts to kick in. Your brain is literally rewiring itself for ${habitName}. You're almost to the threshold.`;
    }
    if (streak < 30) {
        return `<strong>3-week warrior!</strong> Studies by University College London show habits form in an average of 66 days. You're one-third of the way to fully automatic. Keep it up.`;
    }
    if (streak < 60) {
        return `<strong>30+ day champion!</strong> You've crossed the hardest barrier. At this point, missing feels harder than doing. You're building an identity, not just a habit.`;
    }
    return `<strong>You ARE this habit now.</strong> After ${streak} days, this is no longer something you do — it is who you are. James Clear would be proud. Keep the chain alive.`;
}

/**
 * Return an HTML insight message for a weekly streak.
 *
 * @param {number} weeks      Current streak (weeks).
 * @param {string} habitName  Name of the habit.
 * @returns {string}  HTML string.
 */
export function getInsightMessageWeekly(weeks, habitName) {
    if (weeks === 0) {
        return `<strong>Start this week.</strong> "Reduce the habit to its minimal version." One week of showing up is all it takes to begin. Start today.`;
    }
    if (weeks < 4) {
        return `<strong>${weeks} week${weeks > 1 ? 's' : ''} in.</strong> Your schedule is adapting. The neural pathway is forming. Consistency over the next few weeks will lock this in.`;
    }
    if (weeks < 9) {
        return `<strong>First month complete!</strong> You've completed ${weeks} weeks. Research from University College London shows you're well on your way to automaticity. Don't stop now.`;
    }
    if (weeks < 13) {
        return `<strong>9 weeks — the 66-day threshold!</strong> Phillippa Lally's research says most habits solidify around 66 days. You've crossed that line. ${habitName} is sticking.`;
    }
    if (weeks < 26) {
        return `<strong>One quarter done!</strong> 13 weeks of ${habitName}. You've proven to yourself that this is who you are — not just something you're trying.`;
    }
    return `<strong>You ARE this habit.</strong> ${weeks} weeks of consistency. This is identity-level change. James Clear would call this an atomic habit fully formed.`;
}

// ─────────────────────────────────────────────
//  Heatmap HTML
// ─────────────────────────────────────────────

/**
 * Build the HTML for the 12-week heatmap grid.
 * Handles both daily habits (missed/done cells) and frequency habits
 * (week-met/missed-week cells based on targetDaysPerWeek).
 *
 * @param {Object}       state  Global application state.
 * @param {string|number} id    Habit ID.
 * @returns {string} HTML string of .heatmap-col divs.
 */
export function renderHeatmapHtml(state, id) {
    const todayStr = todayKey();
    const habit    = state.habits.find(h => String(h.id) === String(id));
    const isFreq   = habit && (habit.targetDaysPerWeek || 7) < 7;
    const target   = habit ? (habit.targetDaysPerWeek || 7) : 7;

    // Pre-compute per-week completion counts for frequency habits
    const weekCounts = {};
    if (isFreq) {
        for (let w = 0; w < 12; w++) {
            const anchorDate = new Date();
            anchorDate.setDate(anchorDate.getDate() - (11 - w) * 7 - 6);
            const weekStart = new Date(anchorDate);
            weekStart.setDate(weekStart.getDate() - weekStart.getDay());
            weekStart.setHours(0, 0, 0, 0);
            const weekKey = weekStart.toISOString().slice(0, 10);
            if (!weekCounts[weekKey]) {
                weekCounts[weekKey] = 0;
                for (let d = 0; d < 7; d++) {
                    const wd = new Date(weekStart);
                    wd.setDate(wd.getDate() + d);
                    const wdStr = wd.toISOString().slice(0, 10);
                    if ((state.completions[wdStr] || []).map(String).includes(String(id))) {
                        weekCounts[weekKey]++;
                    }
                }
            }
        }
    }

    const weeks = [];
    for (let w = 0; w < 12; w++) {
        const col = [];
        for (let d = 6; d >= 0; d--) {
            const offset  = (11 - w) * 7 + d;
            const date    = new Date();
            date.setDate(date.getDate() - offset);
            const dateStr = date.toISOString().slice(0, 10);
            const done    = (state.completions[dateStr] || []).map(String).includes(String(id));

            let cellClass = '';
            if (done) {
                cellClass = 'done';
            } else if (isFreq) {
                const weekStart = new Date(date);
                weekStart.setDate(weekStart.getDate() - weekStart.getDay());
                weekStart.setHours(0, 0, 0, 0);
                const weekKey = weekStart.toISOString().slice(0, 10);
                const wCount  = weekCounts[weekKey] || 0;
                cellClass = wCount >= target ? 'neutral' : 'missed';
            } else {
                cellClass = dateStr > todayStr ? '' : 'missed';
            }

            col.push({ dateStr, cellClass, isToday: dateStr === todayStr });
        }
        weeks.push(col);
    }

    return weeks.map(col =>
        `<div class="heatmap-col">${col.map(day =>
            `<div class="heatmap-cell ${day.cellClass} ${day.isToday ? 'today' : ''}"></div>`
        ).join('')}</div>`
    ).join('');
}

// ─────────────────────────────────────────────
//  Notes timeline HTML
// ─────────────────────────────────────────────

/**
 * Build the HTML for the notes timeline (last 5 notes, newest first).
 * Returns null if there are no notes (caller should hide the container).
 *
 * @param {Object}       state    Global application state.
 * @param {string|number} habitId Habit ID.
 * @returns {string|null} HTML string or null.
 */
export function renderNotesTimelineHtml(state, habitId) {
    const habitNotes = [];

    for (const [key, note] of Object.entries(state.completionNotes || {})) {
        const [date, hid] = key.split(':');
        if (String(hid) === String(habitId)) {
            habitNotes.push({ date, note });
        }
    }

    if (habitNotes.length === 0) { return null; }

    habitNotes.sort((a, b) => new Date(b.date) - new Date(a.date));
    const recentNotes = habitNotes.slice(0, 5);

    return recentNotes.map(item => {
        const dateObj = new Date(item.date);
        const dayName = dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        return `<div style="background: rgba(168, 85, 247, 0.08); border-left: 3px solid #A855F7; border-radius: 0.5rem; padding: 0.75rem; margin-bottom: 0.5rem;">
            <div style="font-size: 0.7rem; color: #8B92AB; margin-bottom: 0.25rem;">${dayName}</div>
            <div style="font-size: 0.85rem; color: #EAEDF6; line-height: 1.4;">"${item.note}"</div>
        </div>`;
    }).join('');
}

// ─────────────────────────────────────────────
//  Setup card HTML
// ─────────────────────────────────────────────

/**
 * Build the HTML for the "Your Setup" card fields.
 * Returns null if the habit has no non-empty setup fields.
 *
 * @param {Object} habit  Habit object.
 * @returns {string|null} HTML string or null.
 */
export function renderSetupCardHtml(habit) {
    const fields = [
        { label: 'Your Why',          value: habit.why },
        { label: '2-Minute Version',  value: habit.twoMin },
        { label: 'Habit Stack',       value: habit.stack },
        { label: 'Temptation Bundle', value: habit.bundle },
        { label: 'Your Reward',       value: habit.reward },
    ].filter(f => f.value && f.value.trim() !== '');

    if (fields.length === 0) { return null; }

    return fields.map(f =>
        `<div class="setup-field">
            <div class="setup-field-label">${f.label}</div>
            <div class="setup-field-value">${f.value}</div>
        </div>`
    ).join('');
}

// ─────────────────────────────────────────────
//  Main render / update functions
// ─────────────────────────────────────────────

/**
 * Update the streak hero section (fire, count number, label, milestone badge).
 *
 * @param {Object} streakData  { value, unit, graceDayActive } from state.streakData[id].
 * @param {number} rawStreak   Plain streak integer from state.streaks[id].
 * @returns {void}
 */
export function updateStreakHero(streakData, rawStreak) {
    const sd          = streakData || { value: rawStreak, unit: 'days', graceDayActive: false };
    const streakUnit  = sd.unit;

    const fireEl      = document.getElementById('detail-fire');
    const countEl     = document.getElementById('detail-streak-num');
    const labelEl     = document.querySelector('.streak-label');
    const badgeEl     = document.getElementById('detail-milestone');

    if (fireEl) {
        fireEl.textContent = rawStreak > 0
            ? (streakUnit === 'weeks' ? '📅🔥' : getStreakEmoji(rawStreak))
            : '💤';
    }
    if (countEl) { countEl.textContent = sd.value; }
    if (labelEl) { labelEl.textContent = streakUnit === 'weeks' ? 'week streak' : 'day streak'; }

    if (badgeEl) {
        const badge = streakUnit === 'weeks'
            ? getMilestoneBadgeWeekly(sd.value)
            : getMilestoneBadge(rawStreak);
        if (badge) {
            badgeEl.textContent    = badge;
            badgeEl.style.display = 'inline-flex';
        } else {
            badgeEl.style.display = 'none';
        }
    }
}

/**
 * Update all DOM elements in #screen-habit-detail from the provided state and habit ID.
 * Mirrors `showHabitDetail()` in welcome.blade.php.
 *
 * @param {Object}       state  Global application state.
 * @param {string|number} id    Habit ID.
 * @returns {void}
 */
export function updateDetailScreen(state, id) {
    const habit = state.habits.find(h => String(h.id) === String(id));
    if (!habit) { return; }

    const streak    = state.streaks[id] || 0;
    const sd        = state.streakData[id] || { value: streak, unit: 'days', graceDayActive: false };
    const bsd       = state.bestStreakData[id] || { value: state.bestStreaks[id] || 0, unit: sd.unit };
    const bestStreak = Math.max(bsd.value, sd.value);
    const todayDone  = (state.completions[todayKey()] || []).map(String).includes(String(id));
    const rate30     = calcCompletionRate(state, id, 30);
    const totalDone  = calcTotalCompletions(state, id);

    // Title
    const titleEl = document.getElementById('detail-title');
    if (titleEl) { titleEl.textContent = `${habit.emoji} ${habit.name}`; }

    // Streak hero
    updateStreakHero(sd, streak);

    // Stats row
    const set = (elId, val) => { const el = document.getElementById(elId); if (el) { el.textContent = val; } };
    set('detail-best',  bestStreak);
    set('detail-total', totalDone);
    set('detail-rate',  rate30 + '%');

    // Phase card
    const phaseCard = document.getElementById('detail-phase-card');
    if (phaseCard) {
        if (habit.phase) {
            set('detail-phase-icon',  habit.phase.icon || '🌱');
            set('detail-phase-label', habit.phase.label || 'Phase Unknown');
            set('detail-phase-description', habit.phase.description || '');
            const consistencyEl = document.getElementById('detail-phase-consistency');
            if (consistencyEl) {
                consistencyEl.textContent = habit.phase.consistencyRate !== undefined
                    ? `${habit.phase.consistencyRate}% consistency`
                    : `Day ${habit.phase.daysSinceCreation || 0}`;
            }
            phaseCard.style.display = 'block';
        } else {
            phaseCard.style.display = 'none';
        }
    }

    // Heatmap
    const heatmapEl = document.getElementById('detail-heatmap');
    if (heatmapEl) { heatmapEl.innerHTML = renderHeatmapHtml(state, id); }

    // Insight
    const insightEl = document.getElementById('detail-insight');
    if (insightEl) {
        insightEl.innerHTML = sd.unit === 'weeks'
            ? getInsightMessageWeekly(sd.value, habit.name)
            : getInsightMessage(streak, habit.name);
    }

    // Notes timeline
    const notesEl    = document.getElementById('detail-notes-timeline');
    const notesListEl = document.getElementById('detail-notes-list');
    if (notesEl && notesListEl) {
        const notesHtml = renderNotesTimelineHtml(state, id);
        if (notesHtml) {
            notesListEl.innerHTML = notesHtml;
            notesEl.style.display = 'block';
        } else {
            notesEl.style.display = 'none';
        }
    }

    // Setup card
    const setupCardEl    = document.getElementById('detail-setup');
    const setupFieldsEl  = document.getElementById('detail-setup-fields');
    if (setupCardEl && setupFieldsEl) {
        const setupHtml = renderSetupCardHtml(habit);
        if (setupHtml) {
            setupFieldsEl.innerHTML = setupHtml;
            setupCardEl.style.display = '';
        } else {
            setupCardEl.style.display = 'none';
        }
    }

    // Completion status chip
    const chipEl    = document.getElementById('detail-completion-chip');
    const chipIcon  = document.getElementById('detail-chip-icon');
    const chipLabel = document.getElementById('detail-chip-label');
    const chipSub   = document.getElementById('detail-chip-sub');
    if (chipEl) {
        if (todayDone) {
            chipEl.classList.remove('pending');
            chipEl.classList.add('done');
            if (chipIcon)  { chipIcon.textContent  = '✓'; }
            if (chipLabel) { chipLabel.textContent  = 'Completed today'; }
            if (chipSub)   { chipSub.textContent    = 'Tap to undo'; }
        } else {
            chipEl.classList.remove('done');
            chipEl.classList.add('pending');
            if (chipIcon)  { chipIcon.textContent  = ''; }
            if (chipLabel) { chipLabel.textContent  = 'Tap to mark complete'; }
            if (chipSub)   { chipSub.textContent    = 'Not done today'; }
        }
    }
}

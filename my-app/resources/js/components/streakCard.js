/**
 * AtomicMe — Streak Card Component
 *
 * Renders a streak "card" HTML snippet showing fire emoji, streak count,
 * and optional grace-day / at-risk state. Used by the Habit Detail and
 * (optionally) Home screen.
 *
 * Usage:
 *   import { renderStreakCardHtml } from '../components/streakCard.js';
 */

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
 * Build the streak badge HTML for use inside a habit list item or detail screen.
 *
 * @param {Object} streakData  `{ value, unit, graceDayActive }` from state.streakData.
 * @param {number} rawStreak   Plain integer streak from state.streaks.
 * @param {boolean} isAtRisk   Whether the streak is at risk of breaking today.
 * @returns {string}  HTML string (may be empty if no streak).
 */
export function renderStreakBadgeHtml(streakData, rawStreak, isAtRisk) {
    const sd         = streakData || { value: rawStreak, unit: 'days', graceDayActive: false };
    const isGraceDay = sd.graceDayActive;

    if (sd.unit === 'weeks') {
        const freqClass = isGraceDay ? 'grace-day-text' : '';
        return sd.value > 0
            ? `<div class="habit-streak ${freqClass}">📅 ${sd.value} week streak</div>`
            : '';
    }

    if (rawStreak <= 0) { return ''; }

    const streakClass  = isGraceDay ? 'grace-day-text' : (isAtRisk ? 'at-risk-text' : '');
    const streakSuffix = isGraceDay
        ? ' · grace day active'
        : (isAtRisk ? ' · ⚠️ at risk!' : '');

    return `<div class="habit-streak ${streakClass}">${getStreakEmoji(rawStreak)} ${rawStreak} day streak${streakSuffix}</div>`;
}

/**
 * Build the full streak hero section HTML (used in Habit Detail screen).
 *
 * @param {Object} streakData  `{ value, unit, graceDayActive }`.
 * @param {number} rawStreak   Plain integer streak.
 * @returns {string}  HTML string.
 */
export function renderStreakHeroHtml(streakData, rawStreak) {
    const sd         = streakData || { value: rawStreak, unit: 'days', graceDayActive: false };
    const streakUnit = sd.unit;
    const fireEmoji  = rawStreak > 0
        ? (streakUnit === 'weeks' ? '📅🔥' : getStreakEmoji(rawStreak))
        : '💤';
    const unitLabel  = streakUnit === 'weeks' ? 'week streak' : 'day streak';

    return `
        <div class="streak-fire">${fireEmoji}</div>
        <div class="streak-count-num">${sd.value}</div>
        <div class="streak-label">${unitLabel}</div>
    `;
}

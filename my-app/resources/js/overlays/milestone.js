/**
 * AtomicMe — Milestone Overlay Component
 *
 * Encapsulates the `#milestone-overlay` show/hide/confetti logic, including
 * streak milestones (daily and weekly) and achievement celebrations.
 *
 * Exports:
 *   - `showMilestone(value, unit)` — display a streak milestone
 *   - `showAchievementCelebration(achievement)` — display an achievement unlock
 *   - `closeMilestone()` — dismiss the overlay
 */

// ─────────────────────────────────────────────
//  Constants
// ─────────────────────────────────────────────

const MILESTONES = [
    { days: 7,   emoji: '✨', title: '7-Day Streak!',    sub: "You're building momentum. The habit loop is activating.",             quote: "Success is the product of daily habits—not once-in-a-lifetime transformations." },
    { days: 14,  emoji: '🔥', title: '2-Week Streak!',   sub: "The habit loop is forming. Your brain is changing.",                  quote: "Every action you take is a vote for the type of person you wish to become." },
    { days: 21,  emoji: '💪', title: '21-Day Streak!',   sub: "Science says it's becoming automatic. You've crossed a threshold.",   quote: "Habits are the compound interest of self-improvement." },
    { days: 30,  emoji: '🏆', title: '30-Day Champion!', sub: "A full month of showing up. This is extraordinary.",                  quote: "You do not rise to the level of your goals. You fall to the level of your systems." },
    { days: 60,  emoji: '⚡', title: '60 Days Strong!',  sub: "You're in the top 1% of habit-keepers on the planet.",               quote: "The most effective form of motivation is progress." },
    { days: 90,  emoji: '💎', title: '90-Day Legend!',   sub: "This is no longer something you do. It's who you are.",              quote: "The secret to getting results that last is to never stop making improvements." },
    { days: 100, emoji: '🌟', title: '100 Days!!!',      sub: "Legendary. Truly legendary. You are living proof it works.",          quote: "Small changes often appear to make no difference until you cross a critical threshold." },
];

const WEEKLY_MILESTONES = [
    { weeks: 4,  emoji: '🎯', title: '4 Weeks Consistent!',      sub: "One full month of showing up. This habit is taking hold.",          quote: "You don't have to be the victim of your environment. You can also be the architect of it." },
    { weeks: 9,  emoji: '💪', title: '9 Weeks — Habit Forming!', sub: "Research shows habits solidify around the 66-day mark. You're there.", quote: "Habits are the compound interest of self-improvement." },
    { weeks: 13, emoji: '🏆', title: 'Quarter Year!',             sub: "13 weeks of consistent effort. A full quarter of real change.",      quote: "Every action you take is a vote for the type of person you wish to become." },
    { weeks: 26, emoji: '⚡', title: 'Halfway to a Year!',        sub: "26 weeks. You're unstoppable. Half a year of identity votes.",       quote: "The most effective form of motivation is progress." },
    { weeks: 52, emoji: '👑', title: 'A Full Year!',              sub: "52 weeks of consistency. You ARE this habit. Crown deserved.",       quote: "Small changes often appear to make no difference until you cross a critical threshold." },
];

const ACHIEVEMENTS_DEFS = {
    perfect_day:      { name: 'Perfect Day',      icon: '⭐',  desc: 'Complete all habits in one day',     prestige: false },
    perfect_week:     { name: 'Perfect Week',     icon: '📅',  desc: 'Complete all habits for all 7 days', prestige: false },
    habit_builder:    { name: 'Habit Builder',    icon: '🔨',  desc: 'Create your 3rd and 5th habits',     prestige: false },
    comeback:         { name: 'Comeback',         icon: '🔥',  desc: 'Rebuild a streak after it broke',    prestige: false },
    one_percent_club: { name: 'The 1% Club',      icon: '💎',  desc: '365 consecutive days on one habit',  prestige: true },
    atomic_identity:  { name: 'Atomic Identity',  icon: '⚛️', desc: 'All habits in Identity phase',       prestige: true },
    perfect_quarter:  { name: 'Perfect Quarter',  icon: '👑',  desc: '90 days straight, zero missed days', prestige: true },
};

// ─────────────────────────────────────────────
//  Private helpers
// ─────────────────────────────────────────────

/**
 * Inject CSS confetti particles around the milestone emoji element.
 *
 * @param {HTMLElement} emojiEl        The element to attach particles to.
 * @param {string[]}    confettiColors Array of colour strings.
 * @param {number}      count          Number of particles to inject.
 * @returns {void}
 */
function _spawnConfetti(emojiEl, confettiColors, count) {
    for (let i = 0; i < count; i++) {
        const particle = document.createElement('div');
        particle.className = 'confetti-particle';
        const tx = (Math.random() * 200 - 100).toFixed(0) + 'px';
        const ty = (Math.random() * 200 - 100).toFixed(0) + 'px';
        particle.style.cssText = `--tx:${tx}; --ty:${ty}; background:${confettiColors[i % confettiColors.length]}; left:50%; top:50%; margin-left:-4px; margin-top:-4px;`;
        emojiEl.appendChild(particle);
    }
    setTimeout(() => {
        emojiEl.querySelectorAll('.confetti-particle').forEach(p => p.remove());
    }, 600);
}

/**
 * Make the overlay visible and lock the dismiss button behind the
 * entrance animation timing.
 *
 * @param {string} dismissLabel  Text to show on the dismiss button.
 * @returns {void}
 */
function _showOverlay(dismissLabel) {
    const overlay    = document.getElementById('milestone-overlay');
    const dismissBtn = document.getElementById('milestone-dismiss-btn');

    if (overlay) { overlay.classList.add('visible'); }

    if (dismissBtn) {
        if (dismissLabel) { dismissBtn.textContent = dismissLabel; }
        dismissBtn.classList.remove('interactive');
        setTimeout(() => dismissBtn.classList.add('interactive'), 550);
    }
}

// ─────────────────────────────────────────────
//  Public API
// ─────────────────────────────────────────────

/**
 * Show the full-screen milestone celebration overlay for a streak milestone.
 *
 * @param {number}           value  Streak count (days or weeks).
 * @param {'days'|'weeks'}   unit   Streak unit.
 * @returns {void}
 */
export function showMilestone(value, unit) {
    const isWeekly = unit === 'weeks';
    const m = isWeekly
        ? (WEEKLY_MILESTONES.find(x => x.weeks === value) || WEEKLY_MILESTONES[0])
        : (MILESTONES.find(x => x.days === value) || MILESTONES[0]);

    const set = (id, val) => { const el = document.getElementById(id); if (el) { el.textContent = val; } };
    set('milestone-emoji', m.emoji);
    set('milestone-title', m.title);
    set('milestone-sub',   m.sub);
    set('milestone-quote', `"${m.quote}"`);

    _showOverlay('Keep Going! 🚀');

    const emojiEl = document.getElementById('milestone-emoji');
    if (emojiEl) {
        _spawnConfetti(
            emojiEl,
            ['#E8743C', '#C45A29', '#7C6FE0', '#3F8B57', '#F0A26B'],
            8
        );
    }
}

/**
 * Re-use the milestone overlay to celebrate an achievement unlock.
 * Uses gold confetti colours for prestige achievements.
 *
 * @param {{ code: string, prestige?: boolean }} achievement  Achievement record.
 * @returns {void}
 */
export function showAchievementCelebration(achievement) {
    const def = ACHIEVEMENTS_DEFS[achievement.code];
    if (!def) { return; }

    const set = (id, val) => { const el = document.getElementById(id); if (el) { el.textContent = val; } };
    set('milestone-emoji', def.icon);
    set('milestone-title', def.name + '!');
    set('milestone-sub',   'Achievement Unlocked');
    set('milestone-quote', def.desc);

    _showOverlay(def.prestige ? 'Prestige Earned! 🏆' : 'Unlocked! 🎉');

    const emojiEl = document.getElementById('milestone-emoji');
    if (emojiEl) {
        const confettiColors = def.prestige
            ? ['#C45A29', '#E8743C', '#F0A26B', '#7C6FE0', '#3F8B57']
            : ['#E8743C', '#C45A29', '#7C6FE0', '#3F8B57', '#F0A26B'];
        _spawnConfetti(emojiEl, confettiColors, 12);
    }
}

/**
 * Dismiss the milestone / achievement celebration overlay.
 *
 * @returns {void}
 */
export function closeMilestone() {
    const overlay = document.getElementById('milestone-overlay');
    if (overlay) { overlay.classList.remove('visible'); }
}

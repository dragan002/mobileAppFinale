/**
 * AtomicMe — Stat Row Component
 *
 * Renders a horizontal row of `.stat-box` elements. Used in Stats,
 * Growth, Habit Detail, and Profile Sheet screens.
 *
 * Usage:
 *   import { renderStatRowHtml } from '../components/statRow.js';
 *
 *   const html = renderStatRowHtml([
 *     { val: '42',  lbl: 'Day Streak' },
 *     { val: '100', lbl: 'Total Done' },
 *     { val: '85%', lbl: "Today's Rate" },
 *   ]);
 */

/**
 * Build a `.stats-row` HTML string from an array of stat definitions.
 *
 * @param {Array<{ val: string|number, lbl: string }>} stats  Stat items.
 * @returns {string}  HTML string.
 */
export function renderStatRowHtml(stats) {
    const boxes = stats.map(s =>
        `<div class="stat-box">
            <div class="val">${s.val}</div>
            <div class="lbl">${s.lbl}</div>
        </div>`
    ).join('');
    return `<div class="stats-row">${boxes}</div>`;
}

/**
 * Update a set of DOM stat box elements in place (no re-render).
 * Each item maps an element ID to a new value string.
 *
 * @param {Array<{ id: string, val: string|number }>} items
 * @returns {void}
 */
export function updateStatElements(items) {
    items.forEach(({ id, val }) => {
        const el = document.getElementById(id);
        if (el) { el.textContent = val; }
    });
}

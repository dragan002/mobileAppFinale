/**
 * AtomicMe — Chart Component
 *
 * Shared bar-chart rendering helpers used in Stats and Growth screens.
 * Each function returns an HTML string of `.compound-bar` divs that
 * can be injected into any `.compound-chart` container.
 *
 * Usage:
 *   import { renderCompoundChartHtml, renderRateChartHtml } from '../components/chart.js';
 */

/**
 * Build the HTML for the standard 1%-per-day compound growth chart.
 * Uses 5 data points at 0, 30, 90, 180, and 365 days.
 * An optional `currentDay` highlights which bar the user is closest to.
 *
 * @param {number} [currentDay=0]  User's best streak in days, used to mark progress.
 * @returns {string} HTML string of .compound-bar divs.
 */
export function renderCompoundChartHtml(currentDay = 0) {
    // Each entry: [days, display multiplier label]
    const points = [
        [0,   '1x'],
        [30,  '1.3x'],
        [90,  '2.5x'],
        [180, '6x'],
        [365, '37x'],
    ];
    const maxVal = Math.pow(1.01, 365);

    return points.map(([day, multiplierLabel]) => {
        const val    = day === 0 ? 1 : Math.pow(1.01, day);
        const height = Math.max(4, (val / maxVal) * 100);

        // Mark this bar if the user's streak is within this segment
        const isActive = currentDay >= day && currentDay < (points[points.indexOf(points.find(p => p[0] === day)) + 1]?.[0] ?? Infinity);
        const activeAttr = isActive ? ' data-active="true"' : '';

        return `<div class="compound-bar-wrap">
            <div class="compound-bar-label">${multiplierLabel}</div>
            <div class="compound-bar${isActive ? ' compound-bar--active' : ''}" style="height:${height}%"${activeAttr}></div>
        </div>`;
    }).join('');
}

/**
 * Build the HTML for a generic rate bar chart from an array of values.
 * Heights are normalised relative to the max value in the array.
 *
 * @param {number[]} rates  Array of numeric values.
 * @returns {string} HTML string of .compound-bar divs.
 */
export function renderRateChartHtml(rates) {
    if (!rates || rates.length === 0) { return ''; }
    const maxRate = Math.max(...rates, 1);
    return rates.map(rate => {
        const height = maxRate > 0 ? Math.max(4, (rate / maxRate) * 100) : 4;
        return `<div class="compound-bar" style="height:${height}%"></div>`;
    }).join('');
}

/**
 * Build the HTML for the compound growth projection chart.
 * Uses 5 data points at 0, 90, 180, 270, and 365 days.
 *
 * @param {number} consistencyRate  All-time consistency rate (0–100).
 * @returns {string} HTML string of .compound-bar divs.
 */
export function renderProjectionChartHtml(consistencyRate) {
    const rate   = consistencyRate / 100;
    const maxVal = rate > 0 ? Math.pow(rate, 365) : 1;
    const points = [0, 90, 180, 270, 365];
    return points.map(day => {
        const val    = day === 0 ? 1 : (rate > 0 ? Math.pow(rate, day) : 0);
        const height = maxVal > 0 ? Math.max(4, (val / maxVal) * 100) : 4;
        return `<div class="compound-bar" style="height:${height}%"></div>`;
    }).join('');
}

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
 * Uses 6 data points spanning 1 week → 1 year.
 *
 * @returns {string} HTML string of .compound-bar divs.
 */
export function renderCompoundChartHtml() {
    const points = [1, 4, 8, 13, 26, 52];
    const maxVal = Math.pow(1.01, 365);
    return points.map(week => {
        const val    = Math.pow(1.01, week * 7);
        const height = Math.max(4, (val / maxVal) * 100);
        return `<div class="compound-bar" style="height:${height}%"></div>`;
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

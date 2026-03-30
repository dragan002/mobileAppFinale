/**
 * AtomicMe — Heatmap Component
 *
 * Shared heatmap rendering used in the Habit Detail screen.
 * Re-exports `renderHeatmapHtml` from screens/habitDetail.js so callers
 * can import it from a single, semantic location.
 *
 * Usage:
 *   import { renderHeatmapHtml } from '../components/heatmap.js';
 *   container.innerHTML = renderHeatmapHtml(state, habitId);
 */
export { renderHeatmapHtml } from '../screens/habitDetail.js';

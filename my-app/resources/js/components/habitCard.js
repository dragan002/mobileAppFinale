/**
 * AtomicMe — Habit Card Component
 *
 * Shared habit list-item HTML builder used in the Home screen.
 * Re-exports `renderHabitItem` from screens/home.js so callers can import
 * from a semantic component path.
 *
 * Usage:
 *   import { renderHabitItem } from '../components/habitCard.js';
 *   listEl.innerHTML = habits.map(h => renderHabitItem(state, h, completedIds, yesterdayKey)).join('');
 */
export { renderHabitItem } from '../screens/home.js';

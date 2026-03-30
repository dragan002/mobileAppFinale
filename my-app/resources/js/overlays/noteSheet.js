/**
 * AtomicMe — Note Sheet Overlay Component
 *
 * Encapsulates all logic for the `#note-sheet` slide-up panel used to
 * capture an optional completion note after a habit is checked off.
 *
 * Exports:
 *   - `openNoteSheet(habitId)` — open the sheet for a given habit
 *   - `closeNoteSheet()`       — close without saving
 *   - `readNoteValue()`        — return the current textarea value (trimmed)
 *   - `getPendingHabitId()`    — return the habitId currently staged (or null)
 */

// ─────────────────────────────────────────────
//  Module-level state
// ─────────────────────────────────────────────

/** Habit ID staged for note attachment, or null when closed. */
let _pendingHabitId = null;

// ─────────────────────────────────────────────
//  Public API
// ─────────────────────────────────────────────

/**
 * Open the completion note sheet for the given habit.
 * Clears any previously typed text.
 *
 * @param {string|number} habitId  Habit the note is associated with.
 * @returns {void}
 */
export function openNoteSheet(habitId) {
    _pendingHabitId = habitId;

    const noteInput  = document.getElementById('note-input');
    const backdrop   = document.getElementById('note-sheet-backdrop');
    const sheet      = document.getElementById('note-sheet');

    if (noteInput)  { noteInput.value = ''; }
    if (backdrop)   { backdrop.style.display = 'block'; }
    if (sheet)      { sheet.style.display = 'flex'; }
}

/**
 * Close the note sheet without saving.
 *
 * @returns {void}
 */
export function closeNoteSheet() {
    _pendingHabitId = null;

    const backdrop = document.getElementById('note-sheet-backdrop');
    const sheet    = document.getElementById('note-sheet');

    if (backdrop) { backdrop.style.display = 'none'; }
    if (sheet)    { sheet.style.display = 'none'; }
}

/**
 * Return the trimmed value currently entered in the note textarea.
 *
 * @returns {string}
 */
export function readNoteValue() {
    return (document.getElementById('note-input')?.value || '').trim();
}

/**
 * Return the habit ID currently staged in the note sheet, or null.
 *
 * @returns {string|number|null}
 */
export function getPendingHabitId() {
    return _pendingHabitId;
}

/**
 * AtomicMe — Form Helper Utilities
 *
 * Functions for reading values, validating fields, and clearing forms.
 * Extracted from the repeated patterns in the 4-step Add Habit form inside
 * welcome.blade.php.
 *
 * All functions guard against null elements and return safe defaults so they
 * are safe to call before the DOM is fully ready (useful for SSR hydration
 * scenarios and unit tests with a minimal DOM environment).
 */

/**
 * Read the trimmed value of a form input element by its `id`.
 *
 * @param {string} elementId  The input element's `id` attribute.
 * @returns {string}  Trimmed value, or `''` if the element is not found.
 */
export function getFieldValue(elementId) {
    const el = document.getElementById(elementId);
    return el ? el.value.trim() : '';
}

/**
 * Read the raw (untrimmed) value of a form input by its `id`.
 * Useful when whitespace is meaningful (e.g. textarea drafts).
 *
 * @param {string} elementId
 * @returns {string}
 */
export function getRawFieldValue(elementId) {
    const el = document.getElementById(elementId);
    return el ? el.value : '';
}

/**
 * Set the value of a form input element by its `id`. No-ops if not found.
 *
 * @param {string} elementId
 * @param {string} value
 * @returns {void}
 */
export function setFieldValue(elementId, value) {
    const el = document.getElementById(elementId);
    if (el) { el.value = value ?? ''; }
}

/**
 * Clear the value of one or more form inputs by their `id` attributes.
 *
 * @param {...string} elementIds  One or more element IDs.
 * @returns {void}
 */
export function clearFields(...elementIds) {
    elementIds.forEach(id => setFieldValue(id, ''));
}

/**
 * Validate that a string value is non-empty (after trimming).
 *
 * @param {string} value  The value to check.
 * @returns {boolean}     `true` if valid (non-empty).
 */
export function validateRequired(value) {
    return typeof value === 'string' && value.trim().length > 0;
}

/**
 * Validate that a string does not exceed a maximum character count.
 *
 * @param {string} value   The value to check.
 * @param {number} maxLen  Maximum allowed length.
 * @returns {boolean}
 */
export function validateMaxLength(value, maxLen) {
    return typeof value === 'string' && value.length <= maxLen;
}

/**
 * Read all habit form field values from the DOM and return them as a plain
 * object. Does NOT include picker-driven values (emoji, color, time, diff,
 * categoryId, targetDaysPerWeek) — those are tracked in the `newHabit` state
 * object because they rely on click interactions, not input events.
 *
 * @returns {{ name: string, why: string, bundle: string, twoMin: string, stack: string, duration: string, reward: string }}
 */
export function readHabitFormFields() {
    return {
        name: getFieldValue('new-name'),
        why: getFieldValue('new-why'),
        bundle: getFieldValue('new-bundle'),
        twoMin: getFieldValue('new-two-min'),
        stack: getFieldValue('new-stack'),
        duration: getFieldValue('new-duration'),
        reward: getFieldValue('new-reward'),
    };
}

/**
 * Clear all text-input fields in the Add Habit form.
 *
 * @returns {void}
 */
export function clearHabitFormFields() {
    clearFields(
        'new-name',
        'new-why',
        'new-bundle',
        'new-two-min',
        'new-stack',
        'new-duration',
        'new-reward'
    );
}

/**
 * Populate the Add Habit form text inputs from an existing habit object
 * (used when editing a habit). Does NOT set picker state — that is handled
 * separately in `showEditHabit()`.
 *
 * @param {{ name?: string, why?: string, bundle?: string, twoMin?: string, stack?: string, duration?: string, reward?: string }} habit
 * @returns {void}
 */
export function populateHabitFormFields(habit) {
    setFieldValue('new-name',     habit.name     ?? '');
    setFieldValue('new-why',      habit.why      ?? '');
    setFieldValue('new-bundle',   habit.bundle   ?? '');
    setFieldValue('new-two-min',  habit.twoMin   ?? '');
    setFieldValue('new-stack',    habit.stack    ?? '');
    setFieldValue('new-duration', habit.duration ?? '');
    setFieldValue('new-reward',   habit.reward   ?? '');
}

/**
 * Read user-profile form fields from the Edit Profile section of the
 * Profile Sheet.
 *
 * @returns {{ name: string }}
 */
export function readProfileFormFields() {
    return {
        name: getFieldValue('ps-name-input'),
    };
}

/**
 * Read the weekly review note textarea value.
 *
 * @returns {string}
 */
export function readWeeklyReviewNote() {
    return getFieldValue('wr-note');
}

/**
 * Clear the weekly review note textarea.
 *
 * @returns {void}
 */
export function clearWeeklyReviewNote() {
    setFieldValue('wr-note', '');
}

/**
 * Read the completion note from the note sheet.
 *
 * @returns {string}
 */
export function readNoteSheetValue() {
    return getFieldValue('note-input');
}

/**
 * Clear the note sheet textarea.
 *
 * @returns {void}
 */
export function clearNoteSheet() {
    setFieldValue('note-input', '');
}

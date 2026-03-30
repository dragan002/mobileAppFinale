/**
 * AtomicMe — Add / Edit Habit Screen Component
 *
 * Encapsulates all rendering and interaction logic for `#screen-add`:
 * the 5-step wizard (step 0 → freq → 1 → 2 → 3) including emoji grid,
 * colour picker, time picker, frequency picker, category picker, and
 * difficulty picker.
 *
 * This module does NOT call the API directly — it exposes the final
 * habit data via the `onSave` callback so the caller (welcome.blade.php)
 * can handle optimistic state + API sync.
 *
 * Exports:
 *   - `STEP_ORDER`         — canonical step sequence array
 *   - `defaultNewHabit()`  — factory for a blank habit draft object
 *   - `renderFrequencyGridHtml(targetDaysPerWeek)` — freq step HTML
 *   - `renderCategoryPickerHtml(categories, selectedId)` — category HTML
 *   - `goStep(n)`          — navigate wizard step (mutates currentStep)
 *   - `getCurrentStep()`   — returns current step name
 *   - `resetForm()`        — wipe draft + return to step 0
 *   - `readHabitDraft()`   — read all form fields + picker state into object
 *   - `populateForm(habit)` — fill form with existing habit for editing
 */

// ─────────────────────────────────────────────
//  Step constants
// ─────────────────────────────────────────────

/**
 * Canonical wizard step order. '0' is the basics step, 'freq' is the
 * frequency picker, then steps 1–3 are the four laws.
 *
 * @type {Array<number|string>}
 */
export const STEP_ORDER = [0, 'freq', 1, 2, 3];

// ─────────────────────────────────────────────
//  Module-level state
// ─────────────────────────────────────────────

/** The current step being displayed in the wizard. */
let _currentStep = 0;

// ─────────────────────────────────────────────
//  Defaults
// ─────────────────────────────────────────────

/**
 * Return a fresh blank habit draft object.
 *
 * @returns {Object}
 */
export function defaultNewHabit() {
    return {
        name:              '',
        emoji:             '🏃',
        time:              'morning',
        why:               '',
        bundle:            '',
        color:             '#1e3a2f',
        twoMin:            '',
        stack:             '',
        duration:          '',
        reward:            '',
        diff:              'medium',
        categoryId:        null,
        reminderTime:      '',
        targetDaysPerWeek: 7,
    };
}

// ─────────────────────────────────────────────
//  Frequency grid HTML
// ─────────────────────────────────────────────

/**
 * Build the HTML for the frequency selector grid (1–7 days/week).
 *
 * @param {number} targetDaysPerWeek  Currently selected frequency.
 * @returns {string} HTML string of .frequency-btn divs.
 */
export function renderFrequencyGridHtml(targetDaysPerWeek) {
    const labels = ['1x', '2x', '3x', '4x', '5x', '6x', 'Daily'];
    const subs   = ['Sun only', 'Twice', '3 days', '4 days', '5 days', '6 days', 'Every day'];
    return [1, 2, 3, 4, 5, 6, 7].map(d => {
        const selected = d === (targetDaysPerWeek || 7) ? 'selected' : '';
        return `<div class="frequency-btn ${selected}" data-freq="${d}">
            <div>${labels[d - 1]}</div>
            <div style="font-size:0.6rem;color:#8B92AB;margin-top:0.2rem;">${subs[d - 1]}</div>
        </div>`;
    }).join('');
}

// ─────────────────────────────────────────────
//  Category picker HTML
// ─────────────────────────────────────────────

/**
 * Build the HTML for the category picker pill list.
 *
 * @param {Array}        categories    Array of category objects from state.
 * @param {number|null}  selectedId    Currently selected category ID (or null).
 * @returns {string} HTML string.
 */
export function renderCategoryPickerHtml(categories, selectedId) {
    const noneSelected = selectedId === null || selectedId === undefined;
    const pills = categories.map(cat => {
        const isSelected = cat.id === selectedId ? 'selected' : '';
        return `<div class="category-pill ${isSelected}"
                     data-category-id="${cat.id}"
                     style="border-color: ${cat.color}; color: ${cat.color};"
                     >${cat.name}</div>`;
    }).join('');
    return `<div class="category-pill ${noneSelected ? 'selected' : ''}" data-category-id="">None</div>${pills}`;
}

// ─────────────────────────────────────────────
//  Step navigation
// ─────────────────────────────────────────────

/**
 * Navigate the wizard to the specified step. Hides the current step div,
 * shows the target step div, and updates the step indicator bar classes.
 * Also re-renders the frequency grid when entering the 'freq' step.
 *
 * @param {number|string} n    Target step: 0, 'freq', 1, 2, or 3.
 * @param {number}        targetDaysPerWeek  Used to pre-select freq grid.
 * @returns {void}
 */
export function goStep(n, targetDaysPerWeek) {
    const currentStepEl = document.getElementById('add-step-' + _currentStep);
    if (currentStepEl) { currentStepEl.style.display = 'none'; }

    _currentStep = n;

    const nextStepEl = document.getElementById('add-step-' + n);
    if (nextStepEl) { nextStepEl.style.display = 'block'; }

    // Update the step indicator bar
    STEP_ORDER.forEach((sid, idx) => {
        const el = document.getElementById('step-' + sid);
        if (!el) { return; }
        const activeIdx = STEP_ORDER.indexOf(n);
        el.classList.toggle('done',   idx < activeIdx);
        el.classList.toggle('active', idx === activeIdx);
    });

    // Render frequency grid when entering freq step
    if (n === 'freq') {
        const grid = document.getElementById('frequency-grid');
        if (grid) { grid.innerHTML = renderFrequencyGridHtml(targetDaysPerWeek || 7); }
    }

    // Scroll the add-body back to top on step change
    const addBody = document.querySelector('.add-body');
    if (addBody) { addBody.scrollTo(0, 0); }
}

/**
 * Return the current wizard step identifier.
 *
 * @returns {number|string}
 */
export function getCurrentStep() {
    return _currentStep;
}

// ─────────────────────────────────────────────
//  Form read / write
// ─────────────────────────────────────────────

/**
 * Read all habit form field values from the DOM plus picker state from the
 * provided draft object, and merge them into a single habit data object
 * ready to POST/PUT.
 *
 * @param {Object} draft  The `newHabit` draft object maintained by the caller
 *                        (holds emoji, color, time, diff, categoryId,
 *                        targetDaysPerWeek, reminderTime from click interactions).
 * @returns {Object} Merged habit data object.
 */
export function readHabitDraft(draft) {
    return {
        name:              (document.getElementById('new-name')?.value   || '').trim(),
        why:               (document.getElementById('new-why')?.value    || '').trim(),
        bundle:            (document.getElementById('new-bundle')?.value || '').trim(),
        twoMin:            (document.getElementById('new-two-min')?.value || '').trim(),
        stack:             (document.getElementById('new-stack')?.value  || '').trim(),
        duration:          (document.getElementById('new-duration')?.value || '').trim(),
        reward:            (document.getElementById('new-reward')?.value || '').trim(),
        emoji:             draft.emoji             || '🏃',
        time:              draft.time              || 'morning',
        color:             draft.color             || '#1e3a2f',
        diff:              draft.diff              || 'medium',
        categoryId:        draft.categoryId        ?? null,
        reminderTime:      draft.reminderTime      || '',
        targetDaysPerWeek: draft.targetDaysPerWeek || 7,
    };
}

/**
 * Reset all text input fields in the Add Habit form to empty.
 *
 * @returns {void}
 */
export function clearTextFields() {
    ['new-name', 'new-why', 'new-bundle', 'new-two-min', 'new-stack', 'new-duration', 'new-reward'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.value = ''; }
    });
}

/**
 * Reset all picker selections to their defaults:
 * - First emoji selected
 * - First time (morning) selected
 * - First colour selected
 * - Second difficulty (medium) selected
 *
 * @returns {void}
 */
export function resetPickerSelections() {
    document.querySelectorAll('.emoji-btn').forEach((b, i) => b.classList.toggle('selected', i === 0));
    document.querySelectorAll('#time-grid .time-btn').forEach((b, i) => b.classList.toggle('selected', i === 0));
    document.querySelectorAll('#color-grid .color-btn').forEach((b, i) => b.classList.toggle('selected', i === 0));
    document.querySelectorAll('#diff-grid .time-btn').forEach((b, i) => b.classList.toggle('selected', i === 1));
}

/**
 * Reset the full add form: clear text fields, reset pickers, go to step 0.
 *
 * @param {number} targetDaysPerWeek  Default frequency for the freq grid.
 * @returns {void}
 */
export function resetForm(targetDaysPerWeek) {
    clearTextFields();
    resetPickerSelections();
    goStep(0, targetDaysPerWeek || 7);
}

/**
 * Populate the Add Habit form fields from an existing habit object
 * (edit mode). Selects the matching emoji, colour, time, and difficulty buttons.
 *
 * @param {Object} habit  Existing habit object.
 * @returns {void}
 */
export function populateForm(habit) {
    const setValue = (id, val) => {
        const el = document.getElementById(id);
        if (el) { el.value = val ?? ''; }
    };

    setValue('new-name',     habit.name);
    setValue('new-why',      habit.why);
    setValue('new-bundle',   habit.bundle);
    setValue('new-two-min',  habit.twoMin);
    setValue('new-stack',    habit.stack);
    setValue('new-duration', habit.duration);
    setValue('new-reward',   habit.reward);

    // Emoji
    const emojiBtn = document.querySelector(`.emoji-btn[data-emoji="${habit.emoji}"]`);
    if (emojiBtn) {
        document.querySelectorAll('.emoji-btn').forEach(b => b.classList.remove('selected'));
        emojiBtn.classList.add('selected');
    }

    // Colour
    const colorBtn = document.querySelector(`#color-grid .color-btn[data-color="${habit.color}"]`);
    if (colorBtn) {
        document.querySelectorAll('#color-grid .color-btn').forEach(b => b.classList.remove('selected'));
        colorBtn.classList.add('selected');
    }

    // Time
    const timeBtn = document.querySelector(`#time-grid .time-btn[data-time="${habit.time}"]`);
    if (timeBtn) {
        document.querySelectorAll('#time-grid .time-btn').forEach(b => b.classList.remove('selected'));
        timeBtn.classList.add('selected');
    }

    // Difficulty
    const diffBtn = document.querySelector(`#diff-grid .time-btn[data-diff="${habit.diff}"]`);
    if (diffBtn) {
        document.querySelectorAll('#diff-grid .time-btn').forEach(b => b.classList.remove('selected'));
        diffBtn.classList.add('selected');
    }
}

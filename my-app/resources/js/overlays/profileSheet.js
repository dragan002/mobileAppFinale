/**
 * AtomicMe — Profile Sheet Overlay Component
 *
 * Encapsulates all logic for the `#profile-sheet` slide-up panel:
 *   - Opening with computed stats (days using, total completions, longest streak)
 *   - Identity selection in edit form
 *   - Saving profile changes (name + identity)
 *   - Closing the sheet
 *
 * This module reads/writes the global `state` object passed in as an argument.
 * It does NOT own `state` — callers pass the current state and receive the
 * updated state via callbacks.
 */

// ─────────────────────────────────────────────
//  Constants
// ─────────────────────────────────────────────

const IDENTITY_MAP = {
    athlete: { label: 'The Athlete',  icon: '🏃' },
    learner: { label: 'The Learner',  icon: '📚' },
    creator: { label: 'The Creator',  icon: '🎨' },
    mindful: { label: 'The Mindful',  icon: '🧘' },
    leader:  { label: 'The Leader',   icon: '🚀' },
    healthy: { label: 'The Healthy',  icon: '🥗' },
};

// ─────────────────────────────────────────────
//  Module-level state
// ─────────────────────────────────────────────

/** Currently selected identity in the edit form. */
let _profileSelectedIdentity = null;

// ─────────────────────────────────────────────
//  Stats computation
// ─────────────────────────────────────────────

/**
 * Compute the profile stats from state.
 *
 * @param {Object} state  Global application state.
 * @returns {{ daysUsing: number, totalCompletions: number, longestStreak: number }}
 */
export function computeProfileStats(state) {
    const u = state.user;

    // Days using the app
    let earliestDate = null;
    Object.keys(state.completions).forEach(dateKey => {
        if (state.completions[dateKey].length > 0) {
            if (!earliestDate || dateKey < earliestDate) { earliestDate = dateKey; }
        }
    });

    let daysUsing = 1;
    if (earliestDate) {
        const start = new Date(earliestDate);
        const now   = new Date();
        daysUsing   = Math.max(1, Math.round((now - start) / 86400000) + 1);
    } else if (u && u.createdAt) {
        const start = new Date(u.createdAt);
        const now   = new Date();
        daysUsing   = Math.max(1, Math.round((now - start) / 86400000) + 1);
    }

    // Total completions
    let totalCompletions = 0;
    Object.values(state.completions).forEach(arr => { totalCompletions += arr.length; });

    // Longest streak across all habits
    const longestStreak = state.habits.length > 0
        ? Math.max(...state.habits.map(h =>
            Math.max(state.bestStreaks[h.id] || 0, state.streaks[h.id] || 0)
          ))
        : 0;

    return { daysUsing, totalCompletions, longestStreak };
}

// ─────────────────────────────────────────────
//  Open / close
// ─────────────────────────────────────────────

/**
 * Open the profile sheet and populate all stats and edit form fields.
 *
 * @param {Object} state  Global application state.
 * @returns {void}
 */
export function openProfileSheet(state) {
    if (!state.user) { return; }

    const u             = state.user;
    const identityData  = IDENTITY_MAP[u.identity] || { label: u.identityLabel, icon: u.identityIcon };
    const { daysUsing, totalCompletions, longestStreak } = computeProfileStats(state);

    // Identity dashboard
    const set = (id, val) => { const el = document.getElementById(id); if (el) { el.textContent = val; } };
    set('ps-identity-icon',    identityData.icon);
    set('ps-user-name',        u.name);
    set('ps-identity-label',   identityData.label);
    set('ps-days-using',       daysUsing);
    set('ps-total-completions', totalCompletions);
    set('ps-best-streak',      longestStreak);
    set('ps-votes-count',      totalCompletions);
    set('ps-votes-identity',   identityData.label);

    // Edit form
    const nameInput = document.getElementById('ps-name-input');
    if (nameInput) { nameInput.value = u.name; }

    _profileSelectedIdentity = u.identity;
    document.querySelectorAll('.profile-identity-option').forEach(el => {
        el.classList.toggle('selected', el.dataset.id === u.identity);
    });

    // Custom identity panel — show and populate if the user has a custom identity
    const customPanel = document.getElementById('ps-custom-panel');
    if (customPanel) {
        const isCustom = u.identity === 'custom';
        customPanel.style.display = isCustom ? 'block' : 'none';
        if (isCustom) {
            const labelInput = document.getElementById('ps-custom-label');
            if (labelInput) { labelInput.value = u.identityLabel || ''; }
            // Pre-select the saved icon
            const savedIcon = u.identityIcon || '⭐';
            document.querySelectorAll('#ps-custom-icons .ob-custom-icon-btn').forEach(btn => {
                btn.classList.toggle('selected', btn.dataset.icon === savedIcon);
            });
        }
    }

    // Show sheet
    document.getElementById('profile-sheet-backdrop')?.classList.add('show');
    document.getElementById('profile-sheet')?.classList.add('show');
}

/**
 * Close the profile sheet and its backdrop.
 *
 * @returns {void}
 */
export function closeProfileSheet() {
    document.getElementById('profile-sheet-backdrop')?.classList.remove('show');
    document.getElementById('profile-sheet')?.classList.remove('show');
}

// ─────────────────────────────────────────────
//  Identity selection
// ─────────────────────────────────────────────

/**
 * Mark one identity option in the edit form as selected.
 *
 * @param {HTMLElement} el  The clicked `.profile-identity-option` element.
 * @returns {void}
 */
export function selectProfileIdentity(el) {
    document.querySelectorAll('.profile-identity-option').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    _profileSelectedIdentity = el.dataset.id || null;

    const customPanel = document.getElementById('ps-custom-panel');
    if (customPanel) {
        customPanel.style.display = _profileSelectedIdentity === 'custom' ? 'block' : 'none';
        if (_profileSelectedIdentity === 'custom') {
            setTimeout(() => document.getElementById('ps-custom-label')?.focus(), 50);
        }
    }
}

/**
 * Return the currently selected identity in the edit form.
 *
 * @returns {string|null}
 */
export function getSelectedIdentity() {
    return _profileSelectedIdentity;
}

// ─────────────────────────────────────────────
//  Save profile
// ─────────────────────────────────────────────

/**
 * Read the profile edit form values and return the new profile data.
 * Returns null if validation fails (name empty, no identity selected, or custom
 * identity selected but no label typed).
 *
 * @returns {{ name: string, identity: string, identityLabel: string, identityIcon: string }|null}
 */
export function readProfileForm() {
    const name = (document.getElementById('ps-name-input')?.value || '').trim();
    if (!name || !_profileSelectedIdentity) { return null; }

    if (_profileSelectedIdentity === 'custom') {
        const label = (document.getElementById('ps-custom-label')?.value || '').trim();
        if (!label) { return null; }
        const icon = document.querySelector('#ps-custom-icons .ob-custom-icon-btn.selected')?.dataset.icon || '⭐';
        return {
            name,
            identity:      'custom',
            identityLabel: label,
            identityIcon:  icon,
        };
    }

    const identity = IDENTITY_MAP[_profileSelectedIdentity];
    if (!identity) { return null; }

    return {
        name,
        identity:      _profileSelectedIdentity,
        identityLabel: identity.label,
        identityIcon:  identity.icon,
    };
}

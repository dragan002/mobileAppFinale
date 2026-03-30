/**
 * AtomicMe — Onboarding Screen Component
 *
 * Encapsulates all logic for `#screen-onboarding`: identity card selection,
 * name input validation, and the finish-onboarding flow.
 *
 * This module exports:
 *   - `render()`             — static HTML string for the screen body
 *   - `attachListeners()`    — wires all onclick handlers using event delegation
 *   - `cleanup()`            — removes the delegated listener (memory leak prevention)
 *   - `validateReady()`      — returns { valid, name, identityKey }
 *   - `getSelectedIdentity()` — returns the currently selected identity key
 */

// ─────────────────────────────────────────────
//  Constants
// ─────────────────────────────────────────────

export const IDENTITY_MAP = {
    athlete: { label: 'The Athlete',  icon: '🏃' },
    learner: { label: 'The Learner',  icon: '📚' },
    creator: { label: 'The Creator',  icon: '🎨' },
    mindful: { label: 'The Mindful',  icon: '🧘' },
    leader:  { label: 'The Leader',   icon: '🚀' },
    healthy: { label: 'The Healthy',  icon: '🥗' },
};

/**
 * Identity card definitions for the onboarding grid.
 * Order determines display order and animation-delay stagger.
 */
const IDENTITY_CARDS = [
    { id: 'athlete', icon: '🏃', label: 'The Athlete', sub: 'Fit &amp; energetic' },
    { id: 'learner', icon: '📚', label: 'The Learner', sub: 'Curious &amp; growing' },
    { id: 'creator', icon: '🎨', label: 'The Creator', sub: 'Building &amp; making' },
    { id: 'mindful', icon: '🧘', label: 'The Mindful', sub: 'Calm &amp; focused' },
    { id: 'leader',  icon: '🚀', label: 'The Leader',  sub: 'Driven &amp; bold' },
    { id: 'healthy', icon: '🥗', label: 'The Healthy', sub: 'Nourished &amp; strong' },
];

// ─────────────────────────────────────────────
//  Module-level state
// ─────────────────────────────────────────────

/** Currently selected identity key (or null). */
let _selectedIdentity = null;

/** Reference to the delegated click handler for cleanup. */
let _clickHandler = null;

/** Reference to the input handler for cleanup. */
let _inputHandler = null;

// ─────────────────────────────────────────────
//  render()
// ─────────────────────────────────────────────

/**
 * Return the full HTML string for the onboarding screen body.
 * The outer `<div id="screen-onboarding" class="screen active">` wrapper is
 * kept in the static blade template; this returns everything inside it.
 *
 * @returns {string} HTML string.
 */
export function render() {
    const cardsHtml = IDENTITY_CARDS.map(card => `
        <div class="identity-card" data-id="${card.id}">
            <div class="icon">${card.icon}</div>
            <div class="label">${card.label}</div>
            <div class="sub">${card.sub}</div>
        </div>
    `).join('');

    return `
        <div class="ob-logo"><span>AtomicMe</span></div>
        <div class="ob-tagline">Tiny changes. Remarkable results.</div>

        <div class="ob-question">Who do you want to become?</div>
        <div class="ob-sub">Your habits are votes for your identity. Start with who, not what.</div>

        <div class="identity-grid" id="identity-grid">
            ${cardsHtml}
        </div>

        <div class="ob-name-wrap">
            <label>What should we call you?</label>
            <input type="text" id="user-name" placeholder="e.g. Alex" maxlength="20">
        </div>

        <button class="btn-primary" id="ob-btn" disabled>Start My Journey →</button>
    `;
}

// ─────────────────────────────────────────────
//  attachListeners()
// ─────────────────────────────────────────────

/**
 * Wire up event listeners on the onboarding screen using event delegation.
 * Uses a single click handler on #screen-onboarding and a single input handler
 * on #user-name. Both are stored for cleanup.
 *
 * @param {{ onFinish: function(name: string, identity: string): void }} callbacks
 *   - `onFinish(name, identityKey)` called when CTA button is tapped with valid data.
 * @returns {void}
 */
export function attachListeners(callbacks) {
    const screen   = document.getElementById('screen-onboarding');
    const nameInput = document.getElementById('user-name');

    if (!screen) { return; }

    _clickHandler = (e) => {
        // Identity card selection
        const card = e.target.closest('.identity-card');
        if (card) {
            _selectIdentityCard(card);
            _updateObButton();
            return;
        }

        // CTA button
        if (e.target.id === 'ob-btn' || e.target.closest('#ob-btn')) {
            const { valid, name, identityKey } = validateReady();
            if (valid && callbacks && callbacks.onFinish) {
                callbacks.onFinish(name, identityKey);
            }
        }
    };

    _inputHandler = () => _updateObButton();

    screen.addEventListener('click', _clickHandler);
    if (nameInput) { nameInput.addEventListener('input', _inputHandler); }
}

// ─────────────────────────────────────────────
//  cleanup()
// ─────────────────────────────────────────────

/**
 * Remove all event listeners attached by `attachListeners()`.
 * Call this before navigating away from the onboarding screen.
 *
 * @returns {void}
 */
export function cleanup() {
    const screen    = document.getElementById('screen-onboarding');
    const nameInput = document.getElementById('user-name');

    if (screen && _clickHandler) {
        screen.removeEventListener('click', _clickHandler);
        _clickHandler = null;
    }
    if (nameInput && _inputHandler) {
        nameInput.removeEventListener('input', _inputHandler);
        _inputHandler = null;
    }

    _selectedIdentity = null;
}

// ─────────────────────────────────────────────
//  Public helpers
// ─────────────────────────────────────────────

/**
 * Return the currently selected identity key (or null).
 *
 * @returns {string|null}
 */
export function getSelectedIdentity() {
    return _selectedIdentity;
}

/**
 * Validate that both a name and an identity are selected.
 *
 * @returns {{ valid: boolean, name: string, identityKey: string|null }}
 */
export function validateReady() {
    const nameInput = document.getElementById('user-name');
    const name      = nameInput ? nameInput.value.trim() : '';
    const valid     = !!(name && _selectedIdentity);
    return { valid, name, identityKey: _selectedIdentity };
}

// ─────────────────────────────────────────────
//  Private helpers
// ─────────────────────────────────────────────

/**
 * Mark one identity card as selected and deselect all others.
 *
 * @param {HTMLElement} card  The `.identity-card` element to select.
 * @returns {void}
 */
function _selectIdentityCard(card) {
    document.querySelectorAll('#screen-onboarding .identity-card').forEach(c => {
        c.classList.remove('selected');
    });
    card.classList.add('selected');
    _selectedIdentity = card.dataset.id || null;
}

/**
 * Enable or disable the CTA button based on current validation state.
 *
 * @returns {void}
 */
function _updateObButton() {
    const btn = document.getElementById('ob-btn');
    if (!btn) { return; }
    const { valid } = validateReady();
    btn.disabled = !valid;
}

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
 *   - `validateReady()`      — returns { valid, name, identityKey, identityLabel, identityIcon }
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

/** Icons available for the custom identity picker. */
const CUSTOM_ICONS = ['⭐', '🌟', '💫', '🌈', '🎯', '🔑', '💡', '🌱', '🦋', '🌊', '🔮', '🎭'];

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
    { id: 'custom',  icon: '✏️', label: 'Custom',      sub: 'Define your own' },
];

// ─────────────────────────────────────────────
//  Module-level state
// ─────────────────────────────────────────────

/** Currently selected identity key (or null). */
let _selectedIdentity = null;

/** Custom identity label typed by the user. */
let _customLabel = '';

/** Custom identity icon selected by the user (default to first option). */
let _customIcon = CUSTOM_ICONS[0];

/** Reference to the delegated click handler for cleanup. */
let _clickHandler = null;

/** Reference to the input handler for the name field, for cleanup. */
let _inputHandler = null;

/** Reference to the input handler for the custom label field, for cleanup. */
let _customLabelHandler = null;

// ─────────────────────────────────────────────
//  render()
// ─────────────────────────────────────────────

/**
 * Return the full HTML string for the onboarding screen body.
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

    const customIconsHtml = CUSTOM_ICONS.map(icon => `
        <button class="ob-custom-icon-btn${icon === _customIcon ? ' selected' : ''}" data-icon="${icon}" type="button">${icon}</button>
    `).join('');

    return `
        <div class="ob-logo"><span>AtomicMe</span></div>
        <div class="ob-tagline">Tiny changes. Remarkable results.</div>

        <div class="ob-question">Who do you want to become?</div>
        <div class="ob-sub">Your habits are votes for your identity. Start with who, not what.</div>

        <div class="identity-grid" id="identity-grid">
            ${cardsHtml}
        </div>

        <div id="ob-custom-panel" style="display:none; margin-bottom: 1.5rem; background: #1A1F35; border: 2px solid #A855F7; border-radius: 1rem; padding: 1rem;">
            <div style="font-size: 0.78rem; color: #8B92AB; margin-bottom: 0.5rem;">Your identity label</div>
            <input type="text" id="ob-custom-label" placeholder='e.g. "The Focused Parent"' maxlength="30"
                style="width: 100%; background: #0F1221; border: 2px solid #2A3152; border-radius: 0.625rem; padding: 0.75rem 1rem; color: #EAEDF6; font-size: 0.92rem; font-family: inherit; outline: none; margin-bottom: 0.875rem;">
            <div style="font-size: 0.78rem; color: #8B92AB; margin-bottom: 0.5rem;">Choose an icon</div>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;" id="ob-custom-icons">
                ${customIconsHtml}
            </div>
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
 *
 * @param {{ onFinish: function(name: string, identity: string, identityLabel: string, identityIcon: string): void }} callbacks
 * @returns {void}
 */
export function attachListeners(callbacks) {
    const screen    = document.getElementById('screen-onboarding');
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

        // Custom icon button
        const iconBtn = e.target.closest('.ob-custom-icon-btn');
        if (iconBtn) {
            _customIcon = iconBtn.dataset.icon || CUSTOM_ICONS[0];
            document.querySelectorAll('.ob-custom-icon-btn').forEach(b => {
                b.classList.toggle('selected', b.dataset.icon === _customIcon);
            });
            _updateObButton();
            return;
        }

        // CTA button
        if (e.target.id === 'ob-btn' || e.target.closest('#ob-btn')) {
            const result = validateReady();
            if (result.valid && callbacks && callbacks.onFinish) {
                callbacks.onFinish(result.name, result.identityKey, result.identityLabel, result.identityIcon);
            }
        }
    };

    _inputHandler = () => _updateObButton();

    _customLabelHandler = (e) => {
        _customLabel = e.target.value.trim();
        _updateObButton();
    };

    screen.addEventListener('click', _clickHandler);
    if (nameInput) { nameInput.addEventListener('input', _inputHandler); }

    // Custom label input — attached after render since it's inside the panel
    _attachCustomLabelListener();
}

// ─────────────────────────────────────────────
//  cleanup()
// ─────────────────────────────────────────────

/**
 * Remove all event listeners attached by `attachListeners()`.
 *
 * @returns {void}
 */
export function cleanup() {
    const screen        = document.getElementById('screen-onboarding');
    const nameInput     = document.getElementById('user-name');
    const customInput   = document.getElementById('ob-custom-label');

    if (screen && _clickHandler) {
        screen.removeEventListener('click', _clickHandler);
        _clickHandler = null;
    }
    if (nameInput && _inputHandler) {
        nameInput.removeEventListener('input', _inputHandler);
        _inputHandler = null;
    }
    if (customInput && _customLabelHandler) {
        customInput.removeEventListener('input', _customLabelHandler);
        _customLabelHandler = null;
    }

    _selectedIdentity = null;
    _customLabel = '';
    _customIcon = CUSTOM_ICONS[0];
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
 * Validate that both a name and a valid identity are selected.
 * For the custom identity, a non-empty label is also required.
 *
 * @returns {{ valid: boolean, name: string, identityKey: string|null, identityLabel: string, identityIcon: string }}
 */
export function validateReady() {
    const nameInput = document.getElementById('user-name');
    const name      = nameInput ? nameInput.value.trim() : '';

    if (!name || !_selectedIdentity) {
        return { valid: false, name, identityKey: _selectedIdentity, identityLabel: '', identityIcon: '' };
    }

    if (_selectedIdentity === 'custom') {
        const label = _customLabel || (document.getElementById('ob-custom-label')?.value.trim() ?? '');
        if (!label) {
            return { valid: false, name, identityKey: 'custom', identityLabel: '', identityIcon: _customIcon };
        }
        return { valid: true, name, identityKey: 'custom', identityLabel: label, identityIcon: _customIcon };
    }

    const identity = IDENTITY_MAP[_selectedIdentity];
    return {
        valid: true,
        name,
        identityKey:   _selectedIdentity,
        identityLabel: identity.label,
        identityIcon:  identity.icon,
    };
}

// ─────────────────────────────────────────────
//  Private helpers
// ─────────────────────────────────────────────

/**
 * Mark one identity card as selected and deselect all others.
 * Shows or hides the custom panel as appropriate.
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

    const customPanel = document.getElementById('ob-custom-panel');
    if (customPanel) {
        customPanel.style.display = _selectedIdentity === 'custom' ? 'block' : 'none';
    }

    if (_selectedIdentity === 'custom') {
        // Re-attach the custom label listener in case the panel was just shown
        _attachCustomLabelListener();
        const customInput = document.getElementById('ob-custom-label');
        if (customInput) {
            _customLabel = customInput.value.trim();
            setTimeout(() => customInput.focus(), 50);
        }
    }
}

/**
 * Attach input listener to the custom label field if not already attached.
 *
 * @returns {void}
 */
function _attachCustomLabelListener() {
    const customInput = document.getElementById('ob-custom-label');
    if (customInput && _customLabelHandler) {
        // Remove first to avoid double-binding
        customInput.removeEventListener('input', _customLabelHandler);
        customInput.addEventListener('input', _customLabelHandler);
    }
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

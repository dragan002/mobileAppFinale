/**
 * AtomicMe — API Service Layer
 *
 * Centralises all fetch() calls to the Laravel JSON API. Every method returns a
 * standardised response envelope so callers never have to inspect raw fetch responses.
 *
 * Response envelope:
 *   { ok: true,  data: <parsed JSON> }   — success
 *   { ok: false, error: <Error object> } — network / HTTP error
 *
 * CSRF token is read automatically from <meta name="csrf-token"> on every call.
 *
 * Usage (once welcome.blade.php is converted to a module):
 *   import ApiService from './services/api.js';
 *   const { ok, data } = await ApiService.getState();
 */

/** @returns {string} CSRF token from the page meta tag, or empty string. */
function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

/**
 * Low-level fetch wrapper that sets shared headers and parses JSON.
 *
 * @param {string}      method  HTTP verb: 'GET' | 'POST' | 'PUT' | 'DELETE'.
 * @param {string}      url     Absolute or root-relative URL.
 * @param {Object|null} body    Optional JSON-serialisable request body.
 * @returns {Promise<{ ok: boolean, data?: any, error?: Error }>}
 */
async function request(method, url, body = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
    };

    if (body !== null) {
        options.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        const data = await response.json();
        return { ok: true, data };
    } catch (error) {
        return { ok: false, error };
    }
}

const ApiService = {
    /**
     * Fetch the full application state: user, habits, completions, streaks.
     *
     * @returns {Promise<{ ok: boolean, data?: import('../state/reducer').initialState, error?: Error }>}
     */
    getState() {
        return request('GET', '/api/state');
    },

    /**
     * Create or update the user profile (upsert at id=1).
     *
     * @param {{ name: string, identity: string, identityLabel: string, identityIcon: string }} profile
     * @returns {Promise<{ ok: boolean, data?: Object, error?: Error }>}
     */
    setupUser(profile) {
        return request('POST', '/api/setup', profile);
    },

    /**
     * Create a new habit.
     *
     * @param {Object} habitData  All habit fields from the 4-step add form.
     * @returns {Promise<{ ok: boolean, data?: Object, error?: Error }>}
     */
    createHabit(habitData) {
        return request('POST', '/api/habits', habitData);
    },

    /**
     * Update an existing habit by ID. Never modifies completions or streaks.
     *
     * @param {string|number} id         Habit ID.
     * @param {Object}        habitData  Fields to update.
     * @returns {Promise<{ ok: boolean, data?: Object, error?: Error }>}
     */
    updateHabit(id, habitData) {
        return request('PUT', `/api/habits/${id}`, habitData);
    },

    /**
     * Delete a habit. Cascades to completions on the server.
     *
     * @param {string|number} id  Habit ID.
     * @returns {Promise<{ ok: boolean, data?: Object, error?: Error }>}
     */
    deleteHabit(id) {
        return request('DELETE', `/api/habits/${id}`);
    },

    /**
     * Toggle today's completion for a habit.
     * Returns `{ completed, streak, streakData, milestone, achievement }`.
     *
     * @param {string|number} habitId
     * @returns {Promise<{ ok: boolean, data?: { completed: boolean, streak: number, streakData: Object, milestone: number|null, achievement: Object|null }, error?: Error }>}
     */
    toggleCompletion(habitId) {
        return request('POST', '/api/completions/toggle', { habit_id: habitId });
    },

    /**
     * Save or update a completion note for a habit on today's date.
     *
     * @param {string|number} habitId
     * @param {string}        note
     * @returns {Promise<{ ok: boolean, data?: Object, error?: Error }>}
     */
    saveCompletionNote(habitId, note) {
        return request('POST', '/api/completions/note', { habit_id: habitId, note });
    },

    /**
     * Upsert the weekly reflection for the given ISO week date.
     *
     * @param {string} weekOf  ISO date string for the start of the week (Monday).
     * @param {string} note    Reflection text (may be empty).
     * @returns {Promise<{ ok: boolean, data?: Object, error?: Error }>}
     */
    saveReflection(weekOf, note) {
        return request('POST', '/api/reflections', { week_of: weekOf, note });
    },

    /**
     * Fetch extended analytics data (weekly and monthly completion rates).
     *
     * @returns {Promise<{ ok: boolean, data?: { weeklyRates: number[], monthlyRates: number[] }, error?: Error }>}
     */
    getAnalytics() {
        return request('GET', '/api/analytics');
    },

    /**
     * Delete all user data: completions, habits, and user_profile.
     * Wipes in FK-safe order on the server.
     *
     * @returns {Promise<{ ok: boolean, data?: Object, error?: Error }>}
     */
    resetAll() {
        return request('DELETE', '/api/reset');
    },
};

export default ApiService;

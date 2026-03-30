/**
 * AtomicMe — State Reducer Module
 *
 * Pure functions for every state mutation. Each reducer takes the current state
 * (or a relevant slice of it) plus the action payload and returns a new state
 * object. No side effects, no API calls, no DOM access — fully unit-testable.
 *
 * Usage (once welcome.blade.php is converted to a module):
 *   import { addHabit, removeHabit, toggleCompletion, updateUser } from './state/reducer.js';
 */

/**
 * The canonical empty / default state shape.
 *
 * @returns {{ user: null, habits: Array, completions: Object, completionNotes: Object, streaks: Object, bestStreaks: Object, streakData: Object, bestStreakData: Object, categories: Array, achievements: Array }}
 */
export function initialState() {
    return {
        user: null,
        habits: [],
        completions: {},
        completionNotes: {},
        streaks: {},
        bestStreaks: {},
        streakData: {},
        bestStreakData: {},
        categories: [],
        achievements: [],
    };
}

/**
 * Merge a full server-side state snapshot into the current local state.
 *
 * @param {ReturnType<initialState>} state  Current local state.
 * @param {Object}                   data   Server response from GET /api/state.
 * @returns {ReturnType<initialState>}
 */
export function hydrateFromServer(state, data) {
    return {
        ...state,
        user: data.user,
        habits: data.habits,
        completions: data.completions,
        completionNotes: data.completionNotes || {},
        streaks: data.streaks,
        bestStreaks: data.bestStreaks,
        streakData: data.streakData || {},
        bestStreakData: data.bestStreakData || {},
        categories: data.categories || [],
        achievements: data.achievements || [],
    };
}

/**
 * Merge a persisted localStorage snapshot back into state, preserving any
 * newer fields added in later versions that the stored snapshot may not have.
 *
 * @param {ReturnType<initialState>} currentState
 * @param {Object}                   loaded  Parsed JSON from localStorage.
 * @returns {ReturnType<initialState>}
 */
export function mergeLocalSnapshot(currentState, loaded) {
    return {
        ...currentState,
        ...loaded,
        streakData: loaded.streakData || {},
        bestStreakData: loaded.bestStreakData || {},
        completionNotes: loaded.completionNotes || {},
        categories: loaded.categories || [],
        achievements: loaded.achievements || [],
    };
}

/**
 * Set (create or replace) the user profile.
 *
 * @param {ReturnType<initialState>} state
 * @param {{ name: string, identity: string, identityLabel: string, identityIcon: string }} user
 * @returns {ReturnType<initialState>}
 */
export function updateUser(state, user) {
    return { ...state, user };
}

/**
 * Append a new habit (typically the server-confirmed record) to the habits list,
 * replacing any existing entry with a matching `id` (handles temp-ID swap).
 *
 * @param {ReturnType<initialState>} state
 * @param {Object} habit  Habit object with at least an `id` field.
 * @returns {ReturnType<initialState>}
 */
export function addHabit(state, habit) {
    const exists = state.habits.findIndex(h => String(h.id) === String(habit.id));
    if (exists !== -1) {
        const habits = [...state.habits];
        habits[exists] = habit;
        return { ...state, habits };
    }
    return { ...state, habits: [...state.habits, habit] };
}

/**
 * Replace a temporary client-side habit ID with the real server-assigned ID.
 * Used during the optimistic-create flow when the POST /api/habits response arrives.
 *
 * @param {ReturnType<initialState>} state
 * @param {string|number}            tempId     The temporary ID (e.g. "tmp_1234").
 * @param {Object}                   realHabit  The server-confirmed habit record.
 * @returns {ReturnType<initialState>}
 */
export function replaceTempHabit(state, tempId, realHabit) {
    const habits = state.habits.map(h => String(h.id) === String(tempId) ? realHabit : h);
    const streaks = { ...state.streaks };
    const bestStreaks = { ...state.bestStreaks };
    const streakData = { ...state.streakData };
    const bestStreakData = { ...state.bestStreakData };

    delete streaks[tempId];
    delete streakData[tempId];

    const isFreq = (realHabit.targetDaysPerWeek || 7) < 7;
    streaks[realHabit.id] = 0;
    bestStreaks[realHabit.id] = 0;
    streakData[realHabit.id] = { value: 0, unit: isFreq ? 'weeks' : 'days', graceDayActive: false };
    bestStreakData[realHabit.id] = { value: 0, unit: isFreq ? 'weeks' : 'days' };

    return { ...state, habits, streaks, bestStreaks, streakData, bestStreakData };
}

/**
 * Update an existing habit's fields in place.
 *
 * @param {ReturnType<initialState>} state
 * @param {string|number}            id      Habit ID.
 * @param {Partial<Object>}          fields  Fields to merge into the habit.
 * @returns {ReturnType<initialState>}
 */
export function updateHabit(state, id, fields) {
    const habits = state.habits.map(h =>
        String(h.id) === String(id) ? { ...h, ...fields } : h
    );
    return { ...state, habits };
}

/**
 * Remove a habit and all associated streak / completion data.
 *
 * @param {ReturnType<initialState>} state
 * @param {string|number}            id  Habit ID to delete.
 * @returns {ReturnType<initialState>}
 */
export function removeHabit(state, id) {
    const habits = state.habits.filter(h => String(h.id) !== String(id));

    const completions = {};
    Object.keys(state.completions).forEach(date => {
        completions[date] = state.completions[date].filter(
            cid => String(cid) !== String(id)
        );
    });

    const streaks = { ...state.streaks };
    const bestStreaks = { ...state.bestStreaks };
    const streakData = { ...state.streakData };
    const bestStreakData = { ...state.bestStreakData };

    delete streaks[id];
    delete bestStreaks[id];
    delete streakData[id];
    delete bestStreakData[id];

    return { ...state, habits, completions, streaks, bestStreaks, streakData, bestStreakData };
}

/**
 * Toggle a habit completion for today.
 *
 * @param {ReturnType<initialState>} state
 * @param {string|number}            habitId
 * @param {string}                   dateKey  ISO date string e.g. "2026-03-30".
 * @returns {{ newState: ReturnType<initialState>, wasCompleted: boolean }}
 */
export function toggleCompletion(state, habitId, dateKey) {
    const completions = { ...state.completions };
    if (!completions[dateKey]) { completions[dateKey] = []; }

    const arr = [...completions[dateKey]];
    const idx = arr.map(String).indexOf(String(habitId));
    const wasCompleted = idx !== -1;

    const streaks = { ...state.streaks };

    if (wasCompleted) {
        arr.splice(idx, 1);
        streaks[habitId] = Math.max(0, (streaks[habitId] || 1) - 1);
    } else {
        arr.push(habitId);
        streaks[habitId] = (streaks[habitId] || 0) + 1;
    }

    completions[dateKey] = arr;

    return {
        newState: { ...state, completions, streaks },
        wasCompleted,
    };
}

/**
 * Apply the authoritative streak data returned from the server after a toggle.
 *
 * @param {ReturnType<initialState>} state
 * @param {string|number}            habitId
 * @param {number}                   streak       Current streak from server.
 * @param {Object|null}              streakData   Streak metadata from server.
 * @returns {ReturnType<initialState>}
 */
export function applyServerStreak(state, habitId, streak, streakData) {
    const streaks = { ...state.streaks, [habitId]: streak };
    const bestStreaks = { ...state.bestStreaks };

    if (!bestStreaks[habitId] || streak > bestStreaks[habitId]) {
        bestStreaks[habitId] = streak;
    }

    const newStreakData = streakData
        ? { ...state.streakData, [habitId]: streakData }
        : { ...state.streakData };

    return { ...state, streaks, bestStreaks, streakData: newStreakData };
}

/**
 * Add a completion note (optimistic).
 *
 * @param {ReturnType<initialState>} state
 * @param {string}                   key   Composite key "YYYY-MM-DD:habitId".
 * @param {string}                   note  The note text.
 * @returns {ReturnType<initialState>}
 */
export function addCompletionNote(state, key, note) {
    return {
        ...state,
        completionNotes: { ...state.completionNotes, [key]: note },
    };
}

/**
 * Mark an achievement as unlocked (optimistic, deduplicated).
 *
 * @param {ReturnType<initialState>} state
 * @param {{ code: string }}         achievement
 * @returns {ReturnType<initialState>}
 */
export function unlockAchievement(state, achievement) {
    const alreadyHas = (state.achievements || []).some(a => a.code === achievement.code);
    if (alreadyHas) { return state; }

    const newAchievement = {
        code: achievement.code,
        unlocked_at: new Date().toISOString().replace('T', ' ').slice(0, 19),
    };

    return { ...state, achievements: [...(state.achievements || []), newAchievement] };
}

/**
 * Wipe all state back to the initial empty shape.
 *
 * @returns {ReturnType<initialState>}
 */
export function resetState() {
    return initialState();
}

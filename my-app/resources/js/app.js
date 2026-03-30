/**
 * AtomicMe — Application Entry Point
 *
 * Imports all extracted screen and overlay modules, then exposes them as
 * window.App so the inline script in welcome.blade.php can delegate to them.
 *
 * This file is compiled by Vite and injected via @vite('resources/js/app.js').
 */

import './bootstrap';

// ── Screens ──────────────────────────────────────────────────────────────────
import * as ScreenOnboarding  from './screens/onboarding.js';
import * as ScreenHome        from './screens/home.js';
import * as ScreenStats       from './screens/stats.js';
import * as ScreenGrowth      from './screens/growth.js';
import * as ScreenAdd         from './screens/add.js';
import * as ScreenHabitDetail from './screens/habitDetail.js';

// ── Overlays ─────────────────────────────────────────────────────────────────
import * as OverlayMilestone    from './overlays/milestone.js';
import * as OverlayProfileSheet from './overlays/profileSheet.js';
import * as OverlayWeeklyReview from './overlays/weeklyReview.js';
import * as OverlayNoteSheet    from './overlays/noteSheet.js';

// ── Components ───────────────────────────────────────────────────────────────
import * as CompChart     from './components/chart.js';
import * as CompHabitCard from './components/habitCard.js';
import * as CompHeatmap   from './components/heatmap.js';
import * as CompStatRow   from './components/statRow.js';
import * as CompStreakCard from './components/streakCard.js';

// ─────────────────────────────────────────────────────────────────────────────
//  Global App namespace
//
//  All modules are collected under window.App so they can be called from the
//  inline <script> in welcome.blade.php without ES module import syntax.
//
//  Naming convention:
//    App.screens.home      → ScreenHome namespace
//    App.overlays.milestone → OverlayMilestone namespace
//    App.components.chart   → CompChart namespace
// ─────────────────────────────────────────────────────────────────────────────

window.App = {
    screens: {
        onboarding:  ScreenOnboarding,
        home:        ScreenHome,
        stats:       ScreenStats,
        growth:      ScreenGrowth,
        add:         ScreenAdd,
        habitDetail: ScreenHabitDetail,
    },

    overlays: {
        milestone:    OverlayMilestone,
        profileSheet: OverlayProfileSheet,
        weeklyReview: OverlayWeeklyReview,
        noteSheet:    OverlayNoteSheet,
    },

    components: {
        chart:      CompChart,
        habitCard:  CompHabitCard,
        heatmap:    CompHeatmap,
        statRow:    CompStatRow,
        streakCard: CompStreakCard,
    },
};

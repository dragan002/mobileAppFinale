# AtomicMe Product Roadmap

**Vision:** Expand AtomicMe from a daily tracker to a comprehensive habit platform that emphasizes compound growth (1% better daily = 37x better in 365 days). Compete with Atoms while maintaining the 4-Laws methodology as a core differentiator.

**Target:** Launch to friends/small circle first, get feedback, iterate for public release.

---

## Phase 1: Foundation Upgrades (Post-MVP, Post-Device Testing)

### 1. Category & Organization System

**What:** Organize habits by time/context with visual distinction.

**Preset Categories:**
- Morning Routine
- Evening Routine
- Health & Fitness
- Learning & Growth
- Work & Productivity
- Custom (user-created)

**Design:**
- Each category has a color
- Home screen shows habits grouped by category
- Category indicator on habit cards
- Filter/view by category in stats

**Database Changes:**
- Add `category_id` to `habits` table
- Create `categories` table (id, name, color, is_preset, user_profile_id)

**Frontend Changes:**
- Category selection in "Add Habit" form (step 1 or 2)
- Category badges on home screen
- Category filter in stats view

---

### 2. Per-Habit Reminders & Notifications

**What:** Push notifications at user-set times for each habit.

**Behavior:**
- Each habit has optional reminder time (e.g., "9pm")
- User toggles reminder on/off per habit
- Push notification fires at scheduled time
- In-app reminder card appears (persistent until dismissed or completed)
- Notification includes: habit name, category color, "Tap to complete"

**Technical:**
- Use NativePHP Mobile notification APIs
- Store `reminder_time` (nullable time) on `habits` table
- Background job or service worker to trigger at scheduled time
- localStorage cache of reminder preferences: `atomicme_reminders`

**Frontend Changes:**
- "Reminder" toggle in habit detail screen
- Time picker when user enables reminder
- In-app banner/card for active reminders

---

### 3. Compound Growth Analytics Dashboard

**What:** Visualize progress over time with compound growth insights (core differentiator).

**Metrics:**
1. **Week vs. Week:**
   - Compare current week vs. previous 4 weeks
   - Show completion % for each week
   - Trend line (up/down/flat)

2. **Month vs. Month:**
   - Compare current month vs. previous 11 months
   - Highlight best/worst months
   - % improvement month-over-month

3. **Consistency Score:**
   - Daily consistency % (habits completed today / habits assigned today)
   - Weekly consistency % (avg daily % for the week)
   - Monthly consistency % (avg daily % for the month)
   - All-time consistency %

4. **Compound Growth Projection (1% Rule):**
   - Calculate current average consistency (e.g., 80%)
   - Show: "At 80% consistency, you compound to **37x better in 365 days**"
   - Visual: Exponential curve chart (1.008^365 power law)
   - Update daily as consistency changes

5. **Heatmap (Enhanced):**
   - Keep existing 12-week heatmap
   - Add toggles for different date ranges (4-week, 8-week, 12-week, all-time)
   - Color intensity = consistency % (not just completed/not)

**Design:**
- New "Growth" tab/screen in main nav
- Show all metrics on one scrollable dashboard
- Highlight the "37x better" message prominently
- Compare habit-by-habit consistency

**Database Changes:**
- No new tables needed; derive from `completions` + `habits`

**Frontend Changes:**
- New "Growth" screen in sidebar
- Charts: week-over-week bar chart, month-over-month line chart, consistency gauge
- Exponential growth visualization
- Heatmap with date range filter

---

### 4. Enhanced Habit Detail Screen

**What:** Deepen the 4-Laws experience.

**Add:**
- Category badge with color
- "Your Setup" card (already exists) enhanced:
  - Show all 4 Laws clearly: Cue, Craving, Response, Reward
  - Make it editable (tap to edit)
- Consistency streak (separate from completion streak)
  - "You've completed this 42 days in a row"
  - "Your consistency with this: 87%"
- Habit history mini-chart
  - Last 4 weeks completion pattern

---

## Phase 2: Social & Engagement (Later)

### 1. Habit Stacking
- Allow linking habits: "After [Habit A], do [Habit B]"
- Show stacked habits on home screen
- Reward: completing first unlocks second visually

### 2. Social Sharing
- Share streak/milestone to friends
- View-only public profile link
- Optional: friend accountability (invite friend to see your streaks)

### 3. Community Insights (Future)
- Anonymous: "X% of users who picked this habit completed it this week"
- Benchmarking: "Your consistency with Morning Run is better than 72% of users"

---

## Phase 3: Advanced (Year 2+)

### 1. AI Insights
- Pattern detection: "You're weakest on Sundays with evening routine"
- Suggestions: "Try a 2-min warm-up instead of 10-min workout"
- Adaptive reminders: shift reminder time based on historical completion

### 2. Habit Recommendations
- Suggest next habits based on existing ones
- "Users who do Morning Run also like Cold Plunge"

### 3. Coaching Content
- Tie to Atomic Habits book chapters/concepts
- "Why your cue matters: chapter reference"
- Short video tips for common habits

---

## Tech Stack Implications

- **Database:** Add `categories` table, update `habits` schema
- **Notifications:** Leverage NativePHP Mobile native notification APIs
- **Frontend:** Add new "Growth" screen, enhance "Add Habit" form, detail screen
- **Backend:** New `StatsController` for analytics, background job for notifications
- **Testing:** Feature tests for category CRUD, notification triggers, analytics calculations

---

## Launch Sequence

1. **v2.0** (Post-MVP): Categories + Reminders
2. **v2.1** (2-3 weeks later): Compound Growth Dashboard
3. **v2.2** (Feedback-driven): Polish based on friend feedback
4. **v2.5** (Social): Habit stacking + optional sharing
5. **v3.0** (Public): Full feature set ready for App Store/Play Store

---

## Success Metrics

- Friends use app 4+ days/week
- Feedback: "I see my progress clearly" (compound growth resonates)
- No crashes on device testing
- Notifications feel helpful, not annoying

---

## Open Questions (Revisit Post-MVP)

- Should categories be mandatory or optional per habit?
- How granular should weekly review prompts be (still weekly, or more often)?
- Gamification: badges/streaks for hitting consistency milestones?
- Privacy: should users opt into analytics comparisons?


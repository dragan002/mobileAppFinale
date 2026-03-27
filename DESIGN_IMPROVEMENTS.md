# AtomicMe Design Improvements Spec

All changes apply to `my-app/resources/views/welcome.blade.php` (inline `<style>` and `<script>` blocks).

---

## 1. Habit Completion Checkbox — Size (Critical)

**Problem:** `.habit-check` is 28px — too small for a daily primary action.

**Changes:**
```css
.habit-check {
  width: 3rem;        /* was 1.75rem */
  height: 3rem;       /* was 1.75rem */
  border-radius: 50%;
  border: 2.5px solid #2a2a40;
  position: relative;
  overflow: visible;  /* needed for ripple pseudo-element */
}
```

Also make the **entire habit row** tappable for completion (not just the circle). The circle remains the visual indicator. In JS, move the `toggleHabit(id)` click handler from `.habit-check` to `.habit-item` (the whole row), and stop propagation if clicking delete.

---

## 2. Completion Animation — Ripple + Color Fill (Critical)

**Problem:** Current animation is a near-invisible scale bounce on a 28px circle.

**CSS additions:**
```css
/* Ripple ring that expands outward from the check circle on completion */
.habit-check::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  border: 2px solid #22c55e;
  transform: translate(-50%, -50%) scale(0.8);
  opacity: 0;
  pointer-events: none;
}

.habit-check.ripple::before {
  animation: ripple-out 400ms ease-out forwards;
}

@keyframes ripple-out {
  0%   { transform: translate(-50%, -50%) scale(0.8); opacity: 0.6; }
  100% { transform: translate(-50%, -50%) scale(2.5); opacity: 0; }
}

/* Smooth background + border fill on completion */
.habit-check {
  transition: background 150ms ease-out, transform 200ms ease-out, border-color 150ms;
}
```

**JS change:** In `toggleHabit()`, after toggling:
1. Add class `ripple` to the `.habit-check` element
2. Remove it after 400ms (`setTimeout(() => el.classList.remove('ripple'), 400)`)
3. The existing scale bounce animation is fine — keep it but on the larger 48px circle it will now be visible

---

## 3. Streak Visibility on Home Screen (Critical)

**Problem:** Streak text is 11px and has no visual weight. 30-day and 0-day streaks look identical.

**CSS changes:**
```css
.habit-streak {
  font-size: 0.78rem;   /* was 0.7rem */
  font-weight: 700;     /* was not bold */
}

/* Left accent border for streaks >= 7 */
.habit-item.streak-high {
  border-left: 3px solid var(--habit-color, #a78bfa);
  padding-left: calc(1rem - 3px);
}
```

**JS change:** When rendering habit rows, add class `streak-high` to `.habit-item` if `streaks[habit.id] >= 7`. Also set a CSS variable `--habit-color` on the element using the habit's color.

For streaks >= 7, apply gradient text to the streak number:
```css
.habit-item.streak-high .habit-streak {
  background: linear-gradient(90deg, #f97316, #f59e0b);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
```

---

## 4. Bottom Navigation — Sizing + Safe Area (High)

**Problem:** Icons at 19px, labels at under 10px, no iPhone safe-area handling.

**CSS changes:**
```css
.bottom-nav {
  padding: 0.75rem 0 max(1.1rem, env(safe-area-inset-bottom));
}

.nav-item {
  padding: 0.5rem 1.25rem;   /* was 0.25rem 1rem */
  font-size: 0.7rem;          /* was 0.62rem */
  gap: 0.3rem;
}

.nav-icon {
  font-size: 1.5rem;   /* was 1.2rem */
}

/* Active indicator dot below icon */
.nav-item.active::after {
  content: '';
  display: block;
  width: 4px;
  height: 4px;
  border-radius: 2px;
  background: #a78bfa;
  margin-top: 2px;
}
```

---

## 5. Empty State — Identity-Linked CTA (High)

**Problem:** "No habits yet" with a seed emoji is emotionally flat. New users land here after choosing their identity.

**JS change:** In the function that renders the empty state (when habits array is empty), replace the current markup with:

```html
<div class="empty-state">
  <div style="font-size: 4rem; margin-bottom: 1.5rem; text-align: center;">🌱✨</div>
  <h3 style="font-size: 1.1rem; font-weight: 700; color: #fff; margin-bottom: 0.5rem; text-align: center;">
    Your first vote for becoming ${state.user.identity || 'your best self'}
  </h3>
  <p style="font-size: 0.85rem; color: #888; margin-bottom: 1.5rem; line-height: 1.5; text-align: center;">
    Start with one tiny habit. Two minutes or less.
  </p>
  <button onclick="showScreen('screen-add')" class="btn-primary" style="width: 100%; max-width: 280px; margin: 0 auto; display: block;">
    Add Your First Habit
  </button>
</div>
```

**CSS addition:**
```css
.empty-state {
  padding: 3rem 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
}
```

---

## 6. Onboarding — Entrance Animations + Emotional Polish (High)

**Problem:** Identity cards just exist on screen. Selection feels like a form, not a choice.

**CSS additions:**
```css
/* Stagger-fade identity cards on load */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}

.identity-card {
  animation: fadeUp 300ms ease-out both;
}

.identity-card:nth-child(1) { animation-delay: 0ms; }
.identity-card:nth-child(2) { animation-delay: 50ms; }
.identity-card:nth-child(3) { animation-delay: 100ms; }
.identity-card:nth-child(4) { animation-delay: 150ms; }
.identity-card:nth-child(5) { animation-delay: 200ms; }
.identity-card:nth-child(6) { animation-delay: 250ms; }

/* Selected card feels alive */
.identity-card.selected {
  transform: scale(1.02);
  transition: transform 200ms ease-out, border-color 150ms, background 150ms;
}
```

**Copy change:** The name input label should read `"What should we call you?"` instead of `"Your first name"`.

**Heading size:** Increase `.ob-question` from `1.4rem` to `1.6rem`.

---

## 7. Progress Card — 100% Shimmer + Progress Bar (High)

**Problem:** When all habits are done, the card looks the same as when nothing is done.

**CSS additions:**
```css
@keyframes shimmer {
  0%   { background-position: -200% center; }
  100% { background-position: 200% center; }
}

.progress-card.all-done {
  background: linear-gradient(
    135deg,
    #1a0a2e 0%, #2d1b4e 25%, #1a0a2e 50%, #2d1b4e 75%, #1a0a2e 100%
  );
  background-size: 200% auto;
  animation: shimmer 3s linear infinite;
}

/* Thicker progress bar */
.progress-bar-wrap {
  height: 8px;   /* was 6px */
}

.progress-bar-fill {
  height: 8px;   /* was 6px */
}
```

**JS change:** After recalculating progress, if `done === total && total > 0`, add class `all-done` to `.progress-card`. Remove it otherwise.

---

## 8. Milestone Overlay — Entrance Animation + Stagger (High)

**Problem:** Overlay appears as a hard cut. Feels like a popup, not a celebration.

**CSS changes:**
```css
/* Overlay fade-in */
#milestone-overlay {
  /* Keep existing styles, add: */
  transition: opacity 300ms ease-out;
}

#milestone-overlay.visible {
  opacity: 1;
}

#milestone-overlay:not(.visible) {
  opacity: 0;
  pointer-events: none;
}

/* Staggered content entrance */
.milestone-emoji {
  animation: pop 400ms cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
}

.milestone-title {
  animation: fadeUp 300ms ease-out 150ms both;
}

.milestone-subtitle {
  animation: fadeUp 300ms ease-out 250ms both;
}

.milestone-quote {
  animation: fadeUp 300ms ease-out 400ms both;
}

.milestone-btn {
  animation: fadeUp 300ms ease-out 550ms both;
  /* Delay interactivity */
  pointer-events: none;
}

.milestone-btn.interactive {
  pointer-events: auto;
}

/* CSS confetti particles */
@keyframes confetti-pop {
  0%   { transform: translate(0, 0) scale(1); opacity: 1; }
  100% { transform: translate(var(--tx), var(--ty)) scale(0); opacity: 0; }
}

.confetti-particle {
  position: absolute;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  animation: confetti-pop 600ms ease-out forwards;
}
```

**JS change:** In `showMilestone()`:
1. Instead of setting `display: flex` directly, add class `visible` and remove `display: none` → use opacity transition
2. After 550ms, add class `interactive` to the dismiss button
3. Inject 8 `.confetti-particle` elements around the emoji with randomized `--tx` / `--ty` CSS variables (range: -80px to 80px) and colors from `['#a78bfa', '#f97316', '#22c55e', '#f59e0b', '#ec4899']`
4. Remove particles after 600ms

---

## 9. Habit Detail — Sticky Complete Button (Nice-to-have)

**Problem:** Complete button scrolls off screen on long habit detail pages.

**CSS change:**
```css
.detail-action-row {
  position: sticky;
  bottom: 0;
  padding: 1rem 1.25rem 1.5rem;
  background: linear-gradient(to top, #0a0a10 80%, transparent);
  margin: 0 -1.25rem -1.25rem;  /* bleed to screen edges */
}
```

---

## 10. Daily Quote — Reposition (Nice-to-have)

**Problem:** Daily quote is buried at the bottom of the home screen below the add button.

**Change:** In the home screen render function, move the quote block to appear **between** the progress card and the "Today's Habits" section header. No CSS changes needed — just DOM order.

---

## 11. Delete Confirmation — Bottom Sheet (Nice-to-have)

**Problem:** `window.confirm()` breaks immersive mobile feel.

**Replace** the `window.confirm()` in the delete habit flow with a custom bottom sheet:

```html
<!-- Add to DOM, hidden by default -->
<div id="delete-sheet" style="display:none; position:fixed; inset:0; z-index:100; background:rgba(0,0,0,0.6);">
  <div style="position:absolute; bottom:0; left:0; right:0; background:#1a1a2e; border-radius:1rem 1rem 0 0; padding:1.5rem 1.25rem max(1.5rem, env(safe-area-inset-bottom));">
    <p style="color:#fff; font-size:1rem; font-weight:600; margin-bottom:0.5rem;" id="delete-sheet-title">Delete habit?</p>
    <p style="color:#888; font-size:0.85rem; margin-bottom:1.5rem;">This will remove all completion history for this habit.</p>
    <button id="delete-sheet-confirm" class="btn-danger" style="width:100%; margin-bottom:0.75rem;">Delete</button>
    <button onclick="closeDeleteSheet()" style="width:100%; padding:0.75rem; background:transparent; border:1px solid #2a2a40; border-radius:0.75rem; color:#888; font-size:0.9rem; cursor:pointer;">Cancel</button>
  </div>
</div>
```

**JS additions:**
```js
let pendingDeleteId = null;

function showDeleteSheet(habitId, habitName) {
  pendingDeleteId = habitId;
  document.getElementById('delete-sheet-title').textContent = `Delete "${habitName}"?`;
  document.getElementById('delete-sheet').style.display = 'flex';
}

function closeDeleteSheet() {
  pendingDeleteId = null;
  document.getElementById('delete-sheet').style.display = 'none';
}

document.getElementById('delete-sheet-confirm').addEventListener('click', () => {
  if (pendingDeleteId) deleteHabit(pendingDeleteId);
  closeDeleteSheet();
});
```

Replace `window.confirm()` call with `showDeleteSheet(habit.id, habit.name)`.

---

## Implementation Order

1. Checkbox size (item 1)
2. Completion ripple animation (item 2)
3. Streak visibility (item 3)
4. Bottom nav sizing + safe area (item 4)
5. Empty state redesign (item 5)
6. Onboarding animations (item 6)
7. Progress card shimmer (item 7)
8. Milestone overlay entrance (item 8)
9. Sticky complete button (item 9)
10. Quote repositioning (item 10)
11. Delete bottom sheet (item 11)

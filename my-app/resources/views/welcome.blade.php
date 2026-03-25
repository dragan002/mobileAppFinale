<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>HabitFlow</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
            html { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; line-height: 1.5; }
            body { min-height: 100vh; background: #0f0f14; color: #fff; padding: 1.5rem; max-width: 420px; margin: 0 auto; }

            /* Header */
            .header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 0 1.5rem; }
            .header-left h1 { font-size: 1.5rem; font-weight: 700; }
            .header-left p { font-size: 0.8rem; color: #888; margin-top: 0.1rem; }
            .avatar { width: 2.5rem; height: 2.5rem; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #a855f7); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; }

            /* Progress summary */
            .summary { background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); border-radius: 1.25rem; padding: 1.5rem; margin-bottom: 1.5rem; }
            .summary-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.8; margin-bottom: 0.5rem; }
            .summary-count { font-size: 2.5rem; font-weight: 700; line-height: 1; }
            .summary-count span { font-size: 1rem; font-weight: 400; opacity: 0.8; }
            .progress-bar { background: rgba(255,255,255,0.2); border-radius: 999px; height: 6px; margin-top: 1rem; }
            .progress-fill { background: #fff; border-radius: 999px; height: 6px; width: 60%; }
            .summary-sub { font-size: 0.75rem; opacity: 0.7; margin-top: 0.5rem; }

            /* Section title */
            .section-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #888; margin-bottom: 0.75rem; }

            /* Habit items */
            .habits { display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem; }
            .habit { display: flex; align-items: center; gap: 1rem; background: #1a1a24; border-radius: 1rem; padding: 1rem; border: 1px solid #2a2a38; transition: border-color 0.2s; }
            .habit:hover { border-color: #444; }
            .habit-icon { width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
            .habit-info { flex: 1; }
            .habit-name { font-size: 0.95rem; font-weight: 600; }
            .habit-meta { font-size: 0.75rem; color: #888; margin-top: 0.15rem; }
            .habit-streak { display: flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; font-weight: 600; color: #f97316; }
            .check { width: 1.75rem; height: 1.75rem; border-radius: 50%; border: 2px solid #3a3a50; display: flex; align-items: center; justify-content: center; flex-shrink: 0; cursor: pointer; }
            .check.done { background: #22c55e; border-color: #22c55e; }
            .check.done::after { content: '✓'; color: white; font-size: 0.8rem; font-weight: 700; }

            /* Add button */
            .add-btn { width: 100%; padding: 1rem; background: #1a1a24; border: 2px dashed #2a2a38; border-radius: 1rem; color: #666; font-size: 0.9rem; font-family: inherit; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; transition: all 0.2s; }
            .add-btn:hover { border-color: #6366f1; color: #6366f1; }

            /* Bottom nav */
            .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 420px; background: #1a1a24; border-top: 1px solid #2a2a38; display: flex; justify-content: space-around; padding: 0.75rem 0 1.25rem; }
            .nav-item { display: flex; flex-direction: column; align-items: center; gap: 0.25rem; font-size: 0.65rem; color: #666; cursor: pointer; }
            .nav-item.active { color: #6366f1; }
            .nav-icon { font-size: 1.25rem; }

            body { padding-bottom: 5rem; }
        </style>
    </head>
    <body>
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>Good morning 👋</h1>
                <p>Tuesday, March 25</p>
            </div>
            <div class="avatar">JD</div>
        </div>

        <!-- Progress Summary -->
        <div class="summary">
            <p class="summary-title">Today's Progress</p>
            <p class="summary-count">3 <span>/ 5 habits</span></p>
            <div class="progress-bar"><div class="progress-fill"></div></div>
            <p class="summary-sub">You're doing great! 2 habits left.</p>
        </div>

        <!-- Habits List -->
        <p class="section-title">Today's Habits</p>
        <div class="habits">
            <div class="habit">
                <div class="habit-icon" style="background:#1e3a2f;">🏃</div>
                <div class="habit-info">
                    <p class="habit-name">Morning Run</p>
                    <p class="habit-meta">30 min · Every day</p>
                    <p class="habit-streak">🔥 12 day streak</p>
                </div>
                <div class="check done"></div>
            </div>

            <div class="habit">
                <div class="habit-icon" style="background:#1e2a3a;">💧</div>
                <div class="habit-info">
                    <p class="habit-name">Drink Water</p>
                    <p class="habit-meta">8 glasses · Every day</p>
                    <p class="habit-streak">🔥 7 day streak</p>
                </div>
                <div class="check done"></div>
            </div>

            <div class="habit">
                <div class="habit-icon" style="background:#2a1e3a;">📚</div>
                <div class="habit-info">
                    <p class="habit-name">Read a Book</p>
                    <p class="habit-meta">20 min · Every day</p>
                    <p class="habit-streak">🔥 5 day streak</p>
                </div>
                <div class="check done"></div>
            </div>

            <div class="habit">
                <div class="habit-icon" style="background:#3a2a1e;">🧘</div>
                <div class="habit-info">
                    <p class="habit-name">Meditation</p>
                    <p class="habit-meta">10 min · Every day</p>
                    <p class="habit-meta" style="color:#666;">Not done yet</p>
                </div>
                <div class="check"></div>
            </div>

            <div class="habit">
                <div class="habit-icon" style="background:#1e2e2e;">🥗</div>
                <div class="habit-info">
                    <p class="habit-name">Eat Healthy</p>
                    <p class="habit-meta">No junk food · Every day</p>
                    <p class="habit-meta" style="color:#666;">Not done yet</p>
                </div>
                <div class="check"></div>
            </div>
        </div>

        <button class="add-btn">+ Add News Habitsss</button>

        <!-- Bottom Nav -->
        <nav class="bottom-nav">
            <div class="nav-item active">
                <span class="nav-icon">🏠</span>
                <span>Today</span>
            </div>
            <div class="nav-item">
                <span class="nav-icon">📊</span>
                <span>Stats</span>
            </div>
            <div class="nav-item">
                <span class="nav-icon">🏆</span>
                <span>Streaks</span>
            </div>
            <div class="nav-item">
                <span class="nav-icon">⚙️</span>
                <span>Settings</span>
            </div>
        </nav>
    </body>
</html>

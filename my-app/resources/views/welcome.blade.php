<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FAF6F0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>AtomicMe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500&family=Inter+Tight:wght@400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500&family=Inter+Tight:wght@400;500;600;700&display=swap" rel="stylesheet"></noscript>
    <link rel="stylesheet" href="{{ asset('css/design-tokens.css') }}">
    @vite('resources/js/app.js')
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif; background: #0F1221; color: #EAEDF6; overflow: hidden; max-width: 100vw; overflow-x: hidden; }

        /* ── SCREENS ── */
        .screen { position: fixed; inset: 0; display: flex; flex-direction: column; overflow-y: auto; overflow-x: hidden; -webkit-overflow-scrolling: touch; opacity: 0; pointer-events: none; transform: translateX(30px); transition: opacity .3s, transform .3s; }
        .screen.active { opacity: 1; pointer-events: all; transform: translateX(0); }
        .screen.slide-left { transform: translateX(-30px); }

        /* ── BOOT / LOADING ── */
        #screen-boot { background: #0F1221; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1.25rem; }
        .boot-logo { font-size: 2rem; font-weight: 800; letter-spacing: -1px; }
        .boot-logo span { background: linear-gradient(135deg, #a78bfa, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .boot-dot { width: 10px; height: 10px; border-radius: 50%; background: #A855F7; animation: boot-pulse 1.4s ease-in-out infinite; }
        @keyframes boot-pulse { 0%, 100% { opacity: 0.2; transform: scale(0.85); } 50% { opacity: 1; transform: scale(1.15); } }
        .boot-label { font-size: 0.8rem; color: #5A6180; letter-spacing: 0.05em; }

        /* ── ONBOARDING ── */
        #screen-onboarding { background: linear-gradient(160deg, #0F1221 0%, #1a0a2e 100%); padding: max(2rem, env(safe-area-inset-top, 2rem)) 1.5rem calc(2rem + env(safe-area-inset-bottom, 1.5rem)); justify-content: flex-start; overflow-y: auto; -webkit-overflow-scrolling: touch; }
        .ob-logo { font-size: 2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.25rem; }
        .ob-logo span { background: linear-gradient(135deg, #a78bfa, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .ob-tagline { font-size: 0.85rem; color: #8B92AB; margin-bottom: 3rem; }
        .ob-question { font-size: 1.6rem; font-weight: 700; line-height: 1.3; margin-bottom: 0.5rem; }
        .ob-sub { font-size: 0.85rem; color: #8B92AB; margin-bottom: 2rem; }
        .identity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 2rem; }
        .identity-card { background: #1A1F35; border: 2px solid #2A3152; border-radius: 1rem; padding: 1.25rem 1rem; cursor: pointer; transition: all .2s; text-align: center; animation: fadeUp 300ms ease-out both; }
        .identity-card:active { transform: scale(0.97); }
        .identity-card.selected { border-color: #A855F7; background: #1f1535; transform: scale(1.02); transition: transform 200ms ease-out, border-color 150ms, background 150ms; }

        /* Stagger-fade identity cards on load */
        @keyframes fadeUp {
          from { opacity: 0; transform: translateY(8px); }
          to   { opacity: 1; transform: translateY(0); }
        }

        .identity-card:nth-child(1) { animation-delay: 0ms; }
        .identity-card:nth-child(2) { animation-delay: 50ms; }
        .identity-card:nth-child(3) { animation-delay: 100ms; }
        .identity-card:nth-child(4) { animation-delay: 150ms; }
        .identity-card:nth-child(5) { animation-delay: 200ms; }
        .identity-card:nth-child(6) { animation-delay: 250ms; }
        .identity-card .icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .identity-card .label { font-size: 0.85rem; font-weight: 600; color: #EAEDF6; }
        .identity-card .sub { font-size: 0.7rem; color: #8B92AB; margin-top: 0.2rem; }
        .ob-custom-icon-btn { background: #0F1221; border: 2px solid #2A3152; border-radius: 0.5rem; padding: 0.5rem; font-size: 1.3rem; cursor: pointer; transition: all .15s; text-align: center; width: 2.75rem; height: 2.75rem; display: flex; align-items: center; justify-content: center; }
        .ob-custom-icon-btn.selected { border-color: #A855F7; background: #1f1535; }
        .ob-name-wrap { margin-bottom: 1.5rem; }
        .ob-name-wrap label { font-size: 0.8rem; color: #8B92AB; display: block; margin-bottom: 0.5rem; }
        .ob-name-wrap input { width: 100%; background: #1A1F35; border: 2px solid #2A3152; border-radius: 0.75rem; padding: 0.875rem 1rem; color: #EAEDF6; font-size: 1rem; font-family: inherit; outline: none; }
        .ob-name-wrap input:focus { border-color: #A855F7; }
        .btn-primary { width: 100%; padding: 1rem; background: linear-gradient(135deg, #7c3aed, #db2777); border: none; border-radius: 0.875rem; color: #fff; font-size: 1rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: opacity .2s; }
        .btn-primary:active { opacity: 0.85; }
        .btn-primary:disabled { opacity: 0.4; cursor: default; }

        /* ── MAIN APP ── */
        #screen-home, #screen-stats, #screen-add, #screen-growth, #screen-achievements {
            padding-top: env(safe-area-inset-top, 0);
            padding-bottom: 5.5rem;
        }

        /* Header */
        .app-header { padding: max(1.25rem, calc(env(safe-area-inset-top, 0px) + 0.75rem)) 1.25rem 0.75rem; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
        .header-greeting h2 { font-size: 1.3rem; font-weight: 700; }
        .header-greeting p { font-size: 0.75rem; color: #8B92AB; margin-top: 0.1rem; }
        .avatar { width: 2.5rem; height: 2.5rem; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #db2777); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0; cursor: pointer; }

        /* Progress Card */
        .progress-card { margin: 0 1.25rem 1.25rem; background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 50%, #db2777 100%); border-radius: 1.25rem; padding: 1.25rem; position: relative; overflow: hidden; }
        .progress-card::before { content: ''; position: absolute; top: -30px; right: -30px; width: 100px; height: 100px; background: rgba(255,255,255,0.07); border-radius: 50%; }
        .progress-card::after { content: ''; position: absolute; bottom: -40px; left: -10px; width: 130px; height: 130px; background: rgba(255,255,255,0.05); border-radius: 50%; }
        .progress-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.75; margin-bottom: 0.4rem; }
        .progress-numbers { font-size: 2.25rem; font-weight: 700; line-height: 1; }
        .progress-numbers span { font-size: 1rem; font-weight: 400; opacity: 0.75; }
        .progress-bar-wrap { background: rgba(255,255,255,0.2); border-radius: 999px; height: 8px; margin-top: 1rem; }
        .progress-bar-fill { background: #C084FC; border-radius: 999px; height: 8px; transition: width .5s ease; box-shadow: 0 0 8px rgba(192, 132, 252, 0.4), 0 0 16px rgba(168, 85, 247, 0.2); }

        .progress-card.all-done {
          background: linear-gradient(
            135deg,
            #1a0a2e 0%, #2d1b4e 25%, #1a0a2e 50%, #2d1b4e 75%, #1a0a2e 100%
          );
          position: relative;
          overflow: hidden;
        }
        .progress-card.all-done::after {
          content: '';
          position: absolute;
          inset: 0;
          background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.08) 50%, transparent 100%);
          animation: shimmer-glow 3s ease-in-out infinite;
          pointer-events: none;
          will-change: opacity;
        }
        @keyframes shimmer-glow {
          0%, 100% { opacity: 0; }
          50% { opacity: 1; }
        }
        .progress-sub { font-size: 0.72rem; opacity: 0.7; margin-top: 0.5rem; }
        .identity-badge { display: inline-flex; align-items: center; gap: 0.35rem; background: rgba(255,255,255,0.15); border-radius: 999px; padding: 0.3rem 0.7rem; font-size: 0.7rem; font-weight: 600; margin-top: 0.75rem; margin-bottom: 1.25rem; }

        /* Section */
        .section-header { display: flex; align-items: center; justify-content: space-between; padding: 0 1.25rem; margin-bottom: 0.75rem; }
        .section-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #8B92AB; }
        .section-action { font-size: 0.75rem; color: #A855F7; cursor: pointer; }

        /* Habits */
        .habits-list { padding: 0 1.25rem; display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1rem; }
        .habit-item { display: flex; align-items: center; gap: 0.875rem; background: #1A1F35; border: 1px solid #2A3152; border-radius: 1rem; padding: 0.875rem; transition: border-color .2s, background .2s; cursor: pointer; }
        .habit-item.completed { background: #0d1a14; border-color: #1a3024; }
        .habit-item.at-risk { border-color: #f9731640; background: #14100a; }
        .habit-icon-wrap { width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; cursor: pointer; transition: transform .15s; }
        .habit-icon-wrap:active { transform: scale(0.92); }
        .habit-info { flex: 1; min-width: 0; cursor: pointer; }
        .habit-name { font-size: 0.92rem; font-weight: 600; }
        .habit-meta { font-size: 0.72rem; color: #8B92AB; margin-top: 0.15rem; }
        .habit-streak { font-size: 0.78rem; font-weight: 700; color: #f97316; margin-top: 0.2rem; }
        .habit-streak.at-risk-text { color: #f97316; }
        .habit-streak.grace-day-text { color: #A855F7; }

        /* Left accent border for streaks >= 7 */
        .habit-item.streak-high {
          border-left: 3px solid var(--habit-color, #a78bfa);
          padding-left: calc(1rem - 3px);
        }

        .habit-item.streak-high .habit-streak {
          background: linear-gradient(90deg, #f97316, #f59e0b);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
        }
        .habit-check { width: 3rem; height: 3rem; border-radius: 50%; border: 2.5px solid #2A3152; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background 150ms ease-out, transform 200ms ease-out, border-color 150ms; cursor: pointer; position: relative; overflow: visible; }
        .habit-check:active { transform: scale(0.88); }
        .habit-item.completed .habit-check { background: #34D399; border-color: #34D399; }
        .habit-item.completed .habit-check::after { content: '✓'; color: white; font-size: 0.75rem; font-weight: 700; }
        .habit-item.completed .habit-name { opacity: 0.6; text-decoration: line-through; }

        /* Ripple ring that expands outward from the check circle on completion */
        .habit-check::before {
          content: '';
          position: absolute;
          top: 50%;
          left: 50%;
          width: 100%;
          height: 100%;
          border-radius: 50%;
          border: 2px solid #34D399;
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

        /* Add button */
        .add-habit-btn { display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: calc(100% - 2.5rem); margin: 0 1.25rem; padding: 1rem; min-height: 3rem; background: transparent; border: 2px dashed #2A3152; border-radius: 1rem; color: #5A6180; font-size: 0.875rem; font-family: inherit; cursor: pointer; transition: all .2s; box-sizing: border-box; -webkit-tap-highlight-color: transparent; touch-action: manipulation; }
        .add-habit-btn:active { border-color: #A855F7; color: #C084FC; }

        /* Empty state */
        .empty-state {
          padding: 3rem 1.5rem;
          display: flex;
          flex-direction: column;
          align-items: center;
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 1rem; }
        .empty-state p { color: #8B92AB; font-size: 0.875rem; }

        /* Daily quote / identity motivation */
        .daily-quote { margin: 0.75rem 1.25rem 0; padding: 0.875rem 1rem; background: #1A1F35; border: 1px solid #2A3152; border-left: 3px solid #A855F7; border-radius: 0 0.75rem 0.75rem 0; font-size: 0.8rem; color: #EAEDF6; line-height: 1.6; }
        .daily-quote-label { display: block; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; color: #A855F7; font-style: normal; font-weight: 700; margin-bottom: 0.3rem; }

        /* Bottom Nav */
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: #242B45; border-top: 1px solid #2A3152; display: flex; justify-content: space-around; padding: 0.2rem 0 max(0.35rem, env(safe-area-inset-bottom)); z-index: 1000; flex-shrink: 0; width: 100%; }
        .bottom-nav.hidden { display: none; }
        .nav-item { display: flex; flex-direction: column; align-items: center; gap: 0.1rem; font-size: 0.6rem; color: #8B92AB; cursor: pointer; padding: 0.25rem 0; min-height: 36px; min-width: 44px; transition: color .2s; flex: 1; }
        .nav-item.active { color: #C084FC; }
        .nav-icon { font-size: 1.1rem; }

        /* Active indicator dot below icon */
        .nav-item.active::after {
          content: '';
          display: block;
          width: 4px;
          height: 4px;
          border-radius: 2px;
          background: #C084FC;
          margin-top: 2px;
        }

        /* ── ADD HABIT SCREEN ── */
        #screen-add { background: #0F1221; padding: 0; padding-bottom: calc(6.5rem + env(safe-area-inset-bottom, 0px)); }
        .add-header { padding: max(1.25rem, calc(env(safe-area-inset-top, 0px) + 0.75rem)) 1.25rem 1.25rem; display: flex; align-items: center; gap: 1rem; border-bottom: 1px solid #2A3152; flex-shrink: 0; }
        .back-btn { width: 2.75rem; height: 2.75rem; min-width: 44px; min-height: 44px; background: #242B45; border: none; border-radius: 0.625rem; color: #EAEDF6; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .add-header h2 { font-size: 1.1rem; font-weight: 700; flex: 1; }
        .add-body { padding: 1.25rem; flex: 1; overflow-y: auto; }

        /* Law steps */
        .law-steps { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; }
        .law-step { flex: 1; height: 3px; border-radius: 999px; background: #242B45; transition: background .3s; }
        .law-step.active { background: #A855F7; }
        .law-step.done { background: #34D399; }

        .law-badge { display: inline-flex; align-items: center; gap: 0.4rem; background: #242B45; border-radius: 999px; padding: 0.35rem 0.75rem; font-size: 0.72rem; font-weight: 600; color: #C084FC; margin-bottom: 0.75rem; }
        .law-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.35rem; }
        .law-desc { font-size: 0.8rem; color: #8B92AB; margin-bottom: 1.5rem; line-height: 1.5; }

        .form-group { margin-bottom: 1.25rem; }
        .form-label { font-size: 0.78rem; color: #8B92AB; margin-bottom: 0.5rem; display: block; }
        .form-input { width: 100%; background: #1A1F35; border: 2px solid #2A3152; border-radius: 0.75rem; padding: 0.875rem 1rem; color: #EAEDF6; font-size: 0.92rem; font-family: inherit; outline: none; transition: border-color .2s; }
        .form-input:focus { border-color: #A855F7; }
        .form-input::placeholder { color: #8B92AB; }
        textarea.form-input { resize: none; height: 80px; }

        .emoji-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 0.5rem; }
        .emoji-btn { background: #1A1F35; border: 2px solid #2A3152; border-radius: 0.625rem; padding: 0.6rem; font-size: 1.3rem; cursor: pointer; transition: all .15s; text-align: center; }
        .emoji-btn.selected { border-color: #A855F7; background: #1f1535; }

        .color-grid { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .color-btn { width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; border: 3px solid transparent; cursor: pointer; transition: all .15s; }
        .color-btn.selected { border-color: #fff; transform: scale(1.1); }

        .time-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
        .time-btn { background: #1A1F35; border: 2px solid #2A3152; border-radius: 0.75rem; padding: 0.75rem; text-align: center; cursor: pointer; transition: all .15s; }
        .time-btn.selected { border-color: #A855F7; background: #1f1535; }
        .time-btn .t-icon { font-size: 1.25rem; margin-bottom: 0.25rem; }
        .time-btn .t-label { font-size: 0.78rem; font-weight: 600; }
        .time-btn .t-sub { font-size: 0.65rem; color: #8B92AB; margin-top: 0.1rem; }

        .category-picker { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .category-pill { background: #1A1F35; border: 2px solid #2A3152; border-radius: 0.75rem; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all .15s; color: #EAEDF6; }
        .category-pill.selected { border-color: #A855F7; background: #1f1535; color: #A855F7; }

        .reward-input-wrap { position: relative; }
        .reward-input-wrap .reward-emoji { position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); font-size: 1.1rem; }
        .reward-input-wrap .form-input { padding-left: 2.75rem; }

        .nav-row { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
        .btn-secondary { flex: 1; padding: 0.875rem; background: #242B45; border: none; border-radius: 0.875rem; color: #8B92AB; font-size: 0.9rem; font-weight: 600; font-family: inherit; cursor: pointer; }
        .btn-next { flex: 2; padding: 0.875rem; background: linear-gradient(135deg, #7c3aed, #db2777); border: none; border-radius: 0.875rem; color: #fff; font-size: 0.9rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: opacity .2s; }
        .btn-next:active { opacity: 0.85; }

        /* ── STATS SCREEN ── */
        #screen-stats { background: #0F1221; }
        .stats-section { padding: 0 1.25rem 1.25rem; overflow-x: hidden; }
        .stats-card { background: #1A1F35; border: 1px solid #2A3152; border-radius: 1rem; padding: 1.25rem; margin-bottom: 0.75rem; overflow: hidden; }
        .stats-card h3 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #8B92AB; margin-bottom: 1rem; }
        .compound-chart { display: flex; align-items: flex-end; gap: 6px; height: 80px; }
        .compound-chart--labeled { height: 90px; }
        .compound-bar-wrap { flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: center; gap: 2px; height: 100%; }
        .compound-bar { flex: 1; min-width: 0; background: linear-gradient(to top, #7c3aed, #db2777); border-radius: 3px 3px 0 0; min-height: 3px; transition: height .5s ease; opacity: 0.85; align-self: stretch; }
        /* Inside the labeled compound chart, bars are fixed-height and sit at the bottom */
        .compound-bar-wrap .compound-bar { flex: none; width: 100%; opacity: 0.5; }
        /* When no YOU badge, the bar itself gets pushed to the bottom */
        .compound-bar-wrap > .compound-bar:last-child { margin-top: auto; }
        .compound-bar--active { opacity: 1 !important; box-shadow: 0 0 8px #a855f780; }
        .compound-bar-multiplier { font-size: 0.55rem; color: #5A6180; text-align: center; flex-shrink: 0; }
        .compound-bar-multiplier--active { color: #C084FC; font-weight: 700; }
        /* YOU badge pushes itself and the bar that follows to the bottom */
        .compound-bar-you { font-size: 0.42rem; font-weight: 800; color: #fff; background: #a855f7; border-radius: 3px; padding: 1px 4px; letter-spacing: 0.05em; white-space: nowrap; flex-shrink: 0; margin-top: auto; }
        .compound-footer { font-size: 0.72rem; color: #5A6180; margin: 0.5rem 0 0; line-height: 1.5; }
        .compound-footer strong { color: #C084FC; }
        .chart-labels { display: flex; justify-content: space-between; margin-top: 0.4rem; overflow: hidden; }
        .chart-labels span { font-size: 0.58rem; color: #5A6180; }
        .stats-row { display: flex; gap: 0.75rem; margin-bottom: 0.75rem; }
        .stat-box { flex: 1; background: #1A1F35; border: 1px solid #2A3152; border-radius: 1rem; padding: 1rem; text-align: center; }
        .stat-box .val { font-size: 2rem; font-weight: 700; color: #EAEDF6; background: linear-gradient(135deg, #C084FC, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-box .lbl { font-size: 0.7rem; color: #8B92AB; margin-top: 0.2rem; }
        /* Consistency Score card: 2×2 grid so "All-Time" is never cut off */
        #screen-growth .stats-card:first-child .stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0; }
        /* Slightly smaller value text inside the 2×2 grid to fit comfortably */
        #screen-growth .stats-card:first-child .stat-box .val { font-size: 1.5rem; }
        .identity-item { display: flex; align-items: center; gap: 0.875rem; padding: 0.75rem 0; border-bottom: 1px solid #2A3152; cursor: pointer; }
        .identity-item:last-child { border: none; }
        .identity-icon { font-size: 1.5rem; width: 2.25rem; text-align: center; flex-shrink: 0; }
        .identity-info { flex: 1; min-width: 0; }
        .identity-name { font-size: 0.875rem; font-weight: 600; }
        .identity-votes { font-size: 0.72rem; color: #8B92AB; margin-top: 0.15rem; }
        .identity-bar { height: 4px; background: #242B45; border-radius: 999px; margin-top: 0.5rem; overflow: hidden; }
        .identity-bar-fill { height: 4px; background: linear-gradient(135deg, #7c3aed, #db2777); border-radius: 999px; transition: width .6s ease; max-width: 100%; }
        .weekly-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.4rem; }
        .week-day { text-align: center; }
        .week-day-label { font-size: 0.7rem; color: #A3A8C1; font-weight: 500; margin-bottom: 0.4rem; }
        .week-dot { width: 100%; aspect-ratio: 1; border-radius: 0.35rem; background: #242B45; }
        .week-dot.done { background: linear-gradient(135deg, #7c3aed, #db2777); }
        .week-dot.partial { background: #3b1f6e; }

        /* ── HABIT CALENDAR ── */
        .cal-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
        .cal-nav-title { font-size: 0.88rem; font-weight: 700; color: #EAEDF6; }
        .cal-nav-btn { background: #242B45; border: none; color: #C084FC; font-size: 1.1rem; width: 2rem; height: 2rem; border-radius: 0.5rem; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .cal-nav-btn:active { opacity: 0.7; }
        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 3px; }
        .cal-day-label { text-align: center; font-size: 0.6rem; color: #5A6180; font-weight: 600; padding-bottom: 0.3rem; }
        .cal-cell { aspect-ratio: 1; border-radius: 0.35rem; background: #242B45; position: relative; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: opacity .15s; }
        .cal-cell:active { opacity: 0.75; }
        .cal-cell.cal-empty { background: transparent; cursor: default; pointer-events: none; }
        .cal-cell.cal-future { background: #1A1F35; opacity: 0.4; cursor: default; pointer-events: none; }
        .cal-cell.cal-all-done { background: linear-gradient(135deg, #7c3aed, #db2777); }
        .cal-cell.cal-partial { background: #3b1f6e; }
        .cal-cell.cal-today { outline: 2px solid #C084FC; outline-offset: 1px; }
        .cal-cell-num { font-size: 0.6rem; color: #8B92AB; line-height: 1; position: absolute; top: 2px; left: 3px; }
        .cal-cell.cal-all-done .cal-cell-num { color: rgba(255,255,255,0.8); }
        .cal-cell.cal-partial .cal-cell-num { color: rgba(255,255,255,0.7); }
        .cal-dots { position: absolute; bottom: 2px; display: flex; gap: 1px; justify-content: center; flex-wrap: wrap; width: 100%; padding: 0 2px; }
        .cal-dot { width: 3px; height: 3px; border-radius: 50%; background: rgba(255,255,255,0.6); flex-shrink: 0; }
        .cal-day-popup { background: #1A1F35; border: 1px solid #2A3152; border-radius: 0.75rem; padding: 0.75rem 1rem; margin-top: 0.75rem; display: none; }
        .cal-day-popup.show { display: block; }
        .cal-day-popup-date { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; color: #8B92AB; margin-bottom: 0.5rem; }
        .cal-day-popup-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; color: #EAEDF6; padding: 0.25rem 0; }
        .cal-day-popup-dot { width: 6px; height: 6px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #db2777); flex-shrink: 0; }
        .cal-day-popup-empty { font-size: 0.8rem; color: #5A6180; font-style: italic; }
        .cal-cell.cal-selected { outline: 2px solid #ec4899; outline-offset: 1px; }

        /* ── HABIT DETAIL SCREEN ── */
        #screen-habit-detail { background: #0F1221; padding: 0; }
        .detail-header { padding: max(1.25rem, calc(env(safe-area-inset-top, 0px) + 0.75rem)) 1.25rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; border-bottom: 1px solid #2A3152; flex-shrink: 0; }
        .detail-header h2 { font-size: 1rem; font-weight: 700; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .detail-delete-btn { background: none; border: none; color: #8B92AB; font-size: 1.1rem; cursor: pointer; padding: 0.5rem; min-width: 44px; min-height: 44px; display: flex; align-items: center; justify-content: center; }
        .detail-body { flex: 1; overflow-y: auto; padding-bottom: calc(2rem + env(safe-area-inset-bottom, 0px)); -webkit-overflow-scrolling: touch; }

        .streak-hero { text-align: center; padding: 2rem 1.25rem 1.5rem; }
        .streak-fire { font-size: 3.5rem; margin-bottom: 0.25rem; line-height: 1; }
        .streak-count-num { font-size: 5rem; font-weight: 800; background: linear-gradient(135deg, #a78bfa, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; letter-spacing: -2px; }
        .streak-label { font-size: 0.85rem; color: #8B92AB; margin-top: 0.35rem; }
        .milestone-badge-display { display: none; align-items: center; gap: 0.35rem; background: linear-gradient(135deg, #7c3aed22, #db287722); border: 1px solid #7c3aed55; border-radius: 999px; padding: 0.35rem 0.875rem; font-size: 0.78rem; font-weight: 600; color: #a78bfa; margin-top: 0.875rem; }

        .heatmap-wrap { padding: 0 1.25rem; margin-bottom: 0.75rem; }
        .heatmap-day-labels { display: flex; gap: 3px; margin-bottom: 4px; padding-left: 0; }
        .heatmap-grid { display: flex; gap: 3px; overflow-x: hidden; }
        .heatmap-col { display: flex; flex-direction: column; gap: 3px; flex: 1; }
        .heatmap-cell { width: 100%; aspect-ratio: 1; border-radius: 3px; background: #242B45; }
        .heatmap-cell.done { background: linear-gradient(135deg, #7c3aed, #db2777); }
        .heatmap-cell.today { outline: 2px solid #C084FC; outline-offset: 1px; }

        .insight-card { margin: 0 1.25rem 0.75rem; background: #1A1F35; border: 1px solid #2A3152; border-left: 3px solid #A855F7; border-radius: 0 0.75rem 0.75rem 0; padding: 0.875rem 1rem; font-size: 0.82rem; color: #8B92AB; line-height: 1.6; }
        .insight-card strong { color: #EAEDF6; }

        .detail-completion-chip { margin: 0.875rem 1.25rem 0; border-radius: 1rem; padding: 0.875rem 1.125rem; display: flex; align-items: center; gap: 0.75rem; cursor: pointer; transition: opacity .15s, transform .1s; user-select: none; -webkit-tap-highlight-color: transparent; }
        .detail-completion-chip:active { opacity: 0.8; transform: scale(0.98); }
        .detail-completion-chip.pending { background: #1A1F35; border: 1px dashed #3D4566; }
        .detail-completion-chip.done { background: rgba(52, 211, 153, 0.08); border: 1px solid rgba(52, 211, 153, 0.35); }
        .detail-completion-chip .chip-icon { width: 2rem; height: 2rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1rem; }
        .detail-completion-chip.pending .chip-icon { background: #242B45; border: 2px solid #3D4566; }
        .detail-completion-chip.done .chip-icon { background: rgba(52, 211, 153, 0.15); border: 2px solid #34D399; }
        .detail-completion-chip .chip-text { flex: 1; }
        .detail-completion-chip .chip-label { font-size: 0.9rem; font-weight: 600; }
        .detail-completion-chip.pending .chip-label { color: #8B92AB; }
        .detail-completion-chip.done .chip-label { color: #34D399; }
        .detail-completion-chip .chip-sub { font-size: 0.72rem; margin-top: 0.1rem; color: #5A6282; }
        .detail-completion-chip.done .chip-sub { color: rgba(52, 211, 153, 0.6); }

        .setup-card { margin: 0 1.25rem 0.75rem; background: #1A1F35; border: 1px solid #2A3152; border-radius: 1rem; padding: 1rem; }
        .setup-card-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: #8B92AB; margin-bottom: 0.875rem; }
        .setup-field { margin-bottom: 0.75rem; }
        .setup-field:last-child { margin-bottom: 0; }
        .setup-field-label { font-size: 0.7rem; color: #8B92AB; margin-bottom: 0.2rem; }
        .setup-field-value { font-size: 0.85rem; color: #8B92AB; line-height: 1.5; }

        /* ── REMINDER TOGGLE ── */
        .reminder-card { margin: 0 1.25rem 0.75rem; background: #1A1F35; border: 1px solid #2A3152; border-radius: 1rem; padding: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .reminder-card-info { flex: 1; min-width: 0; }
        .reminder-card-title { font-size: 0.88rem; font-weight: 600; color: #EAEDF6; }
        .reminder-card-sub { font-size: 0.72rem; color: #5A6180; margin-top: 0.2rem; }
        .reminder-toggle { position: relative; width: 2.75rem; height: 1.5rem; flex-shrink: 0; }
        .reminder-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
        .reminder-toggle-track { position: absolute; inset: 0; background: #2A3152; border-radius: 999px; cursor: pointer; transition: background .2s; }
        .reminder-toggle input:checked + .reminder-toggle-track { background: #A855F7; }
        .reminder-toggle-track::after { content: ''; position: absolute; top: 3px; left: 3px; width: 1.125rem; height: 1.125rem; background: #fff; border-radius: 50%; transition: transform .2s; }
        .reminder-toggle input:checked + .reminder-toggle-track::after { transform: translateX(1.25rem); }
        .reminder-permission-banner { margin: 0 1.25rem 0.75rem; background: #1A1F35; border: 1px solid #A855F744; border-radius: 1rem; padding: 0.875rem 1rem; display: none; align-items: center; gap: 0.75rem; }
        .reminder-permission-banner.show { display: flex; }
        .reminder-permission-banner-text { flex: 1; font-size: 0.8rem; color: #8B92AB; line-height: 1.5; }
        .reminder-permission-banner-text strong { color: #C084FC; display: block; margin-bottom: 0.15rem; }
        .btn-allow-notifs { background: linear-gradient(135deg, #7c3aed, #db2777); border: none; border-radius: 0.625rem; color: #fff; font-size: 0.8rem; font-weight: 600; font-family: inherit; padding: 0.5rem 0.875rem; cursor: pointer; white-space: nowrap; }

        /* ── MILESTONE OVERLAY ── */
        .milestone-overlay { position: fixed; inset: 0; background: rgba(15,18,33,0.96); z-index: 999; display: flex; align-items: center; justify-content: center; padding: max(2rem, env(safe-area-inset-top)) 1.5rem max(2rem, env(safe-area-inset-bottom)) 1.5rem; transition: opacity 300ms ease-out; }
        #milestone-overlay:not(.visible) { opacity: 0; pointer-events: none; }
        #milestone-overlay.visible { opacity: 1; }
        .milestone-content { text-align: center; max-width: 320px; width: 100%; }
        .milestone-emoji-big { font-size: 5rem; margin-bottom: 1rem; display: block; position: relative; }
        .milestone-title-text { font-size: 2.25rem; font-weight: 800; margin-bottom: 0.5rem; background: linear-gradient(135deg, #a78bfa, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: -1px; }
        .milestone-sub-text { font-size: 0.9rem; color: #8B92AB; margin-bottom: 1.5rem; line-height: 1.5; }
        .milestone-quote-text { font-size: 0.82rem; color: #C084FC; font-style: italic; margin-bottom: 2rem; line-height: 1.7; padding: 1rem; background: #1A1F35; border: 1px solid #2A3152; border-radius: 0.875rem; }
        @keyframes pop { 0% { transform: scale(0.5); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }

        /* Staggered milestone content entrance */
        .milestone-emoji { animation: pop 400ms cubic-bezier(0.175, 0.885, 0.32, 1.275) both; }
        .milestone-title { animation: fadeUp 300ms ease-out 150ms both; }
        .milestone-subtitle { animation: fadeUp 300ms ease-out 250ms both; }
        .milestone-quote { animation: fadeUp 300ms ease-out 400ms both; }
        .milestone-btn { animation: fadeUp 300ms ease-out 550ms both; pointer-events: none; }
        .milestone-btn.interactive { pointer-events: auto; }

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

        /* ── WEEKLY REVIEW OVERLAY ── */
        .weekly-review-overlay { position: fixed; inset: 0; background: rgba(15,18,33,0.97); z-index: 999; display: none; flex-direction: column; overflow-y: auto; padding: max(2rem, env(safe-area-inset-top)) 1.5rem max(2.5rem, env(safe-area-inset-bottom)) 1.5rem; }
        .weekly-review-overlay.show { display: flex; }
        .wr-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .wr-title { font-size: 1.4rem; font-weight: 800; background: linear-gradient(135deg, #a78bfa, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: -0.5px; }
        .wr-skip-btn { font-size: 0.8rem; color: #5A6180; background: none; border: none; font-family: inherit; cursor: pointer; padding: 0.5rem; }
        .wr-sub { font-size: 0.82rem; color: #8B92AB; margin-bottom: 1.5rem; line-height: 1.6; }
        .wr-section-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: #8B92AB; margin-bottom: 0.6rem; }
        .wr-habit-row { display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.875rem; background: #1A1F35; border: 1px solid #2A3152; border-radius: 0.75rem; margin-bottom: 0.5rem; }
        .wr-habit-left { display: flex; align-items: center; gap: 0.6rem; font-size: 0.875rem; font-weight: 600; }
        .wr-habit-pct { font-size: 0.75rem; font-weight: 700; }
        .wr-habit-pct.good { color: #34D399; }
        .wr-habit-pct.ok   { color: #f97316; }
        .wr-habit-pct.low  { color: #ef4444; }
        .wr-question { font-size: 0.92rem; font-weight: 600; color: #EAEDF6; margin: 1.25rem 0 0.6rem; line-height: 1.4; }
        .wr-textarea { width: 100%; min-height: 5rem; background: #1A1F35; border: 2px solid #2A3152; border-radius: 0.875rem; padding: 0.875rem 1rem; color: #EAEDF6; font-size: 0.9rem; font-family: inherit; outline: none; resize: none; line-height: 1.6; }
        .wr-textarea:focus { border-color: #A855F7; }
        .wr-save-btn { margin-top: 1.25rem; width: 100%; padding: 1rem; background: linear-gradient(135deg, #7c3aed, #db2777); border: none; border-radius: 0.875rem; color: #fff; font-size: 1rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: opacity .2s; }
        .wr-save-btn:active { opacity: 0.85; }

        /* Toast */
        .toast { position: fixed; top: 1rem; left: 50%; transform: translateX(-50%) translateY(-80px); background: #34D399; color: #fff; padding: 0.75rem 1.5rem; border-radius: 999px; font-size: 0.85rem; font-weight: 600; z-index: 1002; transition: transform .3s; white-space: nowrap; max-width: calc(100% - 2rem); text-align: center; }
        .toast.show { transform: translateX(-50%) translateY(0); }
        .toast.purple { background: linear-gradient(135deg, #7c3aed, #db2777); }

        /* ── FREQUENCY PICKER ── */
        .frequency-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.4rem; }
        .frequency-btn { background: #1A1F35; border: 2px solid #2A3152; border-radius: 0.75rem; padding: 0.75rem 0.25rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; color: #8B92AB; transition: all 0.2s; text-align: center; }
        .frequency-btn:active { transform: scale(0.95); }
        .frequency-btn.selected { border-color: #A855F7; background: #1f1535; color: #C084FC; }

        /* ── HEATMAP FREQUENCY STATES ── */
        .heatmap-cell.neutral { background: #1A1F35; border: 1px solid #242B45; }
        .heatmap-cell.missed  { background: #2d1515; }

        /* Scrollbar */
        ::-webkit-scrollbar { display: none; }
        * { -webkit-tap-highlight-color: transparent; }

        /* ── PROFILE SHEET ── */
        .profile-sheet-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 1000; opacity: 0; pointer-events: none; transition: opacity .3s; }
        .profile-sheet-backdrop.show { opacity: 1; pointer-events: all; }
        .profile-sheet { position: fixed; bottom: 0; left: 0; right: 0; background: #1A1F35; border-top: 1px solid #2A3152; border-radius: 1.5rem 1.5rem 0 0; z-index: 1001; max-height: 90vh; overflow-y: auto; -webkit-overflow-scrolling: touch; transform: translateY(100%); transition: transform .35s cubic-bezier(.32,0,.67,0); padding-bottom: calc(env(safe-area-inset-bottom, 1rem) + 1rem); }
        .profile-sheet.show { transform: translateY(0); transition-timing-function: cubic-bezier(.33,1,.68,1); }
        .profile-sheet-handle { width: 2.5rem; height: 4px; background: #2A3152; border-radius: 999px; margin: 0.875rem auto 0; }
        .profile-sheet-header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem 0.5rem; }
        .profile-sheet-title { font-size: 1.1rem; font-weight: 700; }
        .profile-sheet-close { background: #242B45; border: none; color: #8B92AB; font-size: 1rem; width: 2.75rem; height: 2.75rem; min-width: 44px; min-height: 44px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .profile-sheet-section { padding: 1rem 1.25rem 0; }
        .profile-sheet-section-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: #8B92AB; margin-bottom: 0.75rem; }
        .profile-identity-hero { display: flex; align-items: center; gap: 1rem; background: #242B45; border: 1px solid #2A3152; border-radius: 1rem; padding: 1rem; margin-bottom: 0.75rem; }
        .profile-identity-icon { font-size: 2.5rem; line-height: 1; flex-shrink: 0; }
        .profile-identity-info { flex: 1; min-width: 0; }
        .profile-identity-name { font-size: 1.1rem; font-weight: 700; }
        .profile-identity-label { font-size: 0.78rem; color: #C084FC; margin-top: 0.15rem; }
        .profile-stat-row { display: flex; gap: 0.6rem; margin-bottom: 0.75rem; }
        .profile-stat-box { flex: 1; background: #1A1F35; border: 1px solid #2A3152; border-radius: 1rem; padding: 0.875rem 0.75rem; text-align: center; }
        .profile-stat-val { font-size: 1.5rem; font-weight: 700; background: linear-gradient(135deg, #C084FC, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1.1; }
        .profile-stat-lbl { font-size: 0.7rem; color: #8B92AB; margin-top: 0.25rem; line-height: 1.3; }
        .profile-votes-line { background: #1A1F35; border: 1px solid #2A3152; border-left: 3px solid #A855F7; border-radius: 0 0.75rem 0.75rem 0; padding: 0.875rem 1rem; font-size: 0.82rem; color: #8B92AB; line-height: 1.6; margin-bottom: 0.75rem; }
        .profile-votes-line strong { color: #EAEDF6; }
        .profile-divider { height: 1px; background: #2A3152; margin: 0.5rem 0 1rem; }
        .profile-form-group { margin-bottom: 1rem; }
        .profile-form-label { font-size: 0.78rem; color: #8B92AB; margin-bottom: 0.5rem; display: block; }
        .profile-form-input { width: 100%; background: #1A1F35; border: 2px solid #2A3152; border-radius: 0.75rem; padding: 0.875rem 1rem; color: #EAEDF6; font-size: 0.92rem; font-family: inherit; outline: none; transition: border-color .2s; }
        .profile-form-input:focus { border-color: #A855F7; }
        .profile-identity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem; }
        .profile-identity-option { background: #1A1F35; border: 2px solid #2A3152; border-radius: 0.875rem; padding: 0.875rem 0.75rem; cursor: pointer; transition: all .2s; text-align: center; }
        .profile-identity-option:active { transform: scale(0.97); }
        .profile-identity-option.selected { border-color: #A855F7; background: #1f1535; }
        .profile-identity-option .pi-icon { font-size: 1.5rem; margin-bottom: 0.3rem; }
        .profile-identity-option .pi-label { font-size: 0.78rem; font-weight: 600; color: #EAEDF6; }
        .btn-save-profile { width: 100%; padding: 1rem; background: linear-gradient(135deg, #7c3aed, #db2777); border: none; border-radius: 0.875rem; color: #fff; font-size: 1rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: opacity .2s; margin-bottom: 0.75rem; }
        .btn-save-profile:active { opacity: 0.85; }
        .btn-reset-data { width: 100%; padding: 1rem; background: transparent; border: 2px solid #ef444444; border-radius: 0.875rem; color: #ef4444; font-size: 0.9rem; font-weight: 600; font-family: inherit; cursor: pointer; transition: all .2s; margin-bottom: 1.5rem; }
        .btn-reset-data:active { background: #ef444411; }

        /* ── ACHIEVEMENTS SCREEN ── */
        .achievement-card { display: flex; align-items: flex-start; gap: 1rem; background: #1A1F35; border: 1px solid #2A3152; border-radius: 1rem; padding: 1.25rem; margin: 0 1.25rem 0.75rem; transition: all 0.2s ease; }
        .achievement-icon { width: 3rem; height: 3rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; background: #242B45; }
        .achievement-card.unlocked .achievement-icon { background: linear-gradient(135deg, #7c3aed22, #db287722); border: 1px solid #7c3aed55; }
        .achievement-card.unlocked { border-left: 3px solid #A855F7; padding-left: calc(1.25rem - 3px); }
        .achievement-info { flex: 1; min-width: 0; }
        .achievement-name { font-size: 0.92rem; font-weight: 700; color: #EAEDF6; margin-bottom: 0.2rem; }
        .achievement-desc { font-size: 0.78rem; color: #8B92AB; line-height: 1.5; margin-bottom: 0.4rem; }
        .achievement-earned { font-size: 0.7rem; color: #34D399; font-weight: 600; }
        .achievement-card.locked { border-color: #1E2338; background: #151929; }
        .achievement-card.locked .achievement-icon { background: #1A1F35; filter: grayscale(1); }
        .achievement-card.locked .achievement-name { color: #8B92AB; }
        .achievement-criteria { font-size: 0.72rem; color: #5A6180; font-style: italic; margin-top: 0.25rem; }
        .achievement-progress-wrap { margin-top: 0.5rem; }
        .achievement-progress-label { font-size: 0.7rem; color: #8B92AB; margin-bottom: 0.3rem; }
        .achievement-progress-bar { height: 4px; background: #242B45; border-radius: 999px; overflow: hidden; }
        .achievement-progress-fill { height: 4px; background: linear-gradient(135deg, #7c3aed, #db2777); border-radius: 999px; transition: width 0.5s ease; }
        .achievement-card.prestige.unlocked { border-left-color: #F59E0B; border-color: #F59E0B33; background: linear-gradient(135deg, #1A1F35 0%, #1f1a0f 100%); }
        .achievement-card.prestige.unlocked .achievement-icon { background: linear-gradient(135deg, #F59E0B22, #EAB30822); border: 1px solid #F59E0B55; }
        .prestige-tag { display: inline-flex; align-items: center; gap: 0.2rem; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #F59E0B; background: #F59E0B18; border-radius: 4px; padding: 0.15rem 0.4rem; margin-left: 0.4rem; }
        .section-title.prestige { color: #F59E0B; }
    </style>
    <link rel="stylesheet" href="{{ asset('css/editorial.css') }}">
</head>
<body>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<!-- MILESTONE OVERLAY -->
<div class="milestone-overlay" id="milestone-overlay">
    <div class="milestone-content">
        <span class="milestone-emoji-big milestone-emoji" id="milestone-emoji">🏆</span>
        <div class="milestone-title-text milestone-title" id="milestone-title">30 Day Streak!</div>
        <div class="milestone-sub-text milestone-subtitle" id="milestone-sub">A full month of showing up. Incredible.</div>
        <div class="milestone-quote-text milestone-quote" id="milestone-quote">"You do not rise to the level of your goals. You fall to the level of your systems."</div>
        <button class="btn-primary milestone-btn" id="milestone-dismiss-btn" onclick="closeMilestone()">Keep Going! 🚀</button>
    </div>
</div>

<!-- WEEKLY REVIEW OVERLAY -->
<div class="weekly-review-overlay" id="weekly-review-overlay">
    <div class="wr-header">
        <div class="wr-title">Weekly Review 📋</div>
        <button class="wr-skip-btn" onclick="skipWeeklyReview()">Skip</button>
    </div>
    <div class="wr-sub">How did your week go? A moment of reflection makes next week stronger.</div>

    <div class="wr-section-label">This week's habits</div>
    <div id="wr-habit-list"></div>

    <div class="wr-question">What worked this week? What was hard?</div>
    <textarea class="wr-textarea" id="wr-note" placeholder="Write a short note... (optional)"></textarea>

    <button class="wr-save-btn" onclick="saveWeeklyReview()">Save Reflection</button>
</div>

<!-- ══════════════════ DELETE BOTTOM SHEET ══════════════════ -->
<div id="delete-sheet" style="display:none; position:fixed; inset:0; z-index:1100; background:rgba(0,0,0,0.6);">
  <div style="position:absolute; bottom:0; left:0; right:0; background:#1A1F35; border:1px solid #2A3152; border-radius:1rem 1rem 0 0; padding:1.5rem 1.25rem max(1.5rem, env(safe-area-inset-bottom));">
    <p style="color:#EAEDF6; font-size:1rem; font-weight:600; margin-bottom:0.5rem;" id="delete-sheet-title">Delete habit?</p>
    <p style="color:#8B92AB; font-size:0.85rem; margin-bottom:1.5rem;">This will remove all completion history for this habit.</p>
    <button id="delete-sheet-confirm" class="btn-danger" style="width:100%; margin-bottom:0.75rem; padding:0.875rem; background:linear-gradient(135deg,#ef4444,#dc2626); border:none; border-radius:0.75rem; color:#fff; font-size:0.95rem; font-weight:700; font-family:inherit; cursor:pointer;">Delete</button>
    <button onclick="closeDeleteSheet()" style="width:100%; padding:0.75rem; background:transparent; border:1px solid #2A3152; border-radius:0.75rem; color:#8B92AB; font-size:0.9rem; cursor:pointer; font-family:inherit;">Cancel</button>
  </div>
</div>

<!-- ══════════════════ PROFILE SHEET ══════════════════ -->
<div class="profile-sheet-backdrop" id="profile-sheet-backdrop" onclick="closeProfileSheet()"></div>
<div class="profile-sheet" id="profile-sheet">
    <div class="profile-sheet-handle"></div>
    <div class="profile-sheet-header">
        <div class="profile-sheet-title">Your Profile</div>
        <button class="profile-sheet-close" onclick="closeProfileSheet()">✕</button>
    </div>

    <!-- Identity Dashboard -->
    <div class="profile-sheet-section">
        <div class="profile-sheet-section-label">Identity</div>
        <div class="profile-identity-hero">
            <div class="profile-identity-icon" id="ps-identity-icon">🏃</div>
            <div class="profile-identity-info">
                <div class="profile-identity-name" id="ps-user-name">—</div>
                <div class="profile-identity-label" id="ps-identity-label">—</div>
            </div>
        </div>
        <div class="profile-stat-row">
            <div class="profile-stat-box">
                <div class="profile-stat-val" id="ps-days-using">0</div>
                <div class="profile-stat-lbl">Days using<br>AtomicMe</div>
            </div>
            <div class="profile-stat-box">
                <div class="profile-stat-val" id="ps-total-completions">0</div>
                <div class="profile-stat-lbl">Total<br>Completions</div>
            </div>
            <div class="profile-stat-box">
                <div class="profile-stat-val" id="ps-best-streak">0</div>
                <div class="profile-stat-lbl">Longest<br>Streak</div>
            </div>
        </div>
        <div class="profile-votes-line" id="ps-votes-line">
            You have cast <strong id="ps-votes-count">0</strong> votes for being <strong id="ps-votes-identity">—</strong>.
        </div>
    </div>

    <div class="profile-sheet-section">
        <div class="profile-divider"></div>
        <div class="profile-sheet-section-label">Edit Profile</div>

        <div class="profile-form-group">
            <label class="profile-form-label">Your name</label>
            <input type="text" class="profile-form-input" id="ps-name-input" maxlength="20" placeholder="Your name">
        </div>

        <div class="profile-form-group">
            <label class="profile-form-label">Your identity</label>
            <div class="profile-identity-grid" id="ps-identity-grid">
                <div class="profile-identity-option" data-id="athlete" onclick="selectProfileIdentity(this)">
                    <div class="pi-icon">🏃</div><div class="pi-label">The Athlete</div>
                </div>
                <div class="profile-identity-option" data-id="learner" onclick="selectProfileIdentity(this)">
                    <div class="pi-icon">📚</div><div class="pi-label">The Learner</div>
                </div>
                <div class="profile-identity-option" data-id="creator" onclick="selectProfileIdentity(this)">
                    <div class="pi-icon">🎨</div><div class="pi-label">The Creator</div>
                </div>
                <div class="profile-identity-option" data-id="mindful" onclick="selectProfileIdentity(this)">
                    <div class="pi-icon">🧘</div><div class="pi-label">The Mindful</div>
                </div>
                <div class="profile-identity-option" data-id="leader" onclick="selectProfileIdentity(this)">
                    <div class="pi-icon">🚀</div><div class="pi-label">The Leader</div>
                </div>
                <div class="profile-identity-option" data-id="healthy" onclick="selectProfileIdentity(this)">
                    <div class="pi-icon">🥗</div><div class="pi-label">The Healthy</div>
                </div>
                <div class="profile-identity-option" data-id="custom" onclick="selectProfileIdentity(this)">
                    <div class="pi-icon">✏️</div><div class="pi-label">Custom</div>
                </div>
            </div>

            <div id="ps-custom-panel" style="display:none; border-radius: 1rem; padding: 1rem; margin-top: 0.5rem;">
                <div style="font-size: 0.78rem; margin-bottom: 0.5rem;" class="custom-panel-label">Your identity label</div>
                <input type="text" id="ps-custom-label" class="profile-form-input" placeholder='e.g. "The Focused Parent"' maxlength="30" style="margin-bottom: 0.875rem;">
                <div style="font-size: 0.78rem; margin-bottom: 0.5rem;" class="custom-panel-label">Choose an icon</div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;" id="ps-custom-icons">
                    <button class="ob-custom-icon-btn selected" data-icon="⭐" type="button" onclick="selectPsCustomIcon(this)">⭐</button>
                    <button class="ob-custom-icon-btn" data-icon="🌟" type="button" onclick="selectPsCustomIcon(this)">🌟</button>
                    <button class="ob-custom-icon-btn" data-icon="💫" type="button" onclick="selectPsCustomIcon(this)">💫</button>
                    <button class="ob-custom-icon-btn" data-icon="🌈" type="button" onclick="selectPsCustomIcon(this)">🌈</button>
                    <button class="ob-custom-icon-btn" data-icon="🎯" type="button" onclick="selectPsCustomIcon(this)">🎯</button>
                    <button class="ob-custom-icon-btn" data-icon="🔑" type="button" onclick="selectPsCustomIcon(this)">🔑</button>
                    <button class="ob-custom-icon-btn" data-icon="💡" type="button" onclick="selectPsCustomIcon(this)">💡</button>
                    <button class="ob-custom-icon-btn" data-icon="🌱" type="button" onclick="selectPsCustomIcon(this)">🌱</button>
                    <button class="ob-custom-icon-btn" data-icon="🦋" type="button" onclick="selectPsCustomIcon(this)">🦋</button>
                    <button class="ob-custom-icon-btn" data-icon="🌊" type="button" onclick="selectPsCustomIcon(this)">🌊</button>
                    <button class="ob-custom-icon-btn" data-icon="🔮" type="button" onclick="selectPsCustomIcon(this)">🔮</button>
                    <button class="ob-custom-icon-btn" data-icon="🎭" type="button" onclick="selectPsCustomIcon(this)">🎭</button>
                </div>
            </div>
        </div>

        <button class="btn-save-profile" onclick="saveProfileChanges()">Save Changes</button>
    </div>

    <div class="profile-sheet-section">
        <div class="profile-divider"></div>
        <div class="profile-sheet-section-label">Danger Zone</div>
        <button class="btn-reset-data" onclick="confirmResetData()">Reset All Data</button>
    </div>
</div>

<!-- Note Input Sheet -->
<div class="profile-sheet-backdrop" id="note-sheet-backdrop" onclick="closeNoteSheet()"></div>
<div class="profile-sheet" id="note-sheet">
    <div class="profile-sheet-handle"></div>
    <div class="profile-sheet-header">
        <div class="profile-sheet-title">Add a Note</div>
        <button class="profile-sheet-close" onclick="closeNoteSheet()">✕</button>
    </div>

    <div class="profile-sheet-section" style="padding: 0 1.25rem;">
        <label class="profile-form-label" style="margin-bottom: 0.75rem;">Reflect on your completion:</label>
        <textarea id="note-input" placeholder="How did it feel? What did you notice? (optional)" style="width: 100%; border-radius: 0.75rem; padding: 0.75rem; font-family: inherit; font-size: 0.9rem; resize: vertical; min-height: 80px; outline: none;" maxlength="500"></textarea>
        <div style="font-size: 0.7rem; margin-top: 0.5rem;" class="note-sheet-hint">Max 500 characters</div>

        <button class="btn-save-profile" onclick="saveNote()" style="margin-top: 1rem;">Save Note</button>
        <button onclick="closeNoteSheet()" style="width: 100%; padding: 0.75rem; border-radius: 0.75rem; margin-top: 0.5rem; font-family: inherit; cursor: pointer;">Skip</button>
    </div>
</div>

<!-- ══════════════════ SCREEN: BOOT (loading) ══════════════════ -->
<div class="screen active" id="screen-boot">
    <div class="boot-logo"><span>AtomicMe</span></div>
    <div class="boot-dot"></div>
    <div class="boot-label">Loading...</div>
</div>

<!-- ══════════════════ SCREEN: ONBOARDING ══════════════════ -->
<div class="screen" id="screen-onboarding">
    <div class="ob-logo"><span>AtomicMe</span></div>
    <div class="ob-tagline">Tiny changes. Remarkable results.</div>

    <div class="ob-question">Who do you want to become?</div>
    <div class="ob-sub">Your habits are votes for your identity. Start with who, not what.</div>

    <div class="identity-grid" id="identity-grid">
        <div class="identity-card" data-id="athlete" onclick="selectIdentity(this)">
            <div class="icon">🏃</div><div class="label">The Athlete</div><div class="sub">Fit &amp; energetic</div>
        </div>
        <div class="identity-card" data-id="learner" onclick="selectIdentity(this)">
            <div class="icon">📚</div><div class="label">The Learner</div><div class="sub">Curious &amp; growing</div>
        </div>
        <div class="identity-card" data-id="creator" onclick="selectIdentity(this)">
            <div class="icon">🎨</div><div class="label">The Creator</div><div class="sub">Building &amp; making</div>
        </div>
        <div class="identity-card" data-id="mindful" onclick="selectIdentity(this)">
            <div class="icon">🧘</div><div class="label">The Mindful</div><div class="sub">Calm &amp; focused</div>
        </div>
        <div class="identity-card" data-id="leader" onclick="selectIdentity(this)">
            <div class="icon">🚀</div><div class="label">The Leader</div><div class="sub">Driven &amp; bold</div>
        </div>
        <div class="identity-card" data-id="healthy" onclick="selectIdentity(this)">
            <div class="icon">🥗</div><div class="label">The Healthy</div><div class="sub">Nourished &amp; strong</div>
        </div>
        <div class="identity-card" data-id="custom" onclick="selectIdentity(this)">
            <div class="icon">✏️</div><div class="label">Custom</div><div class="sub">Define your own</div>
        </div>
    </div>

    <div id="ob-custom-panel" style="display:none; margin-bottom: 1.5rem; border-radius: 1rem; padding: 1rem;">
        <div style="font-size: 0.78rem; margin-bottom: 0.5rem;" class="custom-panel-label">Your identity label</div>
        <input type="text" id="ob-custom-label" placeholder='e.g. "The Focused Parent"' maxlength="30" oninput="checkObReady()"
            style="width: 100%; border-radius: 0.625rem; padding: 0.75rem 1rem; font-size: 0.92rem; font-family: inherit; outline: none; margin-bottom: 0.875rem;">
        <div style="font-size: 0.78rem; margin-bottom: 0.5rem;" class="custom-panel-label">Choose an icon</div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;" id="ob-custom-icons">
            <button class="ob-custom-icon-btn selected" data-icon="⭐" type="button" onclick="selectObCustomIcon(this)">⭐</button>
            <button class="ob-custom-icon-btn" data-icon="🌟" type="button" onclick="selectObCustomIcon(this)">🌟</button>
            <button class="ob-custom-icon-btn" data-icon="💫" type="button" onclick="selectObCustomIcon(this)">💫</button>
            <button class="ob-custom-icon-btn" data-icon="🌈" type="button" onclick="selectObCustomIcon(this)">🌈</button>
            <button class="ob-custom-icon-btn" data-icon="🎯" type="button" onclick="selectObCustomIcon(this)">🎯</button>
            <button class="ob-custom-icon-btn" data-icon="🔑" type="button" onclick="selectObCustomIcon(this)">🔑</button>
            <button class="ob-custom-icon-btn" data-icon="💡" type="button" onclick="selectObCustomIcon(this)">💡</button>
            <button class="ob-custom-icon-btn" data-icon="🌱" type="button" onclick="selectObCustomIcon(this)">🌱</button>
            <button class="ob-custom-icon-btn" data-icon="🦋" type="button" onclick="selectObCustomIcon(this)">🦋</button>
            <button class="ob-custom-icon-btn" data-icon="🌊" type="button" onclick="selectObCustomIcon(this)">🌊</button>
            <button class="ob-custom-icon-btn" data-icon="🔮" type="button" onclick="selectObCustomIcon(this)">🔮</button>
            <button class="ob-custom-icon-btn" data-icon="🎭" type="button" onclick="selectObCustomIcon(this)">🎭</button>
        </div>
    </div>

    <div class="ob-name-wrap">
        <label>What should we call you?</label>
        <input type="text" id="user-name" placeholder="e.g. Alex" maxlength="20" oninput="checkObReady()">
    </div>

    <button class="btn-primary" id="ob-btn" disabled onclick="finishOnboarding()">Start My Journey →</button>
</div>

<!-- ══════════════════ SCREEN: HOME ══════════════════ -->
<div class="screen" id="screen-home">
    <div class="app-header">
        <div class="header-greeting">
            <h2 id="home-greeting">Good morning</h2>
            <p id="home-date"></p>
        </div>
        <div class="avatar" id="home-avatar" onclick="openProfileSheet()">?</div>
    </div>

    <div class="progress-card">
        <div class="progress-label">Today's Progress</div>
        <div class="progress-numbers"><span id="done-count">0</span> <span>/ <span id="total-count">0</span> habits</span></div>
        <div class="progress-bar-wrap"><div class="progress-bar-fill" id="progress-bar" style="width:0%"></div></div>
        <div class="progress-sub" id="progress-sub">Add your first habit to get started.</div>
        <div class="identity-badge" id="identity-badge">✨ Loading...</div>
    </div>

    <div class="daily-quote" id="daily-quote"></div>

    <div class="section-header">
        <span class="section-title">Today's Habits</span>
        <span class="section-action" onclick="showScreen('screen-add')">+ Add</span>
    </div>

    <div class="habits-list" id="habits-list"></div>
    <div id="empty-state" class="empty-state" style="display:none;">
        <div class="icon">🌱</div>
        <p>No habits yet.<br>Add your first atomic habit below.</p>
    </div>

    <button class="add-habit-btn" onclick="showScreen('screen-add')">
        <span style="font-size:1.1rem; pointer-events:none;">+</span><span style="pointer-events:none;">Add New Habit</span>
    </button>

    <div class="reminder-permission-banner" id="home-reminder-permission-banner" style="margin-top:0.75rem;">
        <div class="reminder-permission-banner-text">
            <strong>Enable reminders</strong>
            Never miss a habit — allow daily notifications.
        </div>
        <button class="btn-allow-notifs" onclick="requestNotificationPermission()">Allow</button>
    </div>
</div>

<!-- ══════════════════ SCREEN: ADD HABIT ══════════════════ -->
<div class="screen" id="screen-add">
    <div class="add-header">
        <button class="back-btn" id="add-back-btn" onclick="showScreen('screen-home')">←</button>
        <h2 id="add-screen-title">New Habit</h2>
    </div>
    <div class="add-body">
        <div class="law-steps">
            <div class="law-step active" id="step-0"></div>
            <div class="law-step" id="step-freq"></div>
            <div class="law-step" id="step-1"></div>
            <div class="law-step" id="step-2"></div>
            <div class="law-step" id="step-3"></div>
        </div>

        <!-- STEP 0: Make it Obvious -->
        <div id="add-step-0">
            <div class="law-badge">🔍 Law 1 of 4</div>
            <div class="law-title">Make it Obvious</div>
            <div class="law-desc">"Many people think they lack motivation when what they really lack is clarity." Define exactly what, when, and where.</div>

            <div class="form-group">
                <label class="form-label">What's the habit?</label>
                <input class="form-input" type="text" id="new-name" placeholder="e.g. Morning run, Read books, Meditate" maxlength="40">
            </div>

            <div class="form-group">
                <label class="form-label">Why does this matter to you?</label>
                <textarea class="form-input" id="new-why" placeholder="Your deeper reason — your 'why' makes hard days easier..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Choose an icon</label>
                <div class="emoji-grid">
                    <div class="emoji-btn selected" data-emoji="🏃" onclick="selectEmoji(this)">🏃</div>
                    <div class="emoji-btn" data-emoji="📚" onclick="selectEmoji(this)">📚</div>
                    <div class="emoji-btn" data-emoji="🧘" onclick="selectEmoji(this)">🧘</div>
                    <div class="emoji-btn" data-emoji="💪" onclick="selectEmoji(this)">💪</div>
                    <div class="emoji-btn" data-emoji="🥗" onclick="selectEmoji(this)">🥗</div>
                    <div class="emoji-btn" data-emoji="💧" onclick="selectEmoji(this)">💧</div>
                    <div class="emoji-btn" data-emoji="✍️" onclick="selectEmoji(this)">✍️</div>
                    <div class="emoji-btn" data-emoji="🎨" onclick="selectEmoji(this)">🎨</div>
                    <div class="emoji-btn" data-emoji="🎸" onclick="selectEmoji(this)">🎸</div>
                    <div class="emoji-btn" data-emoji="🌅" onclick="selectEmoji(this)">🌅</div>
                    <div class="emoji-btn" data-emoji="😴" onclick="selectEmoji(this)">😴</div>
                    <div class="emoji-btn" data-emoji="🧠" onclick="selectEmoji(this)">🧠</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Habit colour</label>
                <div class="color-grid" id="color-grid">
                    <div class="color-btn selected" data-color="#1e3a2f" style="background:#22c55e" onclick="selectColor(this)"></div>
                    <div class="color-btn" data-color="#1e1a3a" style="background:#7c3aed" onclick="selectColor(this)"></div>
                    <div class="color-btn" data-color="#1a2a3a" style="background:#3b82f6" onclick="selectColor(this)"></div>
                    <div class="color-btn" data-color="#3a1a1a" style="background:#ef4444" onclick="selectColor(this)"></div>
                    <div class="color-btn" data-color="#3a2a1a" style="background:#f97316" onclick="selectColor(this)"></div>
                    <div class="color-btn" data-color="#3a3a1a" style="background:#eab308" onclick="selectColor(this)"></div>
                    <div class="color-btn" data-color="#1a3a3a" style="background:#14b8a6" onclick="selectColor(this)"></div>
                    <div class="color-btn" data-color="#3a1a3a" style="background:#ec4899" onclick="selectColor(this)"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Category <span style="color:#5A6180;font-weight:400">(optional)</span></label>
                <div class="category-picker" id="category-picker"></div>
            </div>

            <div class="nav-row">
                <button class="btn-next" style="flex:1" onclick="goStep('freq')">Next: Set Frequency →</button>
            </div>
        </div>

        <!-- STEP FREQ: How Often? -->
        <div id="add-step-freq" style="display:none;">
            <div class="law-badge">🎯 Frequency</div>
            <div class="law-title">How Often?</div>
            <div class="law-desc">"Reduce the habit to its minimal version." How many days per week can you realistically commit to this?</div>

            <div class="form-group">
                <label class="form-label">Target days per week</label>
                <div class="frequency-grid" id="frequency-grid"></div>
            </div>

            <div class="form-group">
                <p style="font-size:0.78rem;color:#8B92AB;line-height:1.5;">
                    A consistent 3x/week habit beats an abandoned daily goal. Pick what you can actually stick to.
                </p>
            </div>

            <div class="nav-row">
                <button class="btn-secondary" onclick="goStep(0)">← Back</button>
                <button class="btn-next" onclick="goStep(1)">Next: Make it Attractive →</button>
            </div>
        </div>

        <!-- STEP 1: Make it Attractive -->
        <div id="add-step-1" style="display:none;">
            <div class="law-badge">✨ Law 2 of 4</div>
            <div class="law-title">Make it Attractive</div>
            <div class="law-desc">"The more attractive an opportunity is, the more likely it is to become habit-forming." Bundle it with something you love.</div>

            <div class="form-group">
                <label class="form-label">Temptation bundle — pair it with something you enjoy</label>
                <input class="form-input" type="text" id="new-bundle" placeholder="e.g. Only listen to my favourite podcast while running">
            </div>

            <div class="form-group">
                <label class="form-label">When will you do this?</label>
                <div class="time-grid" id="time-grid">
                    <div class="time-btn selected" data-time="morning" onclick="selectTime(this)">
                        <div class="t-icon">🌅</div><div class="t-label">Morning</div><div class="t-sub">Before 12pm</div>
                    </div>
                    <div class="time-btn" data-time="afternoon" onclick="selectTime(this)">
                        <div class="t-icon">☀️</div><div class="t-label">Afternoon</div><div class="t-sub">12pm – 5pm</div>
                    </div>
                    <div class="time-btn" data-time="evening" onclick="selectTime(this)">
                        <div class="t-icon">🌙</div><div class="t-label">Evening</div><div class="t-sub">After 5pm</div>
                    </div>
                    <div class="time-btn" data-time="anytime" onclick="selectTime(this)">
                        <div class="t-icon">🔄</div><div class="t-label">Anytime</div><div class="t-sub">Flexible</div>
                    </div>
                </div>
            </div>

            <div class="nav-row">
                <button class="btn-secondary" onclick="goStep('freq')">← Back</button>
                <button class="btn-next" onclick="goStep(2)">Next: Make it Easy →</button>
            </div>
        </div>

        <!-- STEP 2: Make it Easy -->
        <div id="add-step-2" style="display:none;">
            <div class="law-badge">⚡ Law 3 of 4</div>
            <div class="law-title">Make it Easy</div>
            <div class="law-desc">"The best way to start a new habit is to make it incredibly small." Use the 2-minute rule to get started.</div>

            <div class="form-group">
                <label class="form-label">The 2-minute version of this habit</label>
                <input class="form-input" type="text" id="new-two-min" placeholder="e.g. Put on running shoes and step outside">
            </div>

            <div class="form-group">
                <label class="form-label">Habit stack — after I do X, I will do this</label>
                <input class="form-input" type="text" id="new-stack" placeholder="e.g. After I pour my morning coffee...">
            </div>

            <div class="form-group">
                <label class="form-label">How long does it take? (optional)</label>
                <input class="form-input" type="text" id="new-duration" placeholder="e.g. 20 minutes, 5 reps">
            </div>

            <div class="nav-row">
                <button class="btn-secondary" onclick="goStep(1)">← Back</button>
                <button class="btn-next" onclick="goStep(3)">Next: Make it Satisfying →</button>
            </div>
        </div>

        <!-- STEP 3: Make it Satisfying -->
        <div id="add-step-3" style="display:none;">
            <div class="law-badge">🏆 Law 4 of 4</div>
            <div class="law-title">Make it Satisfying</div>
            <div class="law-desc">"What is immediately rewarded is repeated." Give yourself a tiny reward right after completing this habit.</div>

            <div class="form-group">
                <label class="form-label">How will you reward yourself?</label>
                <div class="reward-input-wrap">
                    <span class="reward-emoji">🎁</span>
                    <input class="form-input" type="text" id="new-reward" placeholder="e.g. 5 mins of my favourite show, a nice coffee...">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Difficulty level</label>
                <div class="time-grid" id="diff-grid">
                    <div class="time-btn" data-diff="easy" onclick="selectDiff(this)">
                        <div class="t-icon">🌱</div><div class="t-label">Easy</div><div class="t-sub">Just starting out</div>
                    </div>
                    <div class="time-btn selected" data-diff="medium" onclick="selectDiff(this)">
                        <div class="t-icon">🔥</div><div class="t-label">Medium</div><div class="t-sub">Goldilocks zone</div>
                    </div>
                    <div class="time-btn" data-diff="hard" onclick="selectDiff(this)">
                        <div class="t-icon">💎</div><div class="t-label">Hard</div><div class="t-sub">Push your edge</div>
                    </div>
                    <div class="time-btn" data-diff="custom" onclick="selectDiff(this)">
                        <div class="t-icon">🎯</div><div class="t-label">Custom</div><div class="t-sub">You decide</div>
                    </div>
                </div>
            </div>

            <div class="nav-row">
                <button class="btn-secondary" onclick="goStep(2)">← Back</button>
                <button class="btn-next" id="save-habit-btn" onclick="saveHabit()">✓ Create Habit</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════ SCREEN: STATS ══════════════════ -->
<div class="screen" id="screen-stats">
    <div class="app-header">
        <div class="header-greeting">
            <button onclick="showTab('screen-home', null)" class="back-to-today-btn" style="background:none;border:none;padding:0;font-size:0.8rem;font-weight:600;font-family:inherit;cursor:pointer;margin-bottom:0.2rem;display:block;">← Today</button>
            <h2>Your Progress</h2>
            <p>Every rep is a vote for who you're becoming.</p>
        </div>
        <div class="avatar" id="stats-avatar" onclick="openProfileSheet()">?</div>
    </div>

    <div class="stats-section">
        <div class="stats-row">
            <div class="stat-box"><div class="val" id="stat-streak">0</div><div class="lbl">Day Streak</div></div>
            <div class="stat-box"><div class="val" id="stat-total">0</div><div class="lbl">Total Done</div></div>
            <div class="stat-box"><div class="val" id="stat-rate">0%</div><div class="lbl">Today's Rate</div></div>
        </div>

        <div class="stats-card">
            <h3>The 1% Rule</h3>
            <p style="font-size:0.72rem;color:#5A6180;margin:-0.5rem 0 1rem;line-height:1.4;">Get 1% better each day. See where that puts you.</p>
            <div id="compound-section"></div>
        </div>

        <div class="stats-card">
            <h3>This Week</h3>
            <div class="weekly-grid" id="weekly-grid"></div>
        </div>

        <div class="stats-card" id="habit-breakdown-card" style="display:none;">
            <h3>Habit Streaks</h3>
            <div id="habit-breakdown"></div>
        </div>

        <div class="stats-card">
            <h3>Identity Votes</h3>
            <div id="identity-votes-list"></div>
        </div>

        <div class="stats-card">
            <h3>Monthly Calendar</h3>
            <div class="cal-nav">
                <button class="cal-nav-btn" onclick="calPrev()">‹</button>
                <span class="cal-nav-title" id="cal-month-title"></span>
                <button class="cal-nav-btn" onclick="calNext()" id="cal-next-btn">›</button>
            </div>
            <div class="cal-grid" id="cal-grid"></div>
            <div class="cal-day-popup" id="cal-day-popup">
                <div class="cal-day-popup-date" id="cal-popup-date"></div>
                <div id="cal-popup-items"></div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════ SCREEN: GROWTH ══════════════════ -->
<div class="screen" id="screen-growth">
    <div class="app-header">
        <div class="header-greeting">
            <button onclick="showTab('screen-home', null)" class="back-to-today-btn" style="background:none;border:none;padding:0;font-size:0.8rem;font-weight:600;font-family:inherit;cursor:pointer;margin-bottom:0.2rem;display:block;">← Today</button>
            <h2>Compound Growth</h2>
            <p>How consistent are you, really?</p>
        </div>
        <div class="avatar" id="growth-avatar" onclick="openProfileSheet()">?</div>
    </div>

    <div class="stats-section">
        <div class="stats-card">
            <h3>Consistency Score</h3>
            <div class="stats-row" style="margin-bottom:0">
                <div class="stat-box"><div class="val" id="cs-daily">0%</div><div class="lbl">Today</div></div>
                <div class="stat-box"><div class="val" id="cs-weekly">0%</div><div class="lbl">This Week</div></div>
                <div class="stat-box"><div class="val" id="cs-monthly">0%</div><div class="lbl">This Month</div></div>
                <div class="stat-box"><div class="val" id="cs-alltime">0%</div><div class="lbl">All-Time</div></div>
            </div>
        </div>

        <div class="stats-card">
            <h3>Week vs. Week</h3>
            <div class="compound-chart" id="growth-weekly-chart" style="height:80px; align-items:flex-end; gap:6px;"></div>
            <div class="chart-labels" id="growth-weekly-labels"></div>
        </div>

        <div class="stats-card">
            <h3>Month vs. Month</h3>
            <div class="compound-chart" id="growth-monthly-chart" style="height:80px; align-items:flex-end; gap:3px;"></div>
            <div class="chart-labels" id="growth-monthly-labels"></div>
        </div>

        <div class="stats-card">
            <h3>Habit Consistency</h3>
            <div id="growth-habits-list"></div>
        </div>
    </div>
</div>

<!-- ══════════════════ SCREEN: ACHIEVEMENTS ══════════════════ -->
<div class="screen" id="screen-achievements">
    <div class="app-header">
        <div class="header-greeting">
            <h2>Achievements</h2>
            <p>Unlock badges by mastering your habits.</p>
        </div>
        <div class="avatar" id="achievements-avatar" onclick="openProfileSheet()">?</div>
    </div>

    <div class="stats-section">
        <div id="achievements-empty-state" style="padding: 0 1.25rem; margin-bottom: 1.25rem; display: none;">
            <p style="font-size: 0.85rem; line-height: 1.6;" class="achievements-empty-text">
                Complete habits to unlock badges. Start with a <strong>Perfect Day</strong> — complete all your habits today.
            </p>
        </div>

        <div id="easy-achievements-section" style="display: none;">
            <div class="section-header">
                <div class="section-title">Easy Achievements</div>
            </div>
            <div id="easy-achievements-list"></div>
        </div>

        <div id="prestige-achievements-section" style="display: none;">
            <div class="section-header">
                <div class="section-title prestige">Prestige Achievements</div>
            </div>
            <div id="prestige-achievements-list"></div>
        </div>
    </div>
</div>

<!-- ══════════════════ SCREEN: HABIT DETAIL ══════════════════ -->
<div class="screen" id="screen-habit-detail">
    <div class="detail-header">
        <button class="back-btn" onclick="goBackFromDetail()">←</button>
        <h2 id="detail-title">Habit</h2>
        <button class="detail-edit-btn" onclick="showEditHabit(currentDetailHabitId)" title="Edit habit" style="background:none;border:none;font-size:0.85rem;font-weight:600;cursor:pointer;padding:0.25rem 0.5rem;">Edit</button>
        <button class="detail-delete-btn" onclick="deleteHabitFromDetail()" title="Delete habit">🗑</button>
    </div>
    <div class="detail-body">
        <div class="detail-completion-chip pending" id="detail-completion-chip" onclick="toggleHabitFromDetail()">
            <div class="chip-icon" id="detail-chip-icon"></div>
            <div class="chip-text">
                <div class="chip-label" id="detail-chip-label">Tap to mark complete</div>
                <div class="chip-sub" id="detail-chip-sub">Not done today</div>
            </div>
        </div>

        <div class="streak-hero">
            <div class="streak-fire" id="detail-fire">💤</div>
            <div class="streak-count-num" id="detail-streak-num">0</div>
            <div class="streak-label">day streak</div>
            <div class="milestone-badge-display" id="detail-milestone"></div>
        </div>

        <div class="stats-row" style="padding: 0 1.25rem; margin-bottom: 0.75rem;">
            <div class="stat-box"><div class="val" id="detail-best">0</div><div class="lbl">Best Streak</div></div>
            <div class="stat-box"><div class="val" id="detail-total">0</div><div class="lbl">Total Done</div></div>
            <div class="stat-box"><div class="val" id="detail-rate">0%</div><div class="lbl">30-day Rate</div></div>
        </div>

        <div class="phase-card" id="detail-phase-card" style="margin: 0 1.25rem 1rem; border-radius: 1rem; padding: 1rem; display: none;">
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <div id="detail-phase-icon" style="font-size: 1.5rem;">🌱</div>
                <div>
                    <div id="detail-phase-label" style="font-weight: 600; font-size: 0.95rem;" class="phase-label-text">Phase Label</div>
                    <div id="detail-phase-consistency" style="font-size: 0.7rem;" class="phase-consistency-text"></div>
                </div>
            </div>
            <div id="detail-phase-description" style="font-size: 0.8rem; line-height: 1.4;" class="phase-description-text">Phase description</div>
        </div>

        <div class="heatmap-wrap">
            <div class="stats-card" style="padding: 1rem;">
                <h3 style="margin-bottom: 0.75rem;">Last 12 Weeks</h3>
                <div class="heatmap-grid" id="detail-heatmap"></div>
            </div>
        </div>

        <div class="insight-card" id="detail-insight"></div>

        <div class="notes-timeline" id="detail-notes-timeline" style="padding: 0 1.25rem; margin-bottom: 1.25rem; display: none;">
            <h3 style="font-size: 0.9rem; font-weight: 600; margin-bottom: 0.75rem;">Your Journey</h3>
            <div id="detail-notes-list"></div>
        </div>

        <div class="setup-card" id="detail-setup" style="display:none;">
            <div class="setup-card-title">Your Setup</div>
            <div id="detail-setup-fields"></div>
        </div>

        <div class="reminder-permission-banner" id="reminder-permission-banner">
            <div class="reminder-permission-banner-text">
                <strong>Enable notifications</strong>
                Allow AtomicMe to send reminders so you never miss a habit.
            </div>
            <button class="btn-allow-notifs" onclick="requestNotificationPermission()">Allow</button>
        </div>

        <div class="reminder-card" id="detail-reminder-card">
            <div class="reminder-card-info">
                <div class="reminder-card-title">Daily Reminder</div>
                <div class="reminder-card-sub">
                    Remind me at <input type="time" id="detail-reminder-time" style="width:80px;border-radius:0.4rem;padding:0.25rem 0.5rem;font-family:inherit;font-size:0.85rem;" onchange="handleReminderTimeChange(this.value)">
                </div>
                <div style="font-size:0.68rem;color:#5A6180;margin-top:0.35rem;">Push notifications coming soon</div>
            </div>
            <label class="reminder-toggle">
                <input type="checkbox" id="detail-reminder-toggle" onchange="handleReminderToggle(this.checked)">
                <span class="reminder-toggle-track"></span>
            </label>
        </div>

    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     SHARED BOTTOM NAVIGATION (outside all screens, always visible)
     ═══════════════════════════════════════════════════════════ -->
<nav class="bottom-nav">
    <div class="nav-item active" data-tab="screen-home" onclick="showTab('screen-home', this)"><span class="nav-icon">🏠</span><span>Today</span></div>
    <div class="nav-item" data-tab="screen-stats" onclick="showTab('screen-stats', this)"><span class="nav-icon">📊</span><span>Stats</span></div>
    <div class="nav-item" data-tab="screen-growth" onclick="showTab('screen-growth', this)"><span class="nav-icon">📈</span><span>Growth</span></div>
    <div class="nav-item" data-tab="screen-achievements" onclick="showTab('screen-achievements', this)"><span class="nav-icon">🏅</span><span>Badges</span></div>
</nav>

<script>
// ══════════════════════════════════════════
//  CONSTANTS
// ══════════════════════════════════════════

/** @type {Object<string, { label: string, icon: string }>} Maps identity key → display label + emoji. */
const IDENTITY_MAP = {
    athlete: { label: 'The Athlete',  icon: '🏃' },
    learner: { label: 'The Learner',  icon: '📚' },
    creator: { label: 'The Creator',  icon: '🎨' },
    mindful: { label: 'The Mindful',  icon: '🧘' },
    leader:  { label: 'The Leader',   icon: '🚀' },
    healthy: { label: 'The Healthy',  icon: '🥗' },
};

const MILESTONES = [
    { days: 7,   emoji: '✨', title: '7-Day Streak!',   sub: "You're building momentum. The habit loop is activating.",            quote: "Success is the product of daily habits—not once-in-a-lifetime transformations." },
    { days: 14,  emoji: '🔥', title: '2-Week Streak!',  sub: "The habit loop is forming. Your brain is changing.",                 quote: "Every action you take is a vote for the type of person you wish to become." },
    { days: 21,  emoji: '💪', title: '21-Day Streak!',  sub: "Science says it's becoming automatic. You've crossed a threshold.",  quote: "Habits are the compound interest of self-improvement." },
    { days: 30,  emoji: '🏆', title: '30-Day Champion!',sub: "A full month of showing up. This is extraordinary.",                  quote: "You do not rise to the level of your goals. You fall to the level of your systems." },
    { days: 60,  emoji: '⚡', title: '60 Days Strong!', sub: "You're in the top 1% of habit-keepers on the planet.",               quote: "The most effective form of motivation is progress." },
    { days: 90,  emoji: '💎', title: '90-Day Legend!',  sub: "This is no longer something you do. It's who you are.",              quote: "The secret to getting results that last is to never stop making improvements." },
    { days: 100, emoji: '🌟', title: '100 Days!!!',     sub: "Legendary. Truly legendary. You are living proof it works.",         quote: "Small changes often appear to make no difference until you cross a critical threshold." },
];

const WEEKLY_MILESTONES = [
    { weeks: 4,  emoji: '🎯', title: '4 Weeks Consistent!', sub: "One full month of showing up. This habit is taking hold.",         quote: "You don't have to be the victim of your environment. You can also be the architect of it." },
    { weeks: 9,  emoji: '💪', title: '9 Weeks — Habit Forming!', sub: "Research shows habits solidify around the 66-day mark. You're there.", quote: "Habits are the compound interest of self-improvement." },
    { weeks: 13, emoji: '🏆', title: 'Quarter Year!',         sub: "13 weeks of consistent effort. A full quarter of real change.",   quote: "Every action you take is a vote for the type of person you wish to become." },
    { weeks: 26, emoji: '⚡', title: 'Halfway to a Year!',    sub: "26 weeks. You're unstoppable. Half a year of identity votes.",    quote: "The most effective form of motivation is progress." },
    { weeks: 52, emoji: '👑', title: 'A Full Year!',          sub: "52 weeks of consistency. You ARE this habit. Crown deserved.",    quote: "Small changes often appear to make no difference until you cross a critical threshold." },
];

const ACHIEVEMENTS_DEFS = {
    perfect_day:      { name: 'Perfect Day',      icon: '⭐',  desc: 'Complete all habits in one day',              prestige: false, criteria: 'Complete all your habits today' },
    perfect_week:     { name: 'Perfect Week',     icon: '📅',  desc: 'Complete all habits for all 7 days',          prestige: false, criteria: 'Complete all habits every day this week' },
    habit_builder:    { name: 'Habit Builder',    icon: '🔨',  desc: 'Create your 3rd and 5th habits',             prestige: false, criteria: 'Create 3+ habits' },
    comeback:         { name: 'Comeback',         icon: '🔥',  desc: 'Rebuild a streak after it broke',            prestige: false, criteria: 'Break a streak, then rebuild it' },
    streak_30:        { name: '30-Day Streak',    icon: '🏆',  desc: 'Reach a 30-day streak on any habit',         prestige: false, criteria: 'Achieve a 30-day streak on any habit' },
    streak_60:        { name: '60-Day Streak',    icon: '⚡',  desc: 'Reach a 60-day streak on any habit',         prestige: false, criteria: 'Achieve a 60-day streak on any habit' },
    one_percent_club: { name: 'The 1% Club',      icon: '💎',  desc: '365 consecutive days on one habit',          prestige: true,  criteria: 'Achieve 365-day streak on any habit' },
    atomic_identity:  { name: 'Atomic Identity',  icon: '⚛️', desc: 'All habits in Identity phase',               prestige: true,  criteria: 'Reach Identity phase on all habits' },
    perfect_quarter:  { name: 'Perfect Quarter',  icon: '👑',  desc: '90 days straight, zero missed days',         prestige: true,  criteria: 'Complete 90 days with no grace days used' },
};

const QUOTES = [
    "You do not rise to the level of your goals. You fall to the level of your systems.",
    "Every action you take is a vote for the type of person you wish to become.",
    "Habits are the compound interest of self-improvement.",
    "The most effective form of motivation is progress.",
    "Small changes often appear to make no difference until you cross a critical threshold.",
    "Success is the product of daily habits—not once-in-a-lifetime transformations.",
    "The purpose of setting goals is to win the game. The purpose of building systems is to continue playing.",
    "You don't have to be the victim of your environment. You can also be the architect of it.",
    "Be the designer of your world and not merely the consumer of it.",
    "Until you make the unconscious conscious, it will direct your life and you will call it fate.",
];

// ══════════════════════════════════════════
//  STATE
// ══════════════════════════════════════════

/**
 * Global application state. Single source of truth for all screens.
 * Persisted to localStorage under the key `atomicme_v3` as a JSON cache and
 * synced from `/api/state` on every `init()` call.
 *
 * Shape:
 *   user            - null | object (name, identity, identityLabel, identityIcon)
 *   habits          - Array of habit objects
 *   completions     - Object keyed by "YYYY-MM-DD" → Array of habit IDs
 *   completionNotes - Object keyed by "YYYY-MM-DD:habitId" → note string
 *   streaks         - Object keyed by habitId → current streak integer
 *   bestStreaks     - Object keyed by habitId → best streak integer
 *   streakData      - Object keyed by habitId → (value, unit, graceDayActive)
 *   bestStreakData  - Object keyed by habitId → (value, unit)
 *   categories      - Array of category objects
 *   achievements    - Array of (code, unlocked_at) objects
 */
let state = {
    user: null,
    habits: [],
    completions: {},       // { 'YYYY-MM-DD': [habitId, ...] }
    completionNotes: {},   // { 'YYYY-MM-DD:habitId': 'note text' }
    streaks: {},           // { habitId: n }     — plain integer for backward compat
    bestStreaks: {},       // { habitId: n }
    streakData: {},        // { habitId: { value, unit, graceDayActive } }
    bestStreakData: {},    // { habitId: { value, unit } }
    categories: [],        // array of category objects from server
    achievements: [],      // [{ code, unlocked_at }]
};

let currentDetailHabitId = null;
let editingHabitId = null;
let newHabit = { name: '', emoji: '🏃', time: 'morning', why: '', bundle: '', color: '#1e3a2f', twoMin: '', stack: '', duration: '', reward: '', diff: 'medium', categoryId: null, reminderTime: '', targetDaysPerWeek: 7 };
let selectedIdentity = null;
let activeCategoryFilter = null; // null = show all categories
let previousScreen = 'screen-home';

/** @returns {string} Today's date as an ISO string e.g. "2026-03-30". */
function today() { return new Date().toISOString().slice(0, 10); }

// ══════════════════════════════════════════
//  API
// ══════════════════════════════════════════

/**
 * Low-level fetch wrapper used for all API calls. Throws on non-OK HTTP status.
 * Sets JSON content-type and CSRF token headers automatically.
 *
 * @@param {'GET'|'POST'|'PUT'|'DELETE'} method  HTTP verb.
 * @@param {string}                      url     Root-relative URL, e.g. `/api/state`.
 * @@param {Object|null}                 [data]  JSON-serialisable request body.
 * @@returns {Promise<any>}  Parsed JSON response body.
 * @@throws {Error}          On non-OK HTTP status.
 */
async function api(method, url, data = null) {
    const opts = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
        },
    };
    if (data) { opts.body = JSON.stringify(data); }
    const res = await fetch(url, opts);
    if (!res.ok) { throw new Error(res.status); }
    return res.json();
}

// ══════════════════════════════════════════
//  PERSISTENCE
// ══════════════════════════════════════════

/** Serialise the current `state` to localStorage. Silently no-ops on QuotaExceededError. */
function saveLocal() { try { localStorage.setItem('atomicme_v3', JSON.stringify(state)); } catch(e) {} }

/**
 * Deserialise the localStorage snapshot back into `state`.
 * Merges with the default shape to ensure new fields added in later versions
 * are always present even if the stored snapshot predates them.
 */
function loadLocal() {
    try {
        const r = localStorage.getItem('atomicme_v3');
        if (r) {
            const loaded = JSON.parse(r);
            // Merge loaded data with current state defaults so new fields added
            // in later versions don't crash code that expects them to exist.
            state = {
                ...state,
                ...loaded,
                streakData:      loaded.streakData      || {},
                bestStreakData:  loaded.bestStreakData   || {},
                completionNotes: loaded.completionNotes || {},
                categories:      loaded.categories      || [],
                achievements:    loaded.achievements    || [],
            };
        }
    } catch(e) {}
}

// ══════════════════════════════════════════
//  INIT
// ══════════════════════════════════════════

/**
 * Bootstrap the application.
 *  1. Instantly renders the cached state from localStorage (fast paint).
 *  2. Fetches fresh state from `/api/state` and re-renders.
 *  3. Triggers the weekly review prompt if applicable.
 * Called once on page load — never call manually.
 *
 * @@returns {Promise<void>}
 */
function applyServerState(data) {
    state.user            = data.user;
    state.habits          = data.habits          || [];
    state.completions     = data.completions     || {};
    state.completionNotes = data.completionNotes || {};
    state.streaks         = data.streaks         || {};
    state.bestStreaks     = data.bestStreaks      || {};
    state.streakData      = data.streakData      || {};
    state.bestStreakData  = data.bestStreakData   || {};
    state.categories      = data.categories      || [];
    state.achievements    = data.achievements    || [];
    saveLocal();
}

async function fetchStateFromServer() {
    return await api('GET', '/api/state');
}

async function init() {
    console.log('[AtomicMe] init() started');

    // 1. Instant render from localStorage cache
    loadLocal();
    if (state.user) {
        console.log('[AtomicMe] localStorage cache hit — user:', state.user.name, '— showing home immediately');
        showScreen('screen-home');
        updateNavActive('screen-home');
        renderHome();
    } else {
        // No cached user — show boot loading screen while we wait for the PHP server.
        // NEVER default to onboarding here; onboarding is shown only when the server
        // explicitly confirms user = null (genuine new user, not a cold-start race).
        console.log('[AtomicMe] No cached user — showing boot screen');
        showScreen('screen-boot');
    }

    // 2. Try to reach the server. On NativePHP Android, the PHP server can take
    //    3-8 seconds to boot. We keep retrying until we get a real response.
    //    IMPORTANT: We NEVER show onboarding just because the server is unreachable.
    //    Onboarding is shown ONLY when the server explicitly confirms user = null.
    let data = null;
    const retryDelays = [0, 1000, 2000, 3000, 4000];
    for (let attempt = 0; attempt < retryDelays.length; attempt++) {
        try {
            if (retryDelays[attempt] > 0) {
                console.log('[AtomicMe] Retry attempt', attempt, '— waiting', retryDelays[attempt], 'ms');
                await new Promise(r => setTimeout(r, retryDelays[attempt]));
            }
            console.log('[AtomicMe] Fetching /api/state (attempt', attempt, ')');
            data = await fetchStateFromServer();
            console.log('[AtomicMe] Server responded on attempt', attempt, '— user:', data && data.user ? data.user.name : 'null');
            break; // got a response — stop retrying
        } catch(e) {
            console.log('[AtomicMe] Attempt', attempt, 'failed:', e && e.message);
            // server not ready yet, keep retrying
        }
    }

    if (data && data.user) {
        // Server confirmed user exists — go to home
        console.log('[AtomicMe] Server confirmed user — transitioning to home');
        applyServerState(data);
        showScreen('screen-home');
        updateNavActive('screen-home');
        renderHome();
        maybeShowWeeklyReview();
    } else if (data && !data.user) {
        // Server confirmed NO user in DB — this is a genuine new user
        if (data.habits) state.habits = data.habits;
        if (data.completions) state.completions = data.completions;
        if (data.categories) state.categories = data.categories;
        showScreen('screen-onboarding');
    } else {
        // All retries failed — server still not ready after ~10 seconds.
        // If localStorage has a user, trust it and stay on home (already showing).
        // If not, the boot screen is already visible — keep polling every 1.5s.
        if (!state.user) {
            // Server still not ready — keep polling every 1.5s.
            // When we get a response, transition directly without window.location.reload()
            // because reload() is unreliable inside NativePHP Android WebView.
            console.log('[AtomicMe] Starting background polling (server not ready after initial retries)');
            const bgRetry = setInterval(async () => {
                console.log('[AtomicMe] Polling tick — fetching /api/state');
                try {
                    const d = await fetchStateFromServer();
                    console.log('[AtomicMe] Poll response received, user:', d && d.user ? d.user.name : 'null');
                    if (d && d.user) {
                        clearInterval(bgRetry);
                        // Guard: window.App modules must be initialised before renderHome() works.
                        // If Vite hasn't finished loading app.js yet, wait up to 2s and retry once.
                        const doTransition = () => {
                            try {
                                console.log('[AtomicMe] Transitioning from boot to home screen');
                                applyServerState(d);
                                showScreen('screen-home');
                                updateNavActive('screen-home');
                                renderHome();
                                maybeShowWeeklyReview();
                                console.log('[AtomicMe] Boot-to-home transition complete');
                            } catch(err) {
                                console.error('[AtomicMe] Error during boot-to-home transition:', err);
                            }
                        };
                        if (window.App) {
                            doTransition();
                        } else {
                            console.log('[AtomicMe] window.App not ready, waiting 500ms');
                            setTimeout(() => {
                                if (window.App) {
                                    doTransition();
                                } else {
                                    console.warn('[AtomicMe] window.App still not ready after 500ms, attempting transition anyway');
                                    doTransition();
                                }
                            }, 500);
                        }
                    } else if (d && !d.user) {
                        // Confirmed new user
                        clearInterval(bgRetry);
                        console.log('[AtomicMe] Poll confirmed new user — showing onboarding');
                        showScreen('screen-onboarding');
                    }
                    // If d is null/undefined, server responded but malformed — keep polling
                } catch(e) {
                    console.log('[AtomicMe] Poll failed (server not ready yet):', e && e.message);
                }
            }, 1500);
        }
        // If we have a cached user, we already showed home screen above — nothing more to do.
    }
}

init();

// ══════════════════════════════════════════
//  CATEGORY HELPERS
// ══════════════════════════════════════════

/**
 * Find a category object by its numeric ID.
 *
 * @@param {number|null} id
 * @@returns {Object|null}
 */
function getCategoryById(id) {
    return state.categories.find(c => c.id === id) || null;
}

/**
 * Return the hex colour string for a category, or the default muted colour.
 *
 * @@param {number|null} categoryId
 * @@returns {string}  CSS colour string.
 */
function getCategoryColor(categoryId) {
    const cat = getCategoryById(categoryId);
    return cat ? cat.color : '#5A6180';
}

/**
 * Render a small inline badge HTML string for a category pill.
 * Returns an empty string if the category is not found.
 *
 * @@param {number|null} categoryId
 * @@returns {string}  HTML string.
 */
function renderCategoryBadge(categoryId) {
    const cat = getCategoryById(categoryId);
    return cat ? `<span class="category-badge" style="background:${cat.color}22; border:1px solid ${cat.color}55; color:${cat.color};">${cat.name}</span>` : '';
}

// ══════════════════════════════════════════
//  SCREEN NAVIGATION
// ══════════════════════════════════════════

/**
 * Activate a screen by its element ID, deactivating all others.
 * Also triggers the appropriate render function for data-driven screens.
 *
 * @@param {string} id  The screen element's `id`, e.g. `'screen-home'`.
 * @@returns {void}
 */
function showScreen(id) {
    const currentActive = document.querySelector('.screen.active');
    if (currentActive && currentActive.id !== id) { previousScreen = currentActive.id; }
    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active', 'slide-left'));
    const screenEl = document.getElementById(id);
    screenEl.classList.add('active');
    screenEl.scrollTo(0, 0);
    // Hide bottom nav during onboarding and boot loading screen
    const bottomNav = document.querySelector('.bottom-nav');
    if (bottomNav) {
        if (id === 'screen-onboarding' || id === 'screen-boot') {
            bottomNav.classList.add('hidden');
        } else {
            bottomNav.classList.remove('hidden');
        }
    }
    if (id === 'screen-home')  { editingHabitId = null; renderHome(); }
    if (id === 'screen-stats') { renderStats(); }
    if (id === 'screen-growth') { renderGrowth(); }
    if (id === 'screen-achievements') { renderAchievements(); }
    if (id === 'screen-add' && !editingHabitId) { resetAddForm(); }
}

/**
 * Switch to a tab screen and update the active state on the bottom nav item
 * within that screen. Separate from `showScreen` because tab nav items need
 * their own active class management.
 *
 * @@param {string}          id     Screen element ID.
 * @@param {HTMLElement|null} navEl  The clicked nav item element (or null).
 * @@returns {void}
 */
function showTab(id, navEl) {
    if (!state.user && id !== 'screen-onboarding') { return; }
    showScreen(id);
    updateNavActive(id);
}

function updateNavActive(id) {
    document.querySelectorAll('.bottom-nav .nav-item').forEach(n => n.classList.remove('active'));
    const activeNav = document.querySelector(`.bottom-nav .nav-item[data-tab="${id}"]`);
    if (activeNav) { activeNav.classList.add('active'); }
}

// ══════════════════════════════════════════
//  ONBOARDING
// ══════════════════════════════════════════

/**
 * Handle a tap on an identity card in the onboarding grid.
 * Marks the card selected and enables the CTA button if a name is also entered.
 *
 * @@param {HTMLElement} el  The tapped identity card element.
 * @@returns {void}
 */
function selectIdentity(el) {
    document.querySelectorAll('.identity-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedIdentity = el.dataset.id;

    // Show or hide the custom identity input panel
    const customPanel = document.getElementById('ob-custom-panel');
    if (customPanel) {
        customPanel.style.display = selectedIdentity === 'custom' ? 'block' : 'none';
        if (selectedIdentity === 'custom') {
            setTimeout(() => document.getElementById('ob-custom-label')?.focus(), 50);
        }
    }

    checkObReady();
}

/**
 * Select a custom identity icon in the onboarding icon picker.
 *
 * @@param {HTMLElement} btn  The clicked icon button.
 * @@returns {void}
 */
function selectObCustomIcon(btn) {
    document.querySelectorAll('#ob-custom-icons .ob-custom-icon-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
}

/**
 * Enable or disable the onboarding CTA button based on whether both a name and
 * a valid identity (with custom label if applicable) have been provided.
 *
 * @@returns {void}
 */
function checkObReady() {
    const name = document.getElementById('user-name').value.trim();
    let ready   = !!(name && selectedIdentity);
    if (ready && selectedIdentity === 'custom') {
        const customLabel = (document.getElementById('ob-custom-label')?.value || '').trim();
        ready = !!customLabel;
    }
    document.getElementById('ob-btn').disabled = !ready;
}

/**
 * Complete onboarding: set user state, show home, fire welcome toast, and
 * POST to `/api/setup` in the background.
 *
 * @@returns {Promise<void>}
 */
async function finishOnboarding() {
    const name = document.getElementById('user-name').value.trim();
    if (!name || !selectedIdentity) { return; }

    let identityLabel, identityIcon;
    if (selectedIdentity === 'custom') {
        identityLabel = (document.getElementById('ob-custom-label')?.value || '').trim();
        identityIcon  = document.querySelector('#ob-custom-icons .ob-custom-icon-btn.selected')?.dataset.icon || '⭐';
        if (!identityLabel) { return; }
    } else {
        const identity = IDENTITY_MAP[selectedIdentity];
        if (!identity) { return; }
        identityLabel = identity.label;
        identityIcon  = identity.icon;
    }

    state.user = { name, identity: selectedIdentity, identityLabel, identityIcon };
    saveLocal();
    showToast(`Welcome, ${name}! You are becoming ${identityLabel} 🚀`, 'purple');
    showScreen('screen-home');
    renderHome();
    maybeRequestNotificationPermissionAfterOnboarding();
    // Persist the user profile to the DB with retries. On NativePHP Android the
    // embedded PHP server may not be ready on the very first launch, so a single
    // attempt can silently fail leaving the profile missing from SQLite. Without
    // the profile in the DB, every subsequent cold start that loses localStorage
    // (Android WebView can clear it) drops the user back to onboarding.
    const setupPayload = { name, identity: selectedIdentity, identityLabel, identityIcon };
    let saved = false;
    for (let attempt = 0; attempt < 5; attempt++) {
        try {
            if (attempt > 0) {
                await new Promise(r => setTimeout(r, 800 * attempt));
            }
            const result = await api('POST', '/api/setup', setupPayload);
            if (result && result.id) { saved = true; break; }
        } catch(e) { /* keep retrying */ }
    }
    if (!saved) {
        // Last resort: schedule another save attempt after a longer delay
        setTimeout(async () => {
            try { await api('POST', '/api/setup', setupPayload); } catch(e) {}
        }, 5000);
    }
}

// ══════════════════════════════════════════
//  HOME
// ══════════════════════════════════════════

/**
 * Render the home screen: greeting, progress card, daily quote, and habits list.
 * Delegates DOM updates to App.screens.home module helpers.
 *
 * @@returns {void}
 */
function renderHome() {
    if (!state.user) { return; }

    if (window.App) {
        App.screens.home.updateGreeting(state);
        App.screens.home.updateProgressCard(state);
        App.screens.home.updateDailyQuote(state);
        App.screens.home.updateHabitsList(state);
    }
}

/**
 * Capitalise the first character of a string.
 *
 * @@param {string} s
 * @@returns {string}
 */
function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

// ══════════════════════════════════════════
//  TOGGLE COMPLETION
// ══════════════════════════════════════════

/**
 * Toggle today's completion for a habit with an optimistic UI update.
 *
 * Flow:
 *  1. Update `state.completions` and `state.streaks` immediately (optimistic).
 *  2. Re-render home.
 *  3. POST to `/api/completions/toggle` in the background.
 *  4. Apply the authoritative streak returned by the server.
 *  5. Trigger milestone / achievement celebrations if applicable.
 *
 * @@param {string|number} id  Habit ID.
 * @@returns {Promise<void>}
 */
async function toggleHabit(id) {
    const todayKey = today();
    if (!state.completions[todayKey]) { state.completions[todayKey] = []; }
    const arr = state.completions[todayKey];
    const idx = arr.map(String).indexOf(String(id));
    const wasCompleted = idx !== -1;

    // Optimistic update
    if (wasCompleted) {
        arr.splice(idx, 1);
        state.streaks[id] = Math.max(0, (state.streaks[id] || 1) - 1);
    } else {
        arr.push(id);
        state.streaks[id] = (state.streaks[id] || 0) + 1;
        const h = state.habits.find(h => String(h.id) === String(id));
        if (h) { showToast(`${h.emoji} ${h.name} done! Keep it up!`); }
        // Animate
        const checkEl = document.querySelector(`#item-${id} .habit-check`);
        if (checkEl) {
            checkEl.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.4)' }, { transform: 'scale(1)' }], { duration: 300, easing: 'ease-out' });
            checkEl.classList.add('ripple');
            setTimeout(() => checkEl.classList.remove('ripple'), 400);
        }
    }
    saveLocal();
    renderHome();

    // Sync to backend
    try {
        const result = await api('POST', '/api/completions/toggle', { habit_id: id });
        state.streaks[id] = result.streak;
        if (!state.bestStreaks[id] || result.streak > state.bestStreaks[id]) {
            state.bestStreaks[id] = result.streak;
        }
        if (result.streakData) {
            state.streakData[id] = result.streakData;
        }
        saveLocal();
        if (result.milestone && !wasCompleted) {
            const milestoneUnit = result.streakData ? result.streakData.unit : 'days';
            setTimeout(() => showMilestone(result.milestone, milestoneUnit), 700);
        }
        // Achievement unlock celebration (only when not already showing milestone)
        if (result.achievement && !wasCompleted) {
            const delay = result.milestone ? 4000 : 700;
            setTimeout(() => showAchievementCelebration(result.achievement), delay);
        }
        // Update local achievements list
        if (result.achievement && !wasCompleted) {
            const alreadyHas = (state.achievements || []).some(a => a.code === result.achievement.code);
            if (!alreadyHas) {
                state.achievements = state.achievements || [];
                state.achievements.push({ code: result.achievement.code, unlocked_at: new Date().toISOString().replace('T', ' ').slice(0, 19) });
                saveLocal();
            }
        }
        // After successful completion, offer to add a note
        if (!wasCompleted) {
            const noteDelay = (result.milestone || result.achievement) ? 5500 : 1200;
            setTimeout(() => openNoteSheet(id), noteDelay);
        }
    } catch(e) { /* keep optimistic */ }
}

// ══════════════════════════════════════════
//  STREAK HELPERS
// ══════════════════════════════════════════

/**
 * Return one to three fire emojis that visually represent streak intensity.
 *
 * @@param {number} streak  Current streak count.
 * @@returns {string}
 */
function getStreakEmoji(streak) {
    if (streak >= 100) { return '💥🔥💥'; }
    if (streak >= 60)  { return '⚡🔥⚡'; }
    if (streak >= 30)  { return '🔥🔥🔥'; }
    if (streak >= 14)  { return '🔥🔥'; }
    return '🔥';
}


// ══════════════════════════════════════════
//  HABIT DETAIL
// ══════════════════════════════════════════

/**
 * Populate and display the Habit Detail screen for the given habit ID.
 * Sets `currentDetailHabitId`, delegates content rendering to the
 * App.screens.habitDetail module, then syncs the reminder card and navigates.
 *
 * @@param {string|number} id  Habit ID.
 * @@returns {void}
 */
function goBackFromDetail() {
    const target = previousScreen || 'screen-home';
    // Only go back to main tab screens, not other overlays
    const validTargets = ['screen-home', 'screen-stats', 'screen-growth', 'screen-achievements'];
    showScreen(validTargets.includes(target) ? target : 'screen-home');
}

function showHabitDetail(id) {
    if (!id) { return; }
    currentDetailHabitId = id;
    const habit = state.habits.find(h => String(h.id) === String(id));
    if (!habit) { return; }

    if (window.App) {
        App.screens.habitDetail.updateDetailScreen(state, id);
    }

    renderDetailReminder(id);
    showScreen('screen-habit-detail');
}


/**
 * Render the notes timeline section on the Habit Detail screen.
 * Shows the 5 most recent completion notes for the given habit, sorted newest first.
 * Hides the section entirely if there are no notes.
 *
 * @@param {string|number} habitId
 * @@returns {void}
 */
function renderDetailNotes(habitId) {
    // Collect all notes for this habit and render them in a timeline
    const habitNotes = [];

    for (const [key, note] of Object.entries(state.completionNotes)) {
        const [date, hid] = key.split(':');
        if (String(hid) === String(habitId)) {
            habitNotes.push({ date, note });
        }
    }

    const notesEl = document.getElementById('detail-notes-timeline');
    const notesList = document.getElementById('detail-notes-list');

    if (habitNotes.length === 0) {
        notesEl.style.display = 'none';
        return;
    }

    // Sort by date descending (newest first)
    habitNotes.sort((a, b) => new Date(b.date) - new Date(a.date));

    // Show only the last 5 notes
    const recentNotes = habitNotes.slice(0, 5);

    notesList.innerHTML = recentNotes.map(item => {
        const dateObj = new Date(item.date);
        const dayName = dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });

        return `<div class="note-card">
            <div class="note-card-date">${dayName}</div>
            <div class="note-card-text">"${item.note}"</div>
        </div>`;
    }).join('');

    notesEl.style.display = 'block';
}


/**
 * Toggle the current detail habit's completion from the Habit Detail screen.
 * Calls `toggleHabit` then re-renders the detail screen after a short delay.
 *
 * @@returns {void}
 */
function toggleHabitFromDetail() {
    if (currentDetailHabitId) {
        toggleHabit(currentDetailHabitId);
        setTimeout(() => showHabitDetail(currentDetailHabitId), 400);
    }
}

/**
 * Initiate deletion of the habit currently shown in the detail screen.
 * Opens the delete confirmation bottom sheet.
 *
 * @@returns {void}
 */
function deleteHabitFromDetail() {
    if (!currentDetailHabitId) { return; }
    const habit = state.habits.find(h => String(h.id) === String(currentDetailHabitId));
    if (!habit) { return; }
    showDeleteSheet(habit.id, habit.name);
}

/**
 * Delete a habit: remove from local state, cancel its reminder, navigate home,
 * and fire DELETE `/api/habits/{id}` in the background.
 *
 * @@param {string|number} deletedId  Habit ID to remove.
 * @@returns {void}
 */
function deleteHabit(deletedId) {
    state.habits = state.habits.filter(h => String(h.id) !== String(deletedId));
    Object.keys(state.completions).forEach(date => {
        state.completions[date] = state.completions[date].filter(id => String(id) !== String(deletedId));
    });
    delete state.streaks[deletedId];
    delete state.bestStreaks[deletedId];
    delete state.streakData[deletedId];
    delete state.bestStreakData[deletedId];
    // Cancel any scheduled reminder for the deleted habit
    const reminders = loadReminders();
    if (reminders[deletedId]) {
        delete reminders[deletedId];
        saveReminders(reminders);
        cancelLocalNotification(deletedId);
    }
    saveLocal();
    api('DELETE', `/api/habits/${deletedId}`).catch(() => {});
    showScreen('screen-home');
}

/**
 * Pre-populate the Add Habit form with an existing habit's data and switch to
 * edit mode. Sets `editingHabitId` so `saveHabit()` knows to PUT instead of POST.
 *
 * @@param {string|number} id  Habit ID to edit.
 * @@returns {void}
 */
function showEditHabit(id) {
    if (!id) { return; }
    const habit = state.habits.find(h => String(h.id) === String(id));
    if (!habit) { return; }

    editingHabitId = id;

    // Populate form fields and picker selections via module.
    // Sync draft state from habit so picker click handlers stay consistent.
    if (window.App) { App.screens.add.populateForm(habit); }
    newHabit.emoji             = habit.emoji             || '🏃';
    newHabit.color             = habit.color             || '#1e3a2f';
    newHabit.time              = habit.time              || 'morning';
    newHabit.diff              = habit.diff              || 'medium';
    newHabit.categoryId        = habit.categoryId        ?? null;
    newHabit.reminderTime      = habit.reminderTime      || '';
    newHabit.targetDaysPerWeek = habit.targetDaysPerWeek || 7;

    // Update screen chrome for edit mode
    document.getElementById('add-screen-title').textContent = 'Edit Habit';
    document.getElementById('save-habit-btn').textContent   = '✓ Save Changes';
    document.getElementById('add-back-btn').onclick = () => showHabitDetail(id);

    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active', 'slide-left'));
    const addScreen = document.getElementById('screen-add');
    addScreen.classList.add('active');
    addScreen.scrollTo(0, 0);
}

// ══════════════════════════════════════════
//  ADD HABIT FORM
// ══════════════════════════════════════════

/**
 * Reset the Add Habit form to its default blank state.
 * Resets the draft state object, delegates DOM reset to module, then
 * restores screen chrome for create mode.
 *
 * @@returns {void}
 */
function resetAddForm() {
    newHabit = window.App
        ? { ...App.screens.add.defaultNewHabit() }
        : { name: '', emoji: '🏃', time: 'morning', why: '', bundle: '', color: '#1e3a2f', twoMin: '', stack: '', duration: '', reward: '', diff: 'medium', categoryId: null, reminderTime: '', targetDaysPerWeek: 7 };

    if (window.App) { App.screens.add.resetForm(newHabit.targetDaysPerWeek); }

    document.getElementById('save-habit-btn').disabled      = false;
    document.getElementById('save-habit-btn').textContent   = '✓ Create Habit';
    document.getElementById('add-screen-title').textContent = 'New Habit';
    document.getElementById('add-back-btn').onclick = () => showScreen('screen-home');
    renderCategoryPicker();
}

/**
 * Navigate the Add Habit wizard to the specified step.
 * Delegates step display and indicator updates to module; passes current
 * draft frequency so the freq grid renders correctly.
 *
 * @@param {number|'freq'} n  Target step identifier: 0, 'freq', 1, 2, or 3.
 * @@returns {void}
 */
function goStep(n) {
    if (window.App) { App.screens.add.goStep(n, newHabit.targetDaysPerWeek); }
}

/**
 * Set the target frequency for the new habit and highlight the chosen button.
 *
 * @@param {number} days  Number of days per week (1–7).
 * @@returns {void}
 */
function selectFrequency(days) {
    newHabit.targetDaysPerWeek = days;
    document.querySelectorAll('.frequency-btn').forEach(b => b.classList.remove('selected'));
    const btns = document.querySelectorAll('.frequency-btn');
    if (btns[days - 1]) { btns[days - 1].classList.add('selected'); }
}

/**
 * Select an emoji for the new/edited habit from the emoji grid.
 *
 * @@param {HTMLElement} el  The clicked emoji button element.
 * @@returns {void}
 */
function selectEmoji(el) {
    document.querySelectorAll('.emoji-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    newHabit.emoji = el.dataset.emoji;
}

/**
 * Select a time-of-day for the new/edited habit from the time grid.
 *
 * @@param {HTMLElement} el  The clicked time button element.
 * @@returns {void}
 */
function selectTime(el) {
    document.querySelectorAll('#time-grid .time-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    newHabit.time = el.dataset.time;
}

/**
 * Select a background colour for the new/edited habit's icon.
 *
 * @@param {HTMLElement} el  The clicked colour swatch element.
 * @@returns {void}
 */
function selectColor(el) {
    document.querySelectorAll('#color-grid .color-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    newHabit.color = el.dataset.color;
}

/**
 * Select a difficulty level for the new/edited habit.
 *
 * @@param {HTMLElement} el  The clicked difficulty button element.
 * @@returns {void}
 */
function selectDiff(el) {
    document.querySelectorAll('#diff-grid .time-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    newHabit.diff = el.dataset.diff;
}

/**
 * Render the category picker pill list from `state.categories`.
 * Delegates HTML generation to module; keeps inline onclick handlers so
 * `selectCategory()` can update the `newHabit` draft.
 *
 * @@returns {void}
 */
function renderCategoryPicker() {
    const picker = document.getElementById('category-picker');
    if (!picker) { return; }
    picker.innerHTML = `
        <div class="category-pill selected" data-id="" onclick="selectCategory(this, null)">None</div>
        ${state.categories.map(cat => `
            <div class="category-pill ${newHabit.categoryId === cat.id ? 'selected' : ''}"
                 data-id="${cat.id}"
                 style="border-color: ${cat.color}; color: ${cat.color};"
                 onclick="selectCategory(this, ${cat.id})">${cat.name}</div>
        `).join('')}
    `;
}

/**
 * Select a category pill in the category picker.
 *
 * @@param {HTMLElement}  el  The clicked pill element.
 * @@param {number|null}  id  Category ID, or `null` for "None".
 * @@returns {void}
 */
function selectCategory(el, id) {
    const picker = document.getElementById('category-picker');
    picker.querySelectorAll('.category-pill').forEach(p => p.classList.remove('selected'));
    el.classList.add('selected');
    newHabit.categoryId = id;
}

/**
 * Save the current Add Habit form as either a new habit (POST) or an update (PUT).
 * Behaviour is determined by `editingHabitId`:
 *  - `null`    → create mode: optimistic add with temp ID, then server swap.
 *  - non-null  → edit mode: optimistic in-place update, then server confirm.
 *
 * @@returns {Promise<void>}
 */
async function saveHabit() {
    const name = document.getElementById('new-name').value.trim();
    if (!name) { showToast('Please enter a habit name first', 'purple'); goStep(0); return; }

    document.getElementById('save-habit-btn').disabled = true;

    // Read all form field values + draft picker state via module helper.
    const habitData = window.App
        ? App.screens.add.readHabitDraft(newHabit)
        : { name, emoji: newHabit.emoji, time: newHabit.time, color: newHabit.color,
            diff: newHabit.diff, categoryId: newHabit.categoryId,
            targetDaysPerWeek: newHabit.targetDaysPerWeek || 7, reminderTime: newHabit.reminderTime,
            why: '', bundle: '', twoMin: '', stack: '', duration: '', reward: '' };

    if (editingHabitId) {
        // Edit mode — optimistic update in place, no ID change, no touching streaks/completions
        const editId = editingHabitId;
        editingHabitId = null;
        const idx = state.habits.findIndex(h => String(h.id) === String(editId));
        if (idx !== -1) {
            state.habits[idx] = { ...state.habits[idx], ...habitData };
        }
        saveLocal();
        showToast(`${habitData.emoji} "${habitData.name}" updated!`);
        showHabitDetail(editId);

        // Persist to backend
        try {
            const result = await api('PUT', `/api/habits/${editId}`, habitData);
            const i = state.habits.findIndex(h => String(h.id) === String(editId));
            if (i !== -1) { state.habits[i] = result; }
            saveLocal();
            showHabitDetail(editId);
        } catch(e) { /* keep optimistic */ }
        return;
    }

    // Create mode — optimistic add with temp ID
    const tempId    = 'tmp_' + Date.now();
    const isFreq    = (habitData.targetDaysPerWeek || 7) < 7;
    const tempHabit = { ...habitData, id: tempId, createdAt: today() };
    state.habits.push(tempHabit);
    state.streaks[tempId] = 0;
    state.streakData[tempId] = { value: 0, unit: isFreq ? 'weeks' : 'days', graceDayActive: false };
    saveLocal();
    showToast(`${habitData.emoji} "${habitData.name}" added! Every rep is a vote.`);
    showScreen('screen-home');

    // Persist to backend and replace temp ID
    try {
        const result = await api('POST', '/api/habits', habitData);
        const idx = state.habits.findIndex(h => h.id === tempId);
        if (idx !== -1) { state.habits[idx] = result; }
        delete state.streaks[tempId];
        delete state.streakData[tempId];
        state.streaks[result.id] = 0;
        state.bestStreaks[result.id] = 0;
        const resIsFreq = (result.targetDaysPerWeek || 7) < 7;
        state.streakData[result.id] = { value: 0, unit: resIsFreq ? 'weeks' : 'days', graceDayActive: false };
        state.bestStreakData[result.id] = { value: 0, unit: resIsFreq ? 'weeks' : 'days' };
        saveLocal();
        renderHome();
    } catch(e) { /* keep optimistic */ }
}

// ══════════════════════════════════════════
//  STATS
// ══════════════════════════════════════════

/**
 * Render the Stats screen: top stats row, compound chart, weekly grid,
 * per-habit breakdown, and identity votes panel.
 * Delegates to App.screens.stats module.
 *
 * @@returns {void}
 */
function renderStats() {
    if (window.App) {
        App.screens.stats.updateStatsScreen(state);
    }
    renderCalendar();
}

// ══════════════════════════════════════════
//  MONTHLY CALENDAR
// ══════════════════════════════════════════

/**
 * Tracks which year+month offset the calendar is currently showing.
 * 0 = current month, -1 = previous month, etc.
 *
 * @type {number}
 */
let calMonthOffset = 0;

/**
 * The date key ('YYYY-MM-DD') that is currently selected in the calendar popup.
 * Null when no day is selected.
 *
 * @type {string|null}
 */
let calSelectedDate = null;

/**
 * Render the monthly calendar card in the Stats screen.
 * Reads state.completions and state.habits — no extra API calls.
 *
 * @returns {void}
 */
function renderCalendar() {
    const gridEl  = document.getElementById('cal-grid');
    const titleEl = document.getElementById('cal-month-title');
    const nextBtn = document.getElementById('cal-next-btn');
    if (!gridEl || !titleEl) { return; }

    const now         = new Date();
    const year        = now.getFullYear();
    const month       = now.getMonth();
    const targetMonth = month + calMonthOffset;
    const displayDate = new Date(year, targetMonth, 1);
    const dispYear    = displayDate.getFullYear();
    const dispMonth   = displayDate.getMonth();

    titleEl.textContent = displayDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

    if (nextBtn) {
        nextBtn.disabled   = calMonthOffset >= 0;
        nextBtn.style.opacity = calMonthOffset >= 0 ? '0.3' : '1';
    }

    const todayStr     = today();
    const activeHabits = state.habits;
    const totalHabits  = activeHabits.length;

    const firstDay    = new Date(dispYear, dispMonth, 1).getDay();
    const daysInMonth = new Date(dispYear, dispMonth + 1, 0).getDate();

    const DAY_LABELS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
    let html = DAY_LABELS.map(d => `<div class="cal-day-label">${d}</div>`).join('');

    for (let i = 0; i < firstDay; i++) {
        html += `<div class="cal-cell cal-empty"></div>`;
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const m2      = String(dispMonth + 1).padStart(2, '0');
        const d2      = String(d).padStart(2, '0');
        const dateStr = `${dispYear}-${m2}-${d2}`;
        const isFuture   = dateStr > todayStr;
        const isToday    = dateStr === todayStr;
        const isSelected = dateStr === calSelectedDate;

        const completedIds = (state.completions[dateStr] || []).map(String);
        const doneCount = totalHabits > 0
            ? completedIds.filter(id => activeHabits.some(h => String(h.id) === id)).length
            : completedIds.length;

        let cellClass = 'cal-cell';
        if (isFuture) {
            cellClass += ' cal-future';
        } else if (totalHabits > 0 && doneCount >= totalHabits) {
            cellClass += ' cal-all-done';
        } else if (doneCount > 0) {
            cellClass += ' cal-partial';
        }
        if (isToday)    { cellClass += ' cal-today'; }
        if (isSelected) { cellClass += ' cal-selected'; }

        let dotsHtml = '';
        if (doneCount > 0 && !isFuture) {
            const maxDots = Math.min(doneCount, 5);
            dotsHtml = `<div class="cal-dots">${'<div class="cal-dot"></div>'.repeat(maxDots)}</div>`;
        }

        html += `<div class="${cellClass}" data-cal-date="${dateStr}" onclick="calSelectDay('${dateStr}')"><span class="cal-cell-num">${d}</span>${dotsHtml}</div>`;
    }

    gridEl.innerHTML = html;

    if (calSelectedDate) {
        calShowPopup(calSelectedDate);
    } else {
        const popup = document.getElementById('cal-day-popup');
        if (popup) { popup.classList.remove('show'); }
    }
}

/**
 * Navigate the calendar to the previous month.
 *
 * @returns {void}
 */
function calPrev() {
    calMonthOffset--;
    calSelectedDate = null;
    renderCalendar();
}

/**
 * Navigate the calendar to the next month (disabled at current month).
 *
 * @returns {void}
 */
function calNext() {
    if (calMonthOffset >= 0) { return; }
    calMonthOffset++;
    calSelectedDate = null;
    renderCalendar();
}

/**
 * Select a calendar day cell and show the popup of completed habits.
 * Tapping the same day again dismisses the popup.
 *
 * @param {string} dateStr  ISO date string 'YYYY-MM-DD'.
 * @returns {void}
 */
function calSelectDay(dateStr) {
    if (dateStr === calSelectedDate) {
        calSelectedDate = null;
        const popup = document.getElementById('cal-day-popup');
        if (popup) { popup.classList.remove('show'); }
        document.querySelectorAll('.cal-cell.cal-selected').forEach(el => el.classList.remove('cal-selected'));
        return;
    }
    calSelectedDate = dateStr;
    document.querySelectorAll('.cal-cell.cal-selected').forEach(el => el.classList.remove('cal-selected'));
    const cell = document.querySelector(`[data-cal-date="${dateStr}"]`);
    if (cell) { cell.classList.add('cal-selected'); }
    calShowPopup(dateStr);
}

/**
 * Show the day detail popup listing completed habits for the given date.
 *
 * @param {string} dateStr  ISO date string 'YYYY-MM-DD'.
 * @returns {void}
 */
function calShowPopup(dateStr) {
    const popup   = document.getElementById('cal-day-popup');
    const dateEl  = document.getElementById('cal-popup-date');
    const itemsEl = document.getElementById('cal-popup-items');
    if (!popup || !dateEl || !itemsEl) { return; }

    const dateObj = new Date(dateStr + 'T00:00:00');
    dateEl.textContent = dateObj.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });

    const completedIds = (state.completions[dateStr] || []).map(String);
    const doneHabits   = state.habits.filter(h => completedIds.includes(String(h.id)));

    if (doneHabits.length === 0) {
        itemsEl.innerHTML = `<div class="cal-day-popup-empty">No habits completed this day.</div>`;
    } else {
        itemsEl.innerHTML = doneHabits.map(h =>
            `<div class="cal-day-popup-item"><div class="cal-day-popup-dot"></div><span>${h.emoji} ${h.name}</span></div>`
        ).join('');
    }

    popup.classList.add('show');
}

// ══════════════════════════════════════════
//  GROWTH SCREEN
// ══════════════════════════════════════════

/**
 * Render the Growth screen: consistency scores, compound projection chart,
 * week-vs-week and month-vs-month charts (fetched from `/api/analytics`),
 * and per-habit consistency bars.
 * Delegates synchronous rendering to App.screens.growth module; handles
 * the async analytics fetch inline.
 *
 * @@returns {Promise<void>}
 */
async function renderGrowth() {
    if (!state.user) { return; }

    // Update avatar in header
    const growthAvatar = document.getElementById('growth-avatar');
    if (growthAvatar) { growthAvatar.textContent = state.user.name[0].toUpperCase(); }

    if (window.App) {
        // Render synchronous sections (scores, projection, per-habit list)
        App.screens.growth.updateGrowthScreen(state, null);

        // Fetch analytics and inject week/month charts
        try {
            const data = await api('GET', '/api/analytics');
            App.screens.growth.updateGrowthScreen(state, data);
        } catch(e) { /* analytics unavailable — synchronous render already shown */ }
    }
}

// ══════════════════════════════════════════
//  MILESTONE CELEBRATION
// ══════════════════════════════════════════

/**
 * Show the full-screen milestone celebration overlay for a streak milestone.
 * Delegates to App.overlays.milestone module.
 *
 * @@param {number}         value  Streak count (days or weeks).
 * @@param {'days'|'weeks'} unit   Streak unit.
 * @@returns {void}
 */
function showMilestone(value, unit) {
    if (window.App) { App.overlays.milestone.showMilestone(value, unit); }
}

/**
 * Dismiss the milestone / achievement celebration overlay.
 *
 * @@returns {void}
 */
function closeMilestone() {
    if (window.App) { App.overlays.milestone.closeMilestone(); }
}

// ══════════════════════════════════════════
//  ACHIEVEMENT CELEBRATION
// ══════════════════════════════════════════

/**
 * Re-use the milestone overlay to celebrate an achievement unlock.
 * Delegates to App.overlays.milestone module.
 *
 * @@param {Object} achievement  Achievement record from the server (code + optional prestige flag).
 * @@returns {void}
 */
function showAchievementCelebration(achievement) {
    if (window.App) { App.overlays.milestone.showAchievementCelebration(achievement); }
}

// ══════════════════════════════════════════
//  ACHIEVEMENTS SCREEN
// ══════════════════════════════════════════

/**
 * Return a Date object for the Sunday at the start of the week containing `date`.
 *
 * @@param {Date} date
 * @@returns {Date}
 */
function getWeekStart(date) {
    const d = new Date(date);
    d.setDate(d.getDate() - d.getDay());
    d.setHours(0, 0, 0, 0);
    return d;
}

/**
 * Render the Achievements screen, separating easy and prestige achievements
 * into their respective sections.
 *
 * @@returns {void}
 */
function renderAchievements() {
    if (!state.user) { return; }

    const easyList      = document.getElementById('easy-achievements-list');
    const prestigeList  = document.getElementById('prestige-achievements-list');
    const emptyState    = document.getElementById('achievements-empty-state');
    const easySection   = document.getElementById('easy-achievements-section');
    const prestigeSection = document.getElementById('prestige-achievements-section');

    easyList.innerHTML = '';
    prestigeList.innerHTML = '';

    const unlockedCount = (state.achievements || []).length;
    emptyState.style.display = unlockedCount === 0 ? 'block' : 'none';
    easySection.style.display = 'block';
    prestigeSection.style.display = 'block';

    Object.entries(ACHIEVEMENTS_DEFS).forEach(([code, def]) => {
        const unlocked = (state.achievements || []).some(a => a.code === code);
        const card = createAchievementCard(code, def, unlocked);
        if (def.prestige) {
            prestigeList.appendChild(card);
        } else {
            easyList.appendChild(card);
        }
    });
}

/**
 * Create and return an achievement card DOM element.
 * Shows progress bars for trackable locked achievements.
 *
 * @@param {string}  code     Achievement code key from `ACHIEVEMENTS_DEFS`.
 * @@param {Object}  def      Achievement definition object.
 * @@param {boolean} unlocked Whether the achievement has been earned.
 * @@returns {HTMLElement}
 */
function createAchievementCard(code, def, unlocked) {
    const card = document.createElement('div');
    card.className = `achievement-card ${unlocked ? 'unlocked' : 'locked'} ${def.prestige ? 'prestige' : ''}`;

    let progressHtml = '';
    let earnedHtml   = '';

    if (unlocked) {
        const record = (state.achievements || []).find(a => a.code === code);
        const earnedDate = record ? new Date(record.unlocked_at).toLocaleDateString() : '';
        earnedHtml = `<div class="achievement-earned">Earned ${earnedDate}</div>`;
    } else {
        // Progress bars for trackable achievements
        let progress = null;

        if (code === 'perfect_week') {
            const weekStart = getWeekStart(new Date());
            const weekEnd   = new Date(weekStart.getTime() + 7 * 24 * 60 * 60 * 1000);
            let weekDays = 0;
            const totalHabits = state.habits.length;
            if (totalHabits > 0) {
                const cursor = new Date(weekStart);
                while (cursor < weekEnd && cursor <= new Date()) {
                    const key = cursor.toISOString().slice(0, 10);
                    const completedToday = (state.completions[key] || []).filter(id =>
                        state.habits.some(h => String(h.id) === String(id))
                    ).length;
                    if (completedToday >= totalHabits) { weekDays++; }
                    cursor.setDate(cursor.getDate() + 1);
                }
            }
            progress = { current: weekDays, max: 7 };

        } else if (code === 'habit_builder') {
            progress = { current: state.habits.length, max: 5 };

        } else if (code === 'one_percent_club') {
            const maxStreak = state.habits.length > 0
                ? Math.max(...state.habits.map(h => state.streaks[h.id] || 0))
                : 0;
            progress = { current: maxStreak, max: 365 };

        } else if (code === 'perfect_quarter') {
            const habit = state.habits.length > 0
                ? state.habits.reduce((best, h) => (state.streaks[h.id] || 0) > (state.streaks[best.id] || 0) ? h : best)
                : null;
            const maxStreak = habit ? (state.streaks[habit.id] || 0) : 0;
            progress = { current: Math.min(maxStreak, 90), max: 90 };
        }

        if (progress) {
            const pct = Math.round((progress.current / progress.max) * 100);
            progressHtml = `
                <div class="achievement-progress-wrap">
                    <div class="achievement-progress-label">${progress.current}/${progress.max}</div>
                    <div class="achievement-progress-bar">
                        <div class="achievement-progress-fill" style="width:${pct}%"></div>
                    </div>
                </div>`;
        }
    }

    const prestigeTag = def.prestige && unlocked ? `<span class="prestige-tag">PRESTIGE</span>` : '';

    card.innerHTML = `
        <div class="achievement-icon">${def.icon}</div>
        <div class="achievement-info">
            <div class="achievement-name">${def.name}${prestigeTag}</div>
            <div class="achievement-desc">${def.desc}</div>
            ${unlocked ? earnedHtml : `<div class="achievement-criteria">${def.criteria}</div>`}
            ${progressHtml}
        </div>`;

    return card;
}

// ══════════════════════════════════════════
//  TOAST
// ══════════════════════════════════════════

/** @type {number|undefined} setTimeout handle for auto-hiding the toast. */
let toastTimer;

/**
 * Show a brief toast notification at the top of the screen.
 * Auto-hides after 2.8 seconds. Cancels any in-progress toast.
 *
 * @@param {string} msg       Message text to display.
 * @@param {string} [type]    Optional CSS modifier class, e.g. `'purple'`.
 * @@returns {void}
 */
function showToast(msg, type = '') {
    const el = document.getElementById('toast');
    el.textContent = msg;
    el.className = 'toast' + (type ? ' ' + type : '');
    el.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('show'), 2800);
}

// ══════════════════════════════════════════
//  NOTIFICATIONS
// ══════════════════════════════════════════

/**
 * Load the reminder enabled/disabled map from localStorage.
 *
 * @@returns {Object<string|number, boolean>}  Map of habitId → enabled.
 */
function loadReminders() {
    try { return JSON.parse(localStorage.getItem('atomicme_reminders') || '{}'); } catch(e) { return {}; }
}
/**
 * Persist the reminder map to localStorage.
 *
 * @@param {Object<string|number, boolean>} reminders
 * @@returns {void}
 */
function saveReminders(reminders) {
    try { localStorage.setItem('atomicme_reminders', JSON.stringify(reminders)); } catch(e) {}
}

/**
 * Detect whether the app is running inside a NativePHP Mobile WebView.
 * NativePHP serves from 127.0.0.1 (no port); browser dev runs on localhost:8000.
 *
 * @@returns {boolean}
 */
function isNativeContext() {
    // NativePHP Mobile loads the app from 127.0.0.1 (embedded PHP server, no port).
    // The browser dev server runs on localhost:8000. Most reliable sync detection.
    return window.location.hostname === '127.0.0.1';
}

/**
 * Return the current notification permission state.
 * In a NativePHP WebView the browser Notification API is unavailable, so the
 * native context is always treated as `'granted'`.
 *
 * @@returns {'granted'|'denied'|'default'|'unsupported'}
 */
function notificationPermission() {
    // In a NativePHP WebView the browser Notification API is unavailable, but local
    // notifications work through the native bridge. Treat native context as granted.
    if (isNativeContext()) { return 'granted'; }
    if (typeof Notification === 'undefined') { return 'unsupported'; }
    return Notification.permission;
}

/**
 * Request browser notification permission from the user.
 * No-ops in a NativePHP context (permission is managed natively).
 * On grant, re-schedules all reminders that were previously enabled.
 *
 * @@returns {Promise<boolean>}  `true` if permission was granted.
 */
async function requestNotificationPermission() {
    // In native context no browser permission dialog is needed — the bridge handles it.
    if (isNativeContext()) { return true; }
    if (typeof Notification === 'undefined') { return false; }
    try {
        const result = await Notification.requestPermission();
        // Hide both banners
        document.getElementById('home-reminder-permission-banner').classList.remove('show');
        const detailBanner = document.getElementById('reminder-permission-banner');
        if (detailBanner) { detailBanner.classList.remove('show'); }
        if (result === 'granted') {
            showToast('Notifications enabled! Reminders are active.', 'purple');
            // Re-schedule all enabled reminders now that we have permission
            const reminders = loadReminders();
            state.habits.forEach(h => {
                if (reminders[h.id]) {
                    scheduleLocalNotification(h);
                }
            });
            return true;
        } else {
            showToast('Notification permission denied. Reminders will not fire.');
            return false;
        }
    } catch(e) {
        return false;
    }
}

/**
 * Schedule a repeating local notification via the NativePHP native bridge.
 * Fails silently if the native bridge is unavailable (browser context).
 *
 * @@param {Object} habit  Habit object; uses `habit.id`, `habit.emoji`, `habit.name`, `habit.time`.
 * @@returns {Promise<void>}
 */
async function bridgeScheduleNotification(habit) {
    try {
        const timeLabel = getTimeLabel(habit.time);
        await fetch('/_native/api/call', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
            },
            body: JSON.stringify({
                method: 'LocalNotification.Schedule',
                params: {
                    id: 'habit_' + habit.id,
                    title: habit.emoji + ' ' + habit.name,
                    body: 'Time for your ' + timeLabel.toLowerCase() + ' habit. Keep the streak alive!',
                    repeating: true,
                    time: getNotificationTime(habit.time),
                },
            }),
        });
    } catch(e) { /* native API not available in browser — silently ignore */ }
}

/**
 * Cancel a scheduled native notification via the NativePHP bridge.
 * Fails silently if the native bridge is unavailable.
 *
 * @@param {string|number} habitId
 * @@returns {Promise<void>}
 */
async function bridgeCancelNotification(habitId) {
    try {
        await fetch('/_native/api/call', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
            },
            body: JSON.stringify({
                method: 'LocalNotification.Cancel',
                params: { id: 'habit_' + habitId },
            }),
        });
    } catch(e) { /* silently ignore */ }
}

/**
 * Return a human-readable label for a time-of-day key.
 *
 * @@param {'morning'|'afternoon'|'evening'|'anytime'} time
 * @@returns {string}
 */
function getTimeLabel(time) {
    const labels = { morning: 'Morning', afternoon: 'Afternoon', evening: 'Evening', anytime: 'Daily' };
    return labels[time] || 'Daily';
}

/**
 * Return the HH:MM notification time for a habit or a time-of-day key.
 * Accepts either a habit object (checks `reminderTime` first, falls back to
 * `time`) or a bare time string for backwards compatibility.
 *
 * @@param {Object|string} timeOrHabit  Habit object or time-of-day string.
 * @@returns {string}  HH:MM string, e.g. `'08:00'`.
 */
function getNotificationTime(timeOrHabit) {
    // Support both habit object and time string for backwards compatibility
    if (typeof timeOrHabit === 'object' && timeOrHabit !== null) {
        const habit = timeOrHabit;
        if (habit.reminderTime) { return habit.reminderTime; }
        return getNotificationTime(habit.time);
    }
    const times = { morning: '08:00', afternoon: '13:00', evening: '19:00', anytime: '09:00' };
    return times[timeOrHabit] || '09:00';
}

/**
 * Schedule a local notification for a habit.
 * Fires the native bridge schedule and (in browser) shows a Web Notification
 * immediately as a demonstration.
 *
 * @@param {Object} habit  Habit object.
 * @@returns {void}
 */
function scheduleLocalNotification(habit) {
    // Web Notifications API — shows immediately as a demonstration and schedules native if available.
    // In a real device context, the native bridge takes over repeating scheduling.
    bridgeScheduleNotification(habit);

    if (notificationPermission() === 'granted') {
        try {
            new Notification(habit.emoji + ' ' + habit.name, {
                body: 'Time for your ' + getTimeLabel(habit.time).toLowerCase() + ' habit. Keep the streak alive!',
                tag: 'habit_' + habit.id,
                silent: false,
            });
        } catch(e) { /* silently ignore */ }
    }
}

/**
 * Cancel a previously scheduled local notification for a habit.
 *
 * @@param {string|number} habitId
 * @@returns {void}
 */
function cancelLocalNotification(habitId) {
    bridgeCancelNotification(habitId);
}

/**
 * Handle a change to the reminder time input on the Habit Detail screen.
 * Persists the new time to state + backend and re-schedules the notification.
 *
 * @@param {string} time  New HH:MM time string from the `<input type="time">`.
 * @@returns {Promise<void>}
 */
async function handleReminderTimeChange(time) {
    const id = currentDetailHabitId;
    if (!id) { return; }

    const habit = state.habits.find(h => String(h.id) === String(id));
    if (!habit) { return; }

    // Update the habit in state
    habit.reminderTime = time;
    saveLocal();

    // Persist to backend
    try {
        await api('PUT', `/api/habits/${id}`, { ...habit, reminderTime: time });
    } catch(e) { /* keep optimistic */ }

    // Re-schedule notification if enabled
    const reminders = loadReminders();
    if (reminders[id]) {
        cancelLocalNotification(id);
        scheduleLocalNotification(habit);
        showToast(`⏰ Reminder updated to ${time}`, 'purple');
    }
}

/**
 * Handle a change to the reminder toggle on the Habit Detail screen.
 * Requests notification permission if enabling for the first time.
 *
 * @@param {boolean} enabled  `true` to enable, `false` to cancel the reminder.
 * @@returns {Promise<void>}
 */
async function handleReminderToggle(enabled) {
    const id = currentDetailHabitId;
    if (!id) { return; }

    const habit = state.habits.find(h => String(h.id) === String(id));
    if (!habit) { return; }

    // If enabling, check / request permission first
    if (enabled) {
        const perm = notificationPermission();
        if (perm === 'unsupported') {
            showToast('Notifications are not supported on this device.');
            document.getElementById('detail-reminder-toggle').checked = false;
            return;
        }
        if (perm === 'denied') {
            showToast('Notification permission was denied. Please enable in device settings.');
            document.getElementById('detail-reminder-toggle').checked = false;
            return;
        }
        if (perm !== 'granted') {
            const granted = await requestNotificationPermission();
            if (!granted) {
                document.getElementById('detail-reminder-toggle').checked = false;
                return;
            }
        }
        // Schedule
        const reminders = loadReminders();
        reminders[id] = true;
        saveReminders(reminders);
        scheduleLocalNotification(habit);
        showToast('Reminder saved \u2014 push notifications coming in a future update.', 'purple');
    } else {
        // Cancel
        const reminders = loadReminders();
        delete reminders[id];
        saveReminders(reminders);
        cancelLocalNotification(id);
        showToast('Reminder cancelled.');
    }

    renderDetailReminder(id);
}

/**
 * Sync the reminder toggle and time input on the Habit Detail screen to
 * the current persisted state for the given habit.
 * Also shows/hides the permission banner based on current permission state.
 *
 * @@param {string|number} id  Habit ID.
 * @@returns {void}
 */
function renderDetailReminder(id) {
    const habit = state.habits.find(h => String(h.id) === String(id));
    if (!habit) { return; }

    const reminders = loadReminders();
    const isEnabled = !!reminders[id];
    const toggle = document.getElementById('detail-reminder-toggle');
    if (toggle) { toggle.checked = isEnabled; }

    const timeInput = document.getElementById('detail-reminder-time');
    if (timeInput) {
        timeInput.value = getNotificationTime(habit) || '09:00';
    }

    // Show the in-detail permission banner if permission not yet determined
    const perm = notificationPermission();
    const detailBanner = document.getElementById('reminder-permission-banner');
    if (detailBanner) {
        const shouldShow = perm !== 'granted' && perm !== 'denied' && perm !== 'unsupported';
        detailBanner.classList.toggle('show', shouldShow);
    }
}

/**
 * Show the home-screen notification permission banner after onboarding if
 * permission has not yet been requested. Delayed 1.5 s so the home screen
 * is visible first.
 *
 * @@returns {void}
 */
function maybeRequestNotificationPermissionAfterOnboarding() {
    if (notificationPermission() === 'default') {
        // Delay slightly so the user sees the home screen first
        setTimeout(() => {
            const banner = document.getElementById('home-reminder-permission-banner');
            if (banner) { banner.classList.add('show'); }
        }, 1500);
    }
}

// ══════════════════════════════════════════
//  WEEKLY REVIEW
// ══════════════════════════════════════════

/**
 * Show the weekly review overlay if conditions are met.
 * Delayed 1.2 s so the home screen renders first.
 * Delegates condition check and display to App.overlays.weeklyReview.
 *
 * @@returns {void}
 */
function maybeShowWeeklyReview() {
    if (!window.App) { return; }
    if (!App.overlays.weeklyReview.shouldShowWeeklyReview(state)) { return; }
    setTimeout(() => App.overlays.weeklyReview.openWeeklyReview(state), 1200);
}

/**
 * Open the weekly review overlay. Delegates to module.
 *
 * @@returns {void}
 */
function openWeeklyReview() {
    if (window.App) { App.overlays.weeklyReview.openWeeklyReview(state); }
}

/**
 * Close the weekly review overlay. Delegates to module.
 *
 * @@returns {void}
 */
function closeWeeklyReview() {
    if (window.App) { App.overlays.weeklyReview.closeWeeklyReview(); }
}

/**
 * Skip the weekly review: mark this week as reviewed and close the overlay.
 * Delegates skip logic to module; shows toast inline.
 *
 * @@returns {void}
 */
function skipWeeklyReview() {
    if (window.App) { App.overlays.weeklyReview.skipWeeklyReview(); }
    showToast('Review skipped. See you next week!');
}

/**
 * Save the weekly reflection note. Reads note via module helper, applies
 * optimistic dismiss, then POSTs to `/api/reflections` in the background.
 *
 * @@returns {Promise<void>}
 */
async function saveWeeklyReview() {
    if (!window.App) { return; }
    const note   = App.overlays.weeklyReview.readReviewNote();
    const weekOf = App.overlays.weeklyReview.currentWeekOf();

    // Optimistic dismiss.
    App.overlays.weeklyReview.saveLastReviewedWeek(weekOf);
    App.overlays.weeklyReview.closeWeeklyReview();
    showToast('Reflection saved!', 'purple');

    try {
        await api('POST', '/api/reflections', { week_of: weekOf, note });
    } catch(e) { /* non-critical — local state already updated */ }
}

// ══════════════════════════════════════════
//  PROFILE SHEET
// ══════════════════════════════════════════

/**
 * Open the Profile Sheet. Delegates to App.overlays.profileSheet module.
 *
 * @@returns {void}
 */
function openProfileSheet() {
    if (!state.user) { return; }
    if (window.App) { App.overlays.profileSheet.openProfileSheet(state); }
}

/**
 * Close the Profile Sheet and its backdrop. Delegates to module.
 *
 * @@returns {void}
 */
function closeProfileSheet() {
    if (window.App) { App.overlays.profileSheet.closeProfileSheet(); }
}

/**
 * Select an identity option in the Profile Sheet edit form. Delegates to module.
 *
 * @@param {HTMLElement} el  The clicked identity option element.
 * @@returns {void}
 */
function selectProfileIdentity(el) {
    if (window.App) { App.overlays.profileSheet.selectProfileIdentity(el); }
}

/**
 * Select a custom identity icon in the profile sheet icon picker.
 *
 * @@param {HTMLElement} btn  The clicked icon button.
 * @@returns {void}
 */
function selectPsCustomIcon(btn) {
    document.querySelectorAll('#ps-custom-icons .ob-custom-icon-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
}

/**
 * Save profile edits (name + identity). Reads form values via module helpers,
 * applies optimistic state update, then syncs to backend.
 *
 * @@returns {Promise<void>}
 */
async function saveProfileChanges() {
    if (!window.App) { return; }
    const profileData = App.overlays.profileSheet.readProfileForm();
    if (!profileData) {
        if (!document.getElementById('ps-name-input')?.value.trim()) {
            showToast('Please enter your name.');
        } else {
            showToast('Please choose an identity.');
        }
        return;
    }

    const { name, identity, identityLabel, identityIcon } = profileData;

    // Optimistic update
    state.user = { ...state.user, name, identity, identityLabel, identityIcon };
    saveLocal();
    renderHome();
    closeProfileSheet();
    showToast(`Profile updated! You are ${identityLabel}.`, 'purple');

    // Sync to backend
    try {
        await api('POST', '/api/setup', { name, identity, identityLabel, identityIcon });
    } catch(e) { /* keep optimistic state */ }
}

// ══════════════════════════════════════════
//  DELETE BOTTOM SHEET
// ══════════════════════════════════════════

/** @type {string|number|null} Habit ID staged for deletion in the delete sheet. */
let pendingDeleteId = null;

/**
 * Open the delete confirmation bottom sheet for a habit.
 *
 * @@param {string|number} habitId    Habit ID to stage for deletion.
 * @@param {string}        habitName  Habit name shown in the confirmation title.
 * @@returns {void}
 */
function showDeleteSheet(habitId, habitName) {
    pendingDeleteId = habitId;
    document.getElementById('delete-sheet-title').textContent = `Delete "${habitName}"?`;
    document.getElementById('delete-sheet').style.display = 'flex';
}

/**
 * Close the delete confirmation sheet without deleting.
 *
 * @@returns {void}
 */
function closeDeleteSheet() {
    pendingDeleteId = null;
    document.getElementById('delete-sheet').style.display = 'none';
}

document.getElementById('delete-sheet-confirm')?.addEventListener('click', () => {
    if (pendingDeleteId) { deleteHabit(pendingDeleteId); }
    closeDeleteSheet();
});

// ══════════════════════════════════════════
//  NOTE SHEET
// ══════════════════════════════════════════

/**
 * Open the completion note input sheet for a habit. Delegates to module.
 *
 * @@param {string|number} habitId  Habit the note is associated with.
 * @@returns {void}
 */
function openNoteSheet(habitId) {
    if (window.App) { App.overlays.noteSheet.openNoteSheet(habitId); }
}

/**
 * Close the note sheet without saving. Delegates to module.
 *
 * @@returns {void}
 */
function closeNoteSheet() {
    if (window.App) { App.overlays.noteSheet.closeNoteSheet(); }
}

/**
 * Save the completion note from the note sheet. Reads habit ID and note text
 * via module helpers, optimistically updates `state.completionNotes`, closes
 * the sheet, then POSTs to `/api/completions/note` in the background.
 *
 * @@returns {Promise<void>}
 */
async function saveNote() {
    if (!window.App) { return; }
    const habitId = App.overlays.noteSheet.getPendingHabitId();
    if (!habitId) { return; }

    const note = App.overlays.noteSheet.readNoteValue();

    if (!note) {
        closeNoteSheet();
        return;
    }

    const todayStr = today();
    const key = `${todayStr}:${habitId}`;

    // Optimistic update
    state.completionNotes[key] = note;
    saveLocal();

    // If we're on the detail screen, refresh the notes timeline
    if (currentDetailHabitId === habitId) {
        renderDetailNotes(habitId);
    }

    closeNoteSheet();
    showToast('Note saved!', 'purple');

    // Sync note to backend
    try {
        await api('POST', '/api/completions/note', {
            habit_id: habitId,
            note: note,
        });
    } catch(e) {
        // Keep optimistic state if sync fails
    }
}

/**
 * Prompt the user to confirm a full data reset, then DELETE `/api/reset`,
 * clear localStorage, wipe state, and navigate to onboarding.
 *
 * @@returns {Promise<void>}
 */
async function confirmResetData() {
    return new Promise(resolve => {
        const sheet = document.getElementById('delete-sheet');
        const title = document.getElementById('delete-sheet-title');
        const desc = sheet.querySelector('p:nth-child(2)');
        const confirmBtn = document.getElementById('delete-sheet-confirm');

        title.textContent = 'Reset all data?';
        desc.textContent = 'This will delete all your habits and progress. This cannot be undone.';
        confirmBtn.textContent = 'Reset Everything';
        sheet.style.display = 'flex';

        const handler = async () => {
            confirmBtn.removeEventListener('click', handler);
            sheet.style.display = 'none';
            title.textContent = 'Delete habit?';
            desc.textContent = 'This will remove all completion history for this habit.';
            confirmBtn.textContent = 'Delete';
            resolve(true);
        };
        const cancelHandler = () => {
            sheet.style.display = 'none';
            title.textContent = 'Delete habit?';
            desc.textContent = 'This will remove all completion history for this habit.';
            confirmBtn.textContent = 'Delete';
            confirmBtn.removeEventListener('click', handler);
            resolve(false);
        };
        // Override cancel button temporarily
        const cancelBtn = sheet.querySelector('button:last-child');
        const origOnclick = cancelBtn.onclick;
        cancelBtn.onclick = () => { cancelHandler(); cancelBtn.onclick = origOnclick; };
        confirmBtn.addEventListener('click', handler, { once: true });
    }).then(async confirmed => {
        if (!confirmed) { return; }

    try {
        await api('DELETE', '/api/reset');
    } catch(e) { /* proceed with client-side reset regardless */ }

    // Clear all local state and storage
    localStorage.removeItem('atomicme_v3');
    localStorage.removeItem('atomicme_reminders');
    localStorage.removeItem('atomicme_last_reviewed_week');
    state = { user: null, habits: [], completions: {}, completionNotes: {}, streaks: {}, bestStreaks: {}, streakData: {}, bestStreakData: {}, categories: [], achievements: [] };

    closeProfileSheet();
    showScreen('screen-onboarding');

    // Clear rendered home screen content so stale habits aren't visible if user navigates back
    const habitsList = document.getElementById('habits-list');
    if (habitsList) { habitsList.innerHTML = ''; }
    const progressNumbers = document.querySelector('.progress-numbers');
    if (progressNumbers) { progressNumbers.innerHTML = '0<span>/0</span>'; }
    const progressFill = document.querySelector('.progress-bar-fill');
    if (progressFill) { progressFill.style.width = '0%'; }

    // Reset onboarding UI state so the user can re-enter their details cleanly
    selectedIdentity = null;
    const nameInput = document.getElementById('user-name');
    if (nameInput) { nameInput.value = ''; }
    const obBtn = document.getElementById('ob-btn');
    if (obBtn) { obBtn.disabled = true; }
    document.querySelectorAll('.identity-card').forEach(c => c.classList.remove('selected'));
    });
}

// ══════════════════════════════════════════
//  EVENT DELEGATION — data-action handlers
//
//  Modules rendered by App.screens.home.updateHabitsList() use
//  data-action attributes instead of inline onclick. A single delegated
//  listener on document handles all these actions.
// ══════════════════════════════════════════

document.addEventListener('click', function(e) {
    // Frequency button delegation (rendered dynamically by add.js)
    const freqBtn = e.target.closest('.frequency-btn[data-freq]');
    if (freqBtn) {
        selectFrequency(parseInt(freqBtn.dataset.freq, 10));
        return;
    }

    const actionEl = e.target.closest('[data-action]');
    if (!actionEl) { return; }
    const action  = actionEl.dataset.action;
    const habitId = actionEl.dataset.habitId;

    switch (action) {
        case 'toggle-habit':
            if (habitId) { toggleHabit(habitId); }
            break;
        case 'show-detail':
            e.stopPropagation();
            if (habitId) { showHabitDetail(habitId); }
            break;
        case 'go-add':
            showScreen('screen-add');
            break;
        case 'open-profile':
            openProfileSheet();
            break;
        case 'request-notifications':
            requestNotificationPermission();
            break;
    }
});
</script>
</body>
</html>

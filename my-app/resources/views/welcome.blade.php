<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0a10">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>AtomicMe</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; background: #0a0a10; color: #fff; overflow: hidden; }

        /* ── SCREENS ── */
        .screen { position: fixed; inset: 0; display: flex; flex-direction: column; overflow-y: auto; opacity: 0; pointer-events: none; transform: translateX(30px); transition: opacity .3s, transform .3s; }
        .screen.active { opacity: 1; pointer-events: all; transform: translateX(0); }
        .screen.slide-left { transform: translateX(-30px); }

        /* ── ONBOARDING ── */
        #screen-onboarding { background: linear-gradient(160deg, #0a0a10 0%, #1a0a2e 100%); padding: 3rem 1.5rem 2rem; justify-content: center; }
        .ob-logo { font-size: 2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 0.25rem; }
        .ob-logo span { background: linear-gradient(135deg, #a78bfa, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .ob-tagline { font-size: 0.85rem; color: #888; margin-bottom: 3rem; }
        .ob-question { font-size: 1.4rem; font-weight: 700; line-height: 1.3; margin-bottom: 0.5rem; }
        .ob-sub { font-size: 0.85rem; color: #888; margin-bottom: 2rem; }
        .identity-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 2rem; }
        .identity-card { background: #1a1a28; border: 2px solid #2a2a40; border-radius: 1rem; padding: 1.25rem 1rem; cursor: pointer; transition: all .2s; text-align: center; }
        .identity-card:active { transform: scale(0.97); }
        .identity-card.selected { border-color: #a78bfa; background: #1f1535; }
        .identity-card .icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .identity-card .label { font-size: 0.85rem; font-weight: 600; color: #ddd; }
        .identity-card .sub { font-size: 0.7rem; color: #666; margin-top: 0.2rem; }
        .ob-name-wrap { margin-bottom: 1.5rem; }
        .ob-name-wrap label { font-size: 0.8rem; color: #888; display: block; margin-bottom: 0.5rem; }
        .ob-name-wrap input { width: 100%; background: #1a1a28; border: 2px solid #2a2a40; border-radius: 0.75rem; padding: 0.875rem 1rem; color: #fff; font-size: 1rem; font-family: inherit; outline: none; }
        .ob-name-wrap input:focus { border-color: #a78bfa; }
        .btn-primary { width: 100%; padding: 1rem; background: linear-gradient(135deg, #7c3aed, #db2777); border: none; border-radius: 0.875rem; color: #fff; font-size: 1rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: opacity .2s; }
        .btn-primary:active { opacity: 0.85; }
        .btn-primary:disabled { opacity: 0.4; cursor: default; }

        /* ── MAIN APP ── */
        #screen-home, #screen-stats, #screen-add { padding-bottom: 5rem; }

        /* Header */
        .app-header { padding: 1.25rem 1.25rem 0.75rem; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
        .header-greeting h2 { font-size: 1.3rem; font-weight: 700; }
        .header-greeting p { font-size: 0.75rem; color: #888; margin-top: 0.1rem; }
        .avatar { width: 2.5rem; height: 2.5rem; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #db2777); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0; cursor: pointer; }

        /* Progress Card */
        .progress-card { margin: 0 1.25rem 1.25rem; background: linear-gradient(135deg, #5b21b6 0%, #7c3aed 50%, #db2777 100%); border-radius: 1.25rem; padding: 1.25rem; position: relative; overflow: hidden; }
        .progress-card::before { content: ''; position: absolute; top: -30px; right: -30px; width: 100px; height: 100px; background: rgba(255,255,255,0.07); border-radius: 50%; }
        .progress-card::after { content: ''; position: absolute; bottom: -40px; left: -10px; width: 130px; height: 130px; background: rgba(255,255,255,0.05); border-radius: 50%; }
        .progress-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.75; margin-bottom: 0.4rem; }
        .progress-numbers { font-size: 2.25rem; font-weight: 700; line-height: 1; }
        .progress-numbers span { font-size: 1rem; font-weight: 400; opacity: 0.75; }
        .progress-bar-wrap { background: rgba(255,255,255,0.2); border-radius: 999px; height: 6px; margin-top: 1rem; }
        .progress-bar-fill { background: #fff; border-radius: 999px; height: 6px; transition: width .5s ease; }
        .progress-sub { font-size: 0.72rem; opacity: 0.7; margin-top: 0.5rem; }
        .identity-badge { display: inline-flex; align-items: center; gap: 0.35rem; background: rgba(255,255,255,0.15); border-radius: 999px; padding: 0.3rem 0.7rem; font-size: 0.7rem; font-weight: 600; margin-top: 0.75rem; }

        /* Section */
        .section-header { display: flex; align-items: center; justify-content: space-between; padding: 0 1.25rem; margin-bottom: 0.75rem; }
        .section-title { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; color: #666; }
        .section-action { font-size: 0.75rem; color: #a78bfa; cursor: pointer; }

        /* Habits */
        .habits-list { padding: 0 1.25rem; display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1rem; }
        .habit-item { display: flex; align-items: center; gap: 0.875rem; background: #14141e; border: 1px solid #1e1e2e; border-radius: 1rem; padding: 0.875rem; transition: border-color .2s, background .2s; }
        .habit-item.completed { background: #0d1a14; border-color: #1a3024; }
        .habit-item.at-risk { border-color: #f9731640; background: #14100a; }
        .habit-icon-wrap { width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; cursor: pointer; transition: transform .15s; }
        .habit-icon-wrap:active { transform: scale(0.92); }
        .habit-info { flex: 1; min-width: 0; cursor: pointer; }
        .habit-name { font-size: 0.92rem; font-weight: 600; }
        .habit-meta { font-size: 0.72rem; color: #666; margin-top: 0.15rem; }
        .habit-streak { font-size: 0.7rem; font-weight: 600; color: #f97316; margin-top: 0.2rem; }
        .habit-streak.at-risk-text { color: #f97316; }
        .habit-streak.grace-day-text { color: #a78bfa; }
        .habit-check { width: 1.75rem; height: 1.75rem; border-radius: 50%; border: 2px solid #2a2a40; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all .2s; cursor: pointer; }
        .habit-check:active { transform: scale(0.88); }
        .habit-item.completed .habit-check { background: #22c55e; border-color: #22c55e; }
        .habit-item.completed .habit-check::after { content: '✓'; color: white; font-size: 0.75rem; font-weight: 700; }
        .habit-item.completed .habit-name { opacity: 0.6; text-decoration: line-through; }

        /* Add button */
        .add-habit-btn { margin: 0 1.25rem; width: calc(100% - 2.5rem); padding: 1rem; background: transparent; border: 2px dashed #2a2a40; border-radius: 1rem; color: #555; font-size: 0.875rem; font-family: inherit; cursor: pointer; transition: all .2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .add-habit-btn:active { border-color: #7c3aed; color: #a78bfa; }

        /* Empty state */
        .empty-state { text-align: center; padding: 3rem 2rem; }
        .empty-state .icon { font-size: 3rem; margin-bottom: 1rem; }
        .empty-state p { color: #555; font-size: 0.875rem; }

        /* Daily quote */
        .daily-quote { margin: 0.75rem 1.25rem 0; padding: 0.875rem 1rem; background: #14141e; border-left: 3px solid #7c3aed; border-radius: 0 0.75rem 0.75rem 0; font-size: 0.75rem; color: #666; line-height: 1.6; font-style: italic; }

        /* Bottom Nav */
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: #0f0f1a; border-top: 1px solid #1a1a28; display: flex; justify-content: space-around; padding: 0.6rem 0 1.1rem; z-index: 100; }
        .nav-item { display: flex; flex-direction: column; align-items: center; gap: 0.2rem; font-size: 0.62rem; color: #444; cursor: pointer; padding: 0.25rem 1rem; transition: color .2s; }
        .nav-item.active { color: #a78bfa; }
        .nav-icon { font-size: 1.2rem; }

        /* ── ADD HABIT SCREEN ── */
        #screen-add { background: #0a0a10; padding: 0; }
        .add-header { padding: 1.25rem; display: flex; align-items: center; gap: 1rem; border-bottom: 1px solid #1a1a28; flex-shrink: 0; }
        .back-btn { width: 2.25rem; height: 2.25rem; background: #1a1a28; border: none; border-radius: 0.625rem; color: #fff; font-size: 1.1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .add-header h2 { font-size: 1.1rem; font-weight: 700; flex: 1; }
        .add-body { padding: 1.25rem; flex: 1; overflow-y: auto; }

        /* Law steps */
        .law-steps { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; }
        .law-step { flex: 1; height: 3px; border-radius: 999px; background: #1a1a28; transition: background .3s; }
        .law-step.active { background: #7c3aed; }
        .law-step.done { background: #22c55e; }

        .law-badge { display: inline-flex; align-items: center; gap: 0.4rem; background: #1a1a28; border-radius: 999px; padding: 0.35rem 0.75rem; font-size: 0.72rem; font-weight: 600; color: #a78bfa; margin-bottom: 0.75rem; }
        .law-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.35rem; }
        .law-desc { font-size: 0.8rem; color: #666; margin-bottom: 1.5rem; line-height: 1.5; }

        .form-group { margin-bottom: 1.25rem; }
        .form-label { font-size: 0.78rem; color: #888; margin-bottom: 0.5rem; display: block; }
        .form-input { width: 100%; background: #14141e; border: 2px solid #1e1e2e; border-radius: 0.75rem; padding: 0.875rem 1rem; color: #fff; font-size: 0.92rem; font-family: inherit; outline: none; transition: border-color .2s; }
        .form-input:focus { border-color: #7c3aed; }
        .form-input::placeholder { color: #3a3a50; }
        textarea.form-input { resize: none; height: 80px; }

        .emoji-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 0.5rem; }
        .emoji-btn { background: #14141e; border: 2px solid #1e1e2e; border-radius: 0.625rem; padding: 0.6rem; font-size: 1.3rem; cursor: pointer; transition: all .15s; text-align: center; }
        .emoji-btn.selected { border-color: #7c3aed; background: #1f1535; }

        .color-grid { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .color-btn { width: 2.25rem; height: 2.25rem; border-radius: 0.5rem; border: 3px solid transparent; cursor: pointer; transition: all .15s; }
        .color-btn.selected { border-color: #fff; transform: scale(1.1); }

        .time-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
        .time-btn { background: #14141e; border: 2px solid #1e1e2e; border-radius: 0.75rem; padding: 0.75rem; text-align: center; cursor: pointer; transition: all .15s; }
        .time-btn.selected { border-color: #7c3aed; background: #1f1535; }
        .time-btn .t-icon { font-size: 1.25rem; margin-bottom: 0.25rem; }
        .time-btn .t-label { font-size: 0.78rem; font-weight: 600; }
        .time-btn .t-sub { font-size: 0.65rem; color: #666; margin-top: 0.1rem; }

        .reward-input-wrap { position: relative; }
        .reward-input-wrap .reward-emoji { position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); font-size: 1.1rem; }
        .reward-input-wrap .form-input { padding-left: 2.75rem; }

        .nav-row { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
        .btn-secondary { flex: 1; padding: 0.875rem; background: #1a1a28; border: none; border-radius: 0.875rem; color: #aaa; font-size: 0.9rem; font-weight: 600; font-family: inherit; cursor: pointer; }
        .btn-next { flex: 2; padding: 0.875rem; background: linear-gradient(135deg, #7c3aed, #db2777); border: none; border-radius: 0.875rem; color: #fff; font-size: 0.9rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: opacity .2s; }
        .btn-next:active { opacity: 0.85; }

        /* ── STATS SCREEN ── */
        #screen-stats { background: #0a0a10; }
        .stats-section { padding: 0 1.25rem 1.25rem; }
        .stats-card { background: #14141e; border: 1px solid #1e1e2e; border-radius: 1rem; padding: 1.25rem; margin-bottom: 0.75rem; }
        .stats-card h3 { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; color: #666; margin-bottom: 1rem; }
        .compound-chart { display: flex; align-items: flex-end; gap: 3px; height: 60px; }
        .compound-bar { flex: 1; background: linear-gradient(to top, #7c3aed, #db2777); border-radius: 3px 3px 0 0; min-height: 3px; transition: height .5s ease; opacity: 0.85; }
        .compound-bar:last-child { opacity: 1; }
        .chart-labels { display: flex; justify-content: space-between; margin-top: 0.5rem; }
        .chart-labels span { font-size: 0.6rem; color: #444; }
        .stats-row { display: flex; gap: 0.75rem; margin-bottom: 0.75rem; }
        .stat-box { flex: 1; background: #14141e; border: 1px solid #1e1e2e; border-radius: 1rem; padding: 1rem; text-align: center; }
        .stat-box .val { font-size: 1.75rem; font-weight: 700; background: linear-gradient(135deg, #a78bfa, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .stat-box .lbl { font-size: 0.7rem; color: #555; margin-top: 0.2rem; }
        .identity-item { display: flex; align-items: center; gap: 0.875rem; padding: 0.75rem 0; border-bottom: 1px solid #1a1a28; cursor: pointer; }
        .identity-item:last-child { border: none; }
        .identity-icon { font-size: 1.5rem; width: 2.25rem; text-align: center; flex-shrink: 0; }
        .identity-info { flex: 1; min-width: 0; }
        .identity-name { font-size: 0.875rem; font-weight: 600; }
        .identity-votes { font-size: 0.72rem; color: #666; margin-top: 0.15rem; }
        .identity-bar { height: 4px; background: #1a1a28; border-radius: 999px; margin-top: 0.5rem; }
        .identity-bar-fill { height: 4px; background: linear-gradient(135deg, #7c3aed, #db2777); border-radius: 999px; transition: width .6s ease; }
        .weekly-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.4rem; }
        .week-day { text-align: center; }
        .week-day-label { font-size: 0.58rem; color: #444; margin-bottom: 0.4rem; }
        .week-dot { width: 100%; aspect-ratio: 1; border-radius: 0.35rem; background: #1a1a28; }
        .week-dot.done { background: linear-gradient(135deg, #7c3aed, #db2777); }
        .week-dot.partial { background: #3b1f6e; }

        /* ── HABIT DETAIL SCREEN ── */
        #screen-habit-detail { background: #0a0a10; padding: 0; }
        .detail-header { padding: 1.25rem; display: flex; align-items: center; gap: 0.75rem; border-bottom: 1px solid #1a1a28; flex-shrink: 0; }
        .detail-header h2 { font-size: 1rem; font-weight: 700; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .detail-delete-btn { background: none; border: none; color: #555; font-size: 1.1rem; cursor: pointer; padding: 0.25rem; }
        .detail-body { flex: 1; overflow-y: auto; padding-bottom: 2rem; }

        .streak-hero { text-align: center; padding: 2rem 1.25rem 1.5rem; }
        .streak-fire { font-size: 3.5rem; margin-bottom: 0.25rem; line-height: 1; }
        .streak-count-num { font-size: 5rem; font-weight: 800; background: linear-gradient(135deg, #a78bfa, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; letter-spacing: -2px; }
        .streak-label { font-size: 0.85rem; color: #666; margin-top: 0.35rem; }
        .milestone-badge-display { display: none; align-items: center; gap: 0.35rem; background: linear-gradient(135deg, #7c3aed22, #db287722); border: 1px solid #7c3aed55; border-radius: 999px; padding: 0.35rem 0.875rem; font-size: 0.78rem; font-weight: 600; color: #a78bfa; margin-top: 0.875rem; }

        .heatmap-wrap { padding: 0 1.25rem; margin-bottom: 0.75rem; }
        .heatmap-day-labels { display: flex; gap: 3px; margin-bottom: 4px; padding-left: 0; }
        .heatmap-grid { display: flex; gap: 3px; overflow-x: hidden; }
        .heatmap-col { display: flex; flex-direction: column; gap: 3px; flex: 1; }
        .heatmap-cell { width: 100%; aspect-ratio: 1; border-radius: 3px; background: #1a1a28; }
        .heatmap-cell.done { background: linear-gradient(135deg, #7c3aed, #db2777); }
        .heatmap-cell.today { outline: 2px solid #a78bfa; outline-offset: 1px; }

        .insight-card { margin: 0 1.25rem 0.75rem; background: #14141e; border: 1px solid #1e1e2e; border-left: 3px solid #7c3aed; border-radius: 0 0.75rem 0.75rem 0; padding: 0.875rem 1rem; font-size: 0.82rem; color: #888; line-height: 1.6; }
        .insight-card strong { color: #ccc; }

        .detail-action-row { padding: 0 1.25rem; }
        .btn-complete { width: 100%; padding: 1rem; background: linear-gradient(135deg, #7c3aed, #db2777); border: none; border-radius: 0.875rem; color: #fff; font-size: 1rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: all .2s; }
        .btn-complete.is-done { background: #1a3024; color: #22c55e; border: 2px solid #22c55e44; }
        .btn-complete:active { opacity: 0.85; transform: scale(0.98); }

        .setup-card { margin: 0 1.25rem 0.75rem; background: #14141e; border: 1px solid #1e1e2e; border-radius: 1rem; padding: 1rem; }
        .setup-card-title { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.08em; color: #666; margin-bottom: 0.875rem; }
        .setup-field { margin-bottom: 0.75rem; }
        .setup-field:last-child { margin-bottom: 0; }
        .setup-field-label { font-size: 0.7rem; color: #555; margin-bottom: 0.2rem; }
        .setup-field-value { font-size: 0.85rem; color: #bbb; line-height: 1.5; }

        /* ── REMINDER TOGGLE ── */
        .reminder-card { margin: 0 1.25rem 0.75rem; background: #14141e; border: 1px solid #1e1e2e; border-radius: 1rem; padding: 1rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .reminder-card-info { flex: 1; min-width: 0; }
        .reminder-card-title { font-size: 0.88rem; font-weight: 600; color: #ddd; }
        .reminder-card-sub { font-size: 0.72rem; color: #555; margin-top: 0.2rem; }
        .reminder-toggle { position: relative; width: 2.75rem; height: 1.5rem; flex-shrink: 0; }
        .reminder-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
        .reminder-toggle-track { position: absolute; inset: 0; background: #2a2a40; border-radius: 999px; cursor: pointer; transition: background .2s; }
        .reminder-toggle input:checked + .reminder-toggle-track { background: #7c3aed; }
        .reminder-toggle-track::after { content: ''; position: absolute; top: 3px; left: 3px; width: 1.125rem; height: 1.125rem; background: #fff; border-radius: 50%; transition: transform .2s; }
        .reminder-toggle input:checked + .reminder-toggle-track::after { transform: translateX(1.25rem); }
        .reminder-permission-banner { margin: 0 1.25rem 0.75rem; background: #1a1020; border: 1px solid #7c3aed44; border-radius: 1rem; padding: 0.875rem 1rem; display: none; align-items: center; gap: 0.75rem; }
        .reminder-permission-banner.show { display: flex; }
        .reminder-permission-banner-text { flex: 1; font-size: 0.8rem; color: #888; line-height: 1.5; }
        .reminder-permission-banner-text strong { color: #a78bfa; display: block; margin-bottom: 0.15rem; }
        .btn-allow-notifs { background: linear-gradient(135deg, #7c3aed, #db2777); border: none; border-radius: 0.625rem; color: #fff; font-size: 0.8rem; font-weight: 600; font-family: inherit; padding: 0.5rem 0.875rem; cursor: pointer; white-space: nowrap; }

        /* ── MILESTONE OVERLAY ── */
        .milestone-overlay { position: fixed; inset: 0; background: rgba(10,10,16,0.96); z-index: 999; display: none; align-items: center; justify-content: center; padding: 2rem; }
        .milestone-overlay.show { display: flex; }
        .milestone-content { text-align: center; max-width: 320px; width: 100%; }
        .milestone-emoji-big { font-size: 5rem; margin-bottom: 1rem; display: block; animation: pop .4s cubic-bezier(.36,1.2,.56,1) both; }
        .milestone-title-text { font-size: 2.25rem; font-weight: 800; margin-bottom: 0.5rem; background: linear-gradient(135deg, #a78bfa, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: -1px; }
        .milestone-sub-text { font-size: 0.9rem; color: #888; margin-bottom: 1.5rem; line-height: 1.5; }
        .milestone-quote-text { font-size: 0.82rem; color: #a78bfa; font-style: italic; margin-bottom: 2rem; line-height: 1.7; padding: 1rem; background: #1a1a28; border-radius: 0.875rem; }
        @keyframes pop { 0% { transform: scale(0.5); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }

        /* ── WEEKLY REVIEW OVERLAY ── */
        .weekly-review-overlay { position: fixed; inset: 0; background: rgba(10,10,16,0.97); z-index: 999; display: none; flex-direction: column; overflow-y: auto; padding: 2rem 1.5rem 2.5rem; }
        .weekly-review-overlay.show { display: flex; }
        .wr-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .wr-title { font-size: 1.4rem; font-weight: 800; background: linear-gradient(135deg, #a78bfa, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: -0.5px; }
        .wr-skip-btn { font-size: 0.8rem; color: #555; background: none; border: none; font-family: inherit; cursor: pointer; padding: 0.5rem; }
        .wr-sub { font-size: 0.82rem; color: #666; margin-bottom: 1.5rem; line-height: 1.6; }
        .wr-section-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: #555; margin-bottom: 0.6rem; }
        .wr-habit-row { display: flex; align-items: center; justify-content: space-between; padding: 0.65rem 0.875rem; background: #14141e; border: 1px solid #1e1e2e; border-radius: 0.75rem; margin-bottom: 0.5rem; }
        .wr-habit-left { display: flex; align-items: center; gap: 0.6rem; font-size: 0.875rem; font-weight: 600; }
        .wr-habit-pct { font-size: 0.75rem; font-weight: 700; }
        .wr-habit-pct.good { color: #22c55e; }
        .wr-habit-pct.ok   { color: #f97316; }
        .wr-habit-pct.low  { color: #ef4444; }
        .wr-question { font-size: 0.92rem; font-weight: 600; color: #ccc; margin: 1.25rem 0 0.6rem; line-height: 1.4; }
        .wr-textarea { width: 100%; min-height: 5rem; background: #14141e; border: 2px solid #2a2a40; border-radius: 0.875rem; padding: 0.875rem 1rem; color: #fff; font-size: 0.9rem; font-family: inherit; outline: none; resize: none; line-height: 1.6; }
        .wr-textarea:focus { border-color: #a78bfa; }
        .wr-save-btn { margin-top: 1.25rem; width: 100%; padding: 1rem; background: linear-gradient(135deg, #7c3aed, #db2777); border: none; border-radius: 0.875rem; color: #fff; font-size: 1rem; font-weight: 700; font-family: inherit; cursor: pointer; transition: opacity .2s; }
        .wr-save-btn:active { opacity: 0.85; }

        /* Toast */
        .toast { position: fixed; top: 1rem; left: 50%; transform: translateX(-50%) translateY(-80px); background: #22c55e; color: #fff; padding: 0.75rem 1.5rem; border-radius: 999px; font-size: 0.85rem; font-weight: 600; z-index: 998; transition: transform .3s; white-space: nowrap; max-width: calc(100% - 2rem); text-align: center; }
        .toast.show { transform: translateX(-50%) translateY(0); }
        .toast.purple { background: linear-gradient(135deg, #7c3aed, #db2777); }

        /* Scrollbar */
        ::-webkit-scrollbar { display: none; }
        * { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<!-- MILESTONE OVERLAY -->
<div class="milestone-overlay" id="milestone-overlay">
    <div class="milestone-content">
        <span class="milestone-emoji-big" id="milestone-emoji">🏆</span>
        <div class="milestone-title-text" id="milestone-title">30 Day Streak!</div>
        <div class="milestone-sub-text" id="milestone-sub">A full month of showing up. Incredible.</div>
        <div class="milestone-quote-text" id="milestone-quote">"You do not rise to the level of your goals. You fall to the level of your systems."</div>
        <button class="btn-primary" onclick="closeMilestone()">Keep Going! 🚀</button>
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

<!-- ══════════════════ SCREEN: ONBOARDING ══════════════════ -->
<div class="screen active" id="screen-onboarding">
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
    </div>

    <div class="ob-name-wrap">
        <label>Your first name</label>
        <input type="text" id="user-name" placeholder="e.g. Alex" maxlength="20" oninput="checkObReady()">
    </div>

    <button class="btn-primary" id="ob-btn" disabled onclick="finishOnboarding()">Start My Journey →</button>
</div>

<!-- ══════════════════ SCREEN: HOME ══════════════════ -->
<div class="screen" id="screen-home">
    <div class="app-header">
        <div class="header-greeting">
            <h2 id="home-greeting">Good morning 👋</h2>
            <p id="home-date"></p>
        </div>
        <div class="avatar" id="home-avatar" onclick="showHabitDetail(null)">?</div>
    </div>

    <div class="progress-card">
        <div class="progress-label">Today's Progress</div>
        <div class="progress-numbers"><span id="done-count">0</span> <span>/ <span id="total-count">0</span> habits</span></div>
        <div class="progress-bar-wrap"><div class="progress-bar-fill" id="progress-bar" style="width:0%"></div></div>
        <div class="progress-sub" id="progress-sub">Add your first habit to get started.</div>
        <div class="identity-badge" id="identity-badge">✨ Loading...</div>
    </div>

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
        <span style="font-size:1.1rem">+</span> Add New Habit
    </button>

    <div class="reminder-permission-banner" id="home-reminder-permission-banner" style="margin-top:0.75rem;">
        <div class="reminder-permission-banner-text">
            <strong>Enable reminders</strong>
            Never miss a habit — allow daily notifications.
        </div>
        <button class="btn-allow-notifs" onclick="requestNotificationPermission()">Allow</button>
    </div>

    <div class="daily-quote" id="daily-quote"></div>

    <nav class="bottom-nav">
        <div class="nav-item active" onclick="showTab('screen-home', this)"><span class="nav-icon">🏠</span><span>Today</span></div>
        <div class="nav-item" onclick="showTab('screen-stats', this)"><span class="nav-icon">📊</span><span>Stats</span></div>
    </nav>
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

            <div class="nav-row">
                <button class="btn-next" style="flex:1" onclick="goStep(1)">Next: Make it Attractive →</button>
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
                <button class="btn-secondary" onclick="goStep(0)">← Back</button>
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
            <h2>Your Progress</h2>
            <p>Every rep is a vote for who you're becoming.</p>
        </div>
        <div class="avatar" id="stats-avatar">?</div>
    </div>

    <div class="stats-section">
        <div class="stats-row">
            <div class="stat-box"><div class="val" id="stat-streak">0</div><div class="lbl">Day Streak</div></div>
            <div class="stat-box"><div class="val" id="stat-total">0</div><div class="lbl">Total Done</div></div>
            <div class="stat-box"><div class="val" id="stat-rate">0%</div><div class="lbl">Today's Rate</div></div>
        </div>

        <div class="stats-card">
            <h3>1% Compound Growth</h3>
            <div class="compound-chart" id="compound-chart"></div>
            <div class="chart-labels"><span>Week 1</span><span>Week 4</span><span>Week 12</span><span>Week 26</span><span>1 Year</span></div>
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
    </div>

    <nav class="bottom-nav">
        <div class="nav-item" onclick="showTab('screen-home', this)"><span class="nav-icon">🏠</span><span>Today</span></div>
        <div class="nav-item active" onclick="showTab('screen-stats', this)"><span class="nav-icon">📊</span><span>Stats</span></div>
    </nav>
</div>

<!-- ══════════════════ SCREEN: HABIT DETAIL ══════════════════ -->
<div class="screen" id="screen-habit-detail">
    <div class="detail-header">
        <button class="back-btn" onclick="showScreen('screen-home')">←</button>
        <h2 id="detail-title">Habit</h2>
        <button class="detail-edit-btn" onclick="showEditHabit(currentDetailHabitId)" title="Edit habit" style="background:none;border:none;color:#a78bfa;font-size:0.85rem;font-weight:600;cursor:pointer;padding:0.25rem 0.5rem;">Edit</button>
        <button class="detail-delete-btn" onclick="deleteHabitFromDetail()" title="Delete habit">🗑</button>
    </div>
    <div class="detail-body">
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

        <div class="heatmap-wrap">
            <div class="stats-card" style="padding: 1rem;">
                <h3 style="margin-bottom: 0.75rem;">Last 12 Weeks</h3>
                <div class="heatmap-grid" id="detail-heatmap"></div>
            </div>
        </div>

        <div class="insight-card" id="detail-insight"></div>

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
                <div class="reminder-card-sub" id="detail-reminder-sub">Remind me at <span id="detail-reminder-time"></span></div>
            </div>
            <label class="reminder-toggle">
                <input type="checkbox" id="detail-reminder-toggle" onchange="handleReminderToggle(this.checked)">
                <span class="reminder-toggle-track"></span>
            </label>
        </div>

        <div class="detail-action-row">
            <button class="btn-complete" id="detail-complete-btn" onclick="toggleHabitFromDetail()">
                ✓ Complete for Today
            </button>
        </div>
    </div>
</div>

<script>
// ══════════════════════════════════════════
//  CONSTANTS
// ══════════════════════════════════════════
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
let state = {
    user: null,
    habits: [],
    completions: {},  // { 'YYYY-MM-DD': [habitId, ...] }
    streaks: {},      // { habitId: n }
    bestStreaks: {},   // { habitId: n }
};

let currentDetailHabitId = null;
let editingHabitId = null;
let currentStep = 0;
let newHabit = { name: '', emoji: '🏃', time: 'morning', why: '', bundle: '', color: '#1e3a2f', twoMin: '', stack: '', duration: '', reward: '', diff: 'medium' };
let selectedIdentity = null;

function today() { return new Date().toISOString().slice(0, 10); }

// ══════════════════════════════════════════
//  API
// ══════════════════════════════════════════
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
function saveLocal() { try { localStorage.setItem('atomicme_v3', JSON.stringify(state)); } catch(e) {} }
function loadLocal() { try { const r = localStorage.getItem('atomicme_v3'); if (r) state = JSON.parse(r); } catch(e) {} }

// ══════════════════════════════════════════
//  INIT
// ══════════════════════════════════════════
async function init() {
    // Instant render from cache
    loadLocal();
    if (state.user) {
        showScreen('screen-home');
        renderHome();
    }

    // Sync from server
    try {
        const data = await api('GET', '/api/state');
        if (data.user) {
            state.user        = data.user;
            state.habits      = data.habits;
            state.completions = data.completions;
            state.streaks     = data.streaks;
            state.bestStreaks  = data.bestStreaks;
            saveLocal();
            showScreen('screen-home');
            renderHome();
            maybeShowWeeklyReview();
        } else if (!state.user) {
            showScreen('screen-onboarding');
        }
    } catch(e) {
        if (!state.user) { showScreen('screen-onboarding'); }
    }
}

init();

// ══════════════════════════════════════════
//  SCREEN NAVIGATION
// ══════════════════════════════════════════
function showScreen(id) {
    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active', 'slide-left'));
    document.getElementById(id).classList.add('active');
    window.scrollTo(0, 0);
    if (id === 'screen-home')  { editingHabitId = null; renderHome(); }
    if (id === 'screen-stats') { renderStats(); }
    if (id === 'screen-add' && !editingHabitId) { resetAddForm(); }
}

function showTab(id, navEl) {
    showScreen(id);
    document.querySelectorAll('.bottom-nav .nav-item').forEach(n => n.classList.remove('active'));
    navEl.classList.add('active');
}

// ══════════════════════════════════════════
//  ONBOARDING
// ══════════════════════════════════════════
function selectIdentity(el) {
    document.querySelectorAll('.identity-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    selectedIdentity = el.dataset.id;
    checkObReady();
}

function checkObReady() {
    const name = document.getElementById('user-name').value.trim();
    document.getElementById('ob-btn').disabled = !(name && selectedIdentity);
}

async function finishOnboarding() {
    const name = document.getElementById('user-name').value.trim();
    const identity = IDENTITY_MAP[selectedIdentity];
    state.user = { name, identity: selectedIdentity, identityLabel: identity.label, identityIcon: identity.icon };
    saveLocal();
    showToast(`Welcome, ${name}! You are becoming ${identity.label} 🚀`, 'purple');
    showScreen('screen-home');
    renderHome();
    maybeRequestNotificationPermissionAfterOnboarding();
    try {
        await api('POST', '/api/setup', { name, identity: selectedIdentity, identityLabel: identity.label, identityIcon: identity.icon });
    } catch(e) { /* non-critical */ }
}

// ══════════════════════════════════════════
//  HOME
// ══════════════════════════════════════════
function renderHome() {
    if (!state.user) { return; }
    const u = state.user;
    const todayKey = today();
    const completedTodayIds = (state.completions[todayKey] || []).map(String);
    const activeHabits = state.habits;

    const hour = new Date().getHours();
    const greet = hour < 12 ? 'Good morning' : hour < 17 ? 'Good afternoon' : 'Good evening';
    document.getElementById('home-greeting').textContent = `${greet}, ${u.name} 👋`;
    document.getElementById('home-date').textContent = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
    document.getElementById('home-avatar').textContent = u.name[0].toUpperCase();
    document.getElementById('stats-avatar').textContent = u.name[0].toUpperCase();

    const total = activeHabits.length;
    const done  = completedTodayIds.filter(id => activeHabits.some(h => String(h.id) === id)).length;
    const pct   = total ? Math.round((done / total) * 100) : 0;

    document.getElementById('done-count').textContent  = done;
    document.getElementById('total-count').textContent = total;
    document.getElementById('progress-bar').style.width = pct + '%';

    const identityData = IDENTITY_MAP[u.identity] || { label: u.identityLabel, icon: u.identityIcon };
    document.getElementById('identity-badge').textContent = `${identityData.icon} Becoming ${identityData.label}`;

    let sub = '';
    if (total === 0)       { sub = 'Add your first habit to get started.'; }
    else if (done === 0)   { sub = `${total} habit${total > 1 ? 's' : ''} waiting for you.`; }
    else if (done === total) { sub = 'All done! Perfect day. Keep the chain going! 🔥'; }
    else                   { sub = `${total - done} habit${(total - done) > 1 ? 's' : ''} to go — you can do this!`; }
    document.getElementById('progress-sub').textContent = sub;

    // Daily quote
    const qi = new Date().getDate() % QUOTES.length;
    document.getElementById('daily-quote').textContent = `"${QUOTES[qi]}"`;

    const list  = document.getElementById('habits-list');
    const empty = document.getElementById('empty-state');

    if (activeHabits.length === 0) {
        list.innerHTML = '';
        empty.style.display = 'block';
    } else {
        empty.style.display = 'none';
        const yesterdayKey = new Date(Date.now() - 86400000).toISOString().slice(0, 10);
        const completedYesterdayIds = (state.completions[yesterdayKey] || []).map(String);

        list.innerHTML = activeHabits.map(h => {
            const isDone      = completedTodayIds.includes(String(h.id));
            const streak      = state.streaks[h.id] || 0;
            const isAtRisk    = streak >= 3 && !isDone;
            const isGraceDay  = streak > 0 && !isDone && !completedYesterdayIds.includes(String(h.id));
            const streakClass = isGraceDay ? 'grace-day-text' : (isAtRisk ? 'at-risk-text' : '');
            const streakSuffix = isGraceDay
                ? ' · grace day active'
                : (isAtRisk ? ' · ⚠️ at risk!' : '');
            const streakHtml = streak > 0
                ? `<div class="habit-streak ${streakClass}">${getStreakEmoji(streak)} ${streak} day streak${streakSuffix}</div>`
                : '';
            return `
            <div class="habit-item ${isDone ? 'completed' : ''} ${isAtRisk ? 'at-risk' : ''}" id="item-${h.id}">
                <div class="habit-icon-wrap" style="background:${h.color}" onclick="showHabitDetail('${h.id}')">${h.emoji}</div>
                <div class="habit-info" onclick="showHabitDetail('${h.id}')">
                    <div class="habit-name">${h.name}</div>
                    <div class="habit-meta">${h.duration || 'Daily'} · ${capitalize(h.time)}</div>
                    ${streakHtml}
                </div>
                <div class="habit-check" onclick="toggleHabit('${h.id}')"></div>
            </div>`;
        }).join('');
    }
}

function capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

// ══════════════════════════════════════════
//  TOGGLE COMPLETION
// ══════════════════════════════════════════
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
        if (checkEl) { checkEl.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.4)' }, { transform: 'scale(1)' }], { duration: 300, easing: 'ease-out' }); }
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
        saveLocal();
        if (result.milestone && !wasCompleted) {
            setTimeout(() => showMilestone(result.milestone), 700);
        }
    } catch(e) { /* keep optimistic */ }
}

// ══════════════════════════════════════════
//  STREAK HELPERS
// ══════════════════════════════════════════
function getStreakEmoji(streak) {
    if (streak >= 100) { return '💥🔥💥'; }
    if (streak >= 60)  { return '⚡🔥⚡'; }
    if (streak >= 30)  { return '🔥🔥🔥'; }
    if (streak >= 14)  { return '🔥🔥'; }
    return '🔥';
}

function getMilestoneBadge(streak) {
    if (streak >= 100) { return '🌟 Legend — 100 days'; }
    if (streak >= 90)  { return '💎 Diamond — 90 days'; }
    if (streak >= 60)  { return '⚡ Elite — 60 days'; }
    if (streak >= 30)  { return '🏆 Champion — 30 days'; }
    if (streak >= 21)  { return '💪 Committed — 21 days'; }
    if (streak >= 14)  { return '🔥 On Fire — 14 days'; }
    if (streak >= 7)   { return '✨ Consistent — 7 days'; }
    return null;
}

function calcCompletionRate(id, days) {
    let count = 0;
    for (let i = 0; i < days; i++) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        const key = d.toISOString().slice(0, 10);
        if ((state.completions[key] || []).map(String).includes(String(id))) { count++; }
    }
    return Math.round((count / days) * 100);
}

function calcTotalCompletions(id) {
    let total = 0;
    Object.values(state.completions).forEach(arr => {
        if (arr.map(String).includes(String(id))) { total++; }
    });
    return total;
}

// ══════════════════════════════════════════
//  HABIT DETAIL
// ══════════════════════════════════════════
function showHabitDetail(id) {
    if (!id) { return; }
    currentDetailHabitId = id;
    const habit = state.habits.find(h => String(h.id) === String(id));
    if (!habit) { return; }

    const streak      = state.streaks[id] || 0;
    const bestStreak  = Math.max(state.bestStreaks[id] || 0, streak);
    const todayDone   = (state.completions[today()] || []).map(String).includes(String(id));
    const rate30      = calcCompletionRate(id, 30);
    const totalDone   = calcTotalCompletions(id);

    document.getElementById('detail-title').textContent       = `${habit.emoji} ${habit.name}`;
    document.getElementById('detail-fire').textContent        = streak > 0 ? getStreakEmoji(streak) : '💤';
    document.getElementById('detail-streak-num').textContent  = streak;
    document.getElementById('detail-best').textContent        = bestStreak;
    document.getElementById('detail-total').textContent       = totalDone;
    document.getElementById('detail-rate').textContent        = rate30 + '%';

    const badge    = getMilestoneBadge(streak);
    const badgeEl  = document.getElementById('detail-milestone');
    if (badge) { badgeEl.textContent = badge; badgeEl.style.display = 'inline-flex'; }
    else        { badgeEl.style.display = 'none'; }

    renderDetailHeatmap(id);
    document.getElementById('detail-insight').innerHTML = getInsightMessage(streak, habit.name);

    const setupFields = [
        { label: 'Your Why',          value: habit.why },
        { label: '2-Minute Version',  value: habit.twoMin },
        { label: 'Habit Stack',       value: habit.stack },
        { label: 'Temptation Bundle', value: habit.bundle },
        { label: 'Your Reward',       value: habit.reward },
    ].filter(f => f.value && f.value.trim() !== '');

    const setupCard = document.getElementById('detail-setup');
    if (setupFields.length > 0) {
        document.getElementById('detail-setup-fields').innerHTML = setupFields.map(f =>
            `<div class="setup-field">
                <div class="setup-field-label">${f.label}</div>
                <div class="setup-field-value">${f.value}</div>
            </div>`
        ).join('');
        setupCard.style.display = '';
    } else {
        setupCard.style.display = 'none';
    }

    const btn = document.getElementById('detail-complete-btn');
    if (todayDone) { btn.textContent = '✓ Completed Today!'; btn.classList.add('is-done'); }
    else           { btn.textContent = '✓ Complete for Today'; btn.classList.remove('is-done'); }

    renderDetailReminder(id);

    showScreen('screen-habit-detail');
}

function renderDetailHeatmap(id) {
    const container = document.getElementById('detail-heatmap');
    const todayStr  = today();
    const weeks     = [];

    for (let w = 0; w < 12; w++) {
        const col = [];
        for (let d = 6; d >= 0; d--) {
            const offset = (11 - w) * 7 + d;
            const date   = new Date();
            date.setDate(date.getDate() - offset);
            const dateStr = date.toISOString().slice(0, 10);
            col.push({
                date:    dateStr,
                done:    (state.completions[dateStr] || []).map(String).includes(String(id)),
                isToday: dateStr === todayStr,
            });
        }
        weeks.push(col);
    }

    container.innerHTML = weeks.map(col =>
        `<div class="heatmap-col">${col.map(day =>
            `<div class="heatmap-cell ${day.done ? 'done' : ''} ${day.isToday ? 'today' : ''}"></div>`
        ).join('')}</div>`
    ).join('');
}

function getInsightMessage(streak, habitName) {
    if (streak === 0) {
        return `<strong>Start today.</strong> "The best time to plant a tree was 20 years ago. The second best time is now." Every master was once a beginner.`;
    }
    if (streak < 7) {
        return `<strong>${streak} day${streak > 1 ? 's' : ''} in.</strong> Your brain is beginning to link the cue, routine, and reward. The habit loop is forming. Keep showing up.`;
    }
    if (streak < 14) {
        return `<strong>One week strong!</strong> You've cast ${streak} votes for your identity. Research shows the first week is the hardest — and you've done it. The automaticity effect has begun.`;
    }
    if (streak < 21) {
        return `<strong>Two weeks of consistency!</strong> Around day 18, automaticity starts to kick in. Your brain is literally rewiring itself for ${habitName}. You're almost to the threshold.`;
    }
    if (streak < 30) {
        return `<strong>3-week warrior!</strong> Studies by University College London show habits form in an average of 66 days. You're one-third of the way to fully automatic. Keep it up.`;
    }
    if (streak < 60) {
        return `<strong>30+ day champion!</strong> You've crossed the hardest barrier. At this point, missing feels harder than doing. You're building an identity, not just a habit.`;
    }
    return `<strong>You ARE this habit now.</strong> After ${streak} days, this is no longer something you do — it is who you are. James Clear would be proud. Keep the chain alive.`;
}

function toggleHabitFromDetail() {
    if (currentDetailHabitId) {
        toggleHabit(currentDetailHabitId);
        setTimeout(() => showHabitDetail(currentDetailHabitId), 400);
    }
}

function deleteHabitFromDetail() {
    if (!currentDetailHabitId) { return; }
    const habit = state.habits.find(h => String(h.id) === String(currentDetailHabitId));
    if (!confirm(`Delete "${habit?.name}"? This cannot be undone.`)) { return; }

    const deletedId = currentDetailHabitId;
    state.habits = state.habits.filter(h => String(h.id) !== String(deletedId));
    Object.keys(state.completions).forEach(date => {
        state.completions[date] = state.completions[date].filter(id => String(id) !== String(deletedId));
    });
    delete state.streaks[deletedId];
    delete state.bestStreaks[deletedId];
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

function showEditHabit(id) {
    if (!id) { return; }
    const habit = state.habits.find(h => String(h.id) === String(id));
    if (!habit) { return; }

    editingHabitId = id;

    // Populate the form fields from the habit in state
    resetAddForm();

    document.getElementById('new-name').value      = habit.name  || '';
    document.getElementById('new-why').value       = habit.why   || '';
    document.getElementById('new-bundle').value    = habit.bundle || '';
    document.getElementById('new-two-min').value   = habit.twoMin || '';
    document.getElementById('new-stack').value     = habit.stack || '';
    document.getElementById('new-duration').value  = habit.duration || '';
    document.getElementById('new-reward').value    = habit.reward || '';

    // Select the correct emoji
    const emojiBtn = document.querySelector(`.emoji-btn[data-emoji="${habit.emoji}"]`);
    if (emojiBtn) {
        document.querySelectorAll('.emoji-btn').forEach(b => b.classList.remove('selected'));
        emojiBtn.classList.add('selected');
    }
    newHabit.emoji = habit.emoji;

    // Select the correct color
    const colorBtn = document.querySelector(`.color-btn[data-color="${habit.color}"]`);
    if (colorBtn) {
        document.querySelectorAll('#color-grid .color-btn').forEach(b => b.classList.remove('selected'));
        colorBtn.classList.add('selected');
    }
    newHabit.color = habit.color;

    // Select the correct time
    const timeBtn = document.querySelector(`#time-grid .time-btn[data-time="${habit.time}"]`);
    if (timeBtn) {
        document.querySelectorAll('#time-grid .time-btn').forEach(b => b.classList.remove('selected'));
        timeBtn.classList.add('selected');
    }
    newHabit.time = habit.time || 'morning';

    // Select the correct difficulty
    const diffBtn = document.querySelector(`#diff-grid .time-btn[data-diff="${habit.diff}"]`);
    if (diffBtn) {
        document.querySelectorAll('#diff-grid .time-btn').forEach(b => b.classList.remove('selected'));
        diffBtn.classList.add('selected');
    }
    newHabit.diff = habit.diff || 'medium';

    // Update screen chrome for edit mode
    document.getElementById('add-screen-title').textContent = 'Edit Habit';
    document.getElementById('save-habit-btn').textContent   = '✓ Save Changes';
    document.getElementById('add-back-btn').onclick = () => showHabitDetail(id);

    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active', 'slide-left'));
    document.getElementById('screen-add').classList.add('active');
    window.scrollTo(0, 0);
}

// ══════════════════════════════════════════
//  ADD HABIT FORM
// ══════════════════════════════════════════
function resetAddForm() {
    newHabit = { name: '', emoji: '🏃', time: 'morning', why: '', bundle: '', color: '#1e3a2f', twoMin: '', stack: '', duration: '', reward: '', diff: 'medium' };
    goStep(0);
    ['new-name','new-why','new-bundle','new-two-min','new-stack','new-duration','new-reward'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.value = ''; }
    });
    document.querySelectorAll('.emoji-btn').forEach((b, i) => b.classList.toggle('selected', i === 0));
    document.querySelectorAll('#time-grid .time-btn').forEach((b, i) => b.classList.toggle('selected', i === 0));
    document.querySelectorAll('#color-grid .color-btn').forEach((b, i) => b.classList.toggle('selected', i === 0));
    document.querySelectorAll('#diff-grid .time-btn').forEach((b, i) => b.classList.toggle('selected', i === 1));
    document.getElementById('save-habit-btn').disabled = false;
    document.getElementById('save-habit-btn').textContent   = '✓ Create Habit';
    document.getElementById('add-screen-title').textContent = 'New Habit';
    document.getElementById('add-back-btn').onclick = () => showScreen('screen-home');
}

function goStep(n) {
    document.getElementById('add-step-' + currentStep).style.display = 'none';
    currentStep = n;
    document.getElementById('add-step-' + n).style.display = 'block';
    for (let i = 0; i < 4; i++) {
        const s = document.getElementById('step-' + i);
        s.classList.toggle('done', i < n);
        s.classList.toggle('active', i === n);
    }
    document.querySelector('.add-body').scrollTo(0, 0);
}

function selectEmoji(el) {
    document.querySelectorAll('.emoji-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    newHabit.emoji = el.dataset.emoji;
}

function selectTime(el) {
    document.querySelectorAll('#time-grid .time-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    newHabit.time = el.dataset.time;
}

function selectColor(el) {
    document.querySelectorAll('#color-grid .color-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    newHabit.color = el.dataset.color;
}

function selectDiff(el) {
    document.querySelectorAll('#diff-grid .time-btn').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    newHabit.diff = el.dataset.diff;
}

async function saveHabit() {
    const name = document.getElementById('new-name').value.trim();
    if (!name) { showToast('Please enter a habit name first', 'purple'); goStep(0); return; }

    document.getElementById('save-habit-btn').disabled = true;

    const habitData = {
        name,
        emoji:    newHabit.emoji,
        time:     newHabit.time,
        why:      document.getElementById('new-why').value.trim(),
        bundle:   document.getElementById('new-bundle').value.trim(),
        color:    newHabit.color,
        twoMin:   document.getElementById('new-two-min').value.trim(),
        stack:    document.getElementById('new-stack').value.trim(),
        duration: document.getElementById('new-duration').value.trim(),
        reward:   document.getElementById('new-reward').value.trim(),
        diff:     newHabit.diff,
    };

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
    const tempHabit = { ...habitData, id: tempId, createdAt: today() };
    state.habits.push(tempHabit);
    state.streaks[tempId] = 0;
    saveLocal();
    showToast(`${habitData.emoji} "${habitData.name}" added! Every rep is a vote.`);
    showScreen('screen-home');

    // Persist to backend and replace temp ID
    try {
        const result = await api('POST', '/api/habits', habitData);
        const idx = state.habits.findIndex(h => h.id === tempId);
        if (idx !== -1) { state.habits[idx] = result; }
        delete state.streaks[tempId];
        state.streaks[result.id] = 0;
        state.bestStreaks[result.id] = 0;
        saveLocal();
        renderHome();
    } catch(e) { /* keep optimistic */ }
}

// ══════════════════════════════════════════
//  STATS
// ══════════════════════════════════════════
function renderStats() {
    if (!state.user) { return; }

    const todayKey       = today();
    const activeHabits   = state.habits;
    const total          = activeHabits.length;
    const todayDone      = (state.completions[todayKey] || []).filter(id => activeHabits.some(h => String(h.id) === String(id))).length;
    const rate           = total ? Math.round((todayDone / total) * 100) : 0;
    const overallStreak  = calcOverallStreak();
    let   totalAllTime   = 0;
    Object.values(state.completions).forEach(arr => { totalAllTime += arr.filter(id => activeHabits.some(h => String(h.id) === String(id))).length; });

    document.getElementById('stat-streak').textContent = overallStreak;
    document.getElementById('stat-total').textContent  = totalAllTime;
    document.getElementById('stat-rate').textContent   = rate + '%';

    // Compound chart (1% improvement model)
    const chart  = document.getElementById('compound-chart');
    const points = [1, 4, 8, 13, 26, 52];
    const maxVal = Math.pow(1.01, 365);
    chart.innerHTML = points.map(week => {
        const val = Math.pow(1.01, week * 7);
        return `<div class="compound-bar" style="height:${Math.max(4, (val / maxVal) * 100)}%"></div>`;
    }).join('');

    // Weekly grid
    const weekGrid = document.getElementById('weekly-grid');
    const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const now = new Date();
    weekGrid.innerHTML = dayNames.map((label, i) => {
        const d = new Date(now);
        d.setDate(now.getDate() - now.getDay() + i);
        const key  = d.toISOString().slice(0, 10);
        const comp = (state.completions[key] || []).filter(id => activeHabits.some(h => String(h.id) === String(id))).length;
        const cls  = total > 0 && comp >= total ? 'done' : comp > 0 ? 'partial' : '';
        return `<div class="week-day"><div class="week-day-label">${label}</div><div class="week-dot ${cls}"></div></div>`;
    }).join('');

    // Per-habit breakdown
    const breakdownCard = document.getElementById('habit-breakdown-card');
    const breakdown     = document.getElementById('habit-breakdown');
    if (activeHabits.length > 0) {
        breakdownCard.style.display = 'block';
        breakdown.innerHTML = activeHabits.map(h => {
            const streak = state.streaks[h.id] || 0;
            const rate30 = calcCompletionRate(h.id, 30);
            return `<div class="identity-item" onclick="showHabitDetail('${h.id}')">
                <div class="identity-icon">${h.emoji}</div>
                <div class="identity-info">
                    <div class="identity-name">${h.name}</div>
                    <div class="identity-votes">${streak > 0 ? getStreakEmoji(streak) + ' ' + streak + ' day streak · ' : ''}${rate30}% this month</div>
                    <div class="identity-bar"><div class="identity-bar-fill" style="width:${rate30}%"></div></div>
                </div>
            </div>`;
        }).join('');
    } else {
        breakdownCard.style.display = 'none';
    }

    // Identity votes
    const votesList = document.getElementById('identity-votes-list');
    if (activeHabits.length === 0) {
        votesList.innerHTML = '<p style="color:#444;font-size:.8rem;padding:.5rem 0;">Add habits to track identity votes.</p>';
        return;
    }
    const u        = state.user;
    let voteCount  = 0;
    Object.values(state.completions).forEach(arr => {
        voteCount += arr.filter(id => activeHabits.some(h => String(h.id) === String(id))).length;
    });
    const identityData = IDENTITY_MAP[u.identity] || { label: u.identityLabel, icon: u.identityIcon };
    votesList.innerHTML = `<div class="identity-item" style="cursor:default;">
        <div class="identity-icon">${identityData.icon}</div>
        <div class="identity-info">
            <div class="identity-name">${identityData.label}</div>
            <div class="identity-votes">${voteCount} vote${voteCount !== 1 ? 's' : ''} cast for your identity</div>
            <div class="identity-bar"><div class="identity-bar-fill" style="width:${Math.min(100, voteCount)}%"></div></div>
        </div>
    </div>`;
}

function calcOverallStreak() {
    let streak = 0;
    const d = new Date();
    while (true) {
        const key  = d.toISOString().slice(0, 10);
        const comp = (state.completions[key] || []).filter(id => state.habits.some(h => String(h.id) === String(id)));
        if (comp.length > 0) { streak++; d.setDate(d.getDate() - 1); }
        else if (key === today()) { break; }
        else { break; }
    }
    return streak;
}

// ══════════════════════════════════════════
//  MILESTONE CELEBRATION
// ══════════════════════════════════════════
function showMilestone(days) {
    const m = MILESTONES.find(x => x.days === days) || MILESTONES[0];
    document.getElementById('milestone-emoji').textContent = m.emoji;
    document.getElementById('milestone-title').textContent = m.title;
    document.getElementById('milestone-sub').textContent   = m.sub;
    document.getElementById('milestone-quote').textContent = `"${m.quote}"`;
    document.getElementById('milestone-overlay').classList.add('show');
}

function closeMilestone() {
    document.getElementById('milestone-overlay').classList.remove('show');
}

// ══════════════════════════════════════════
//  TOAST
// ══════════════════════════════════════════
let toastTimer;
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

// reminder state: { [habitId]: boolean } — stored in localStorage
function loadReminders() {
    try { return JSON.parse(localStorage.getItem('atomicme_reminders') || '{}'); } catch(e) { return {}; }
}
function saveReminders(reminders) {
    try { localStorage.setItem('atomicme_reminders', JSON.stringify(reminders)); } catch(e) {}
}

function notificationPermission() {
    if (typeof Notification === 'undefined') { return 'unsupported'; }
    return Notification.permission;
}

async function requestNotificationPermission() {
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

// Also try the NativePHP bridge for native scheduling (fails silently in browser).
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

function getTimeLabel(time) {
    const labels = { morning: 'Morning', afternoon: 'Afternoon', evening: 'Evening', anytime: 'Daily' };
    return labels[time] || 'Daily';
}

// Returns a HH:MM string for the preferred time of day (used for display + native scheduling).
function getNotificationTime(time) {
    const times = { morning: '08:00', afternoon: '13:00', evening: '19:00', anytime: '09:00' };
    return times[time] || '09:00';
}

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

function cancelLocalNotification(habitId) {
    bridgeCancelNotification(habitId);
}

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
        showToast(`🔔 Reminder set for ${getNotificationTime(habit.time)} (${getTimeLabel(habit.time)})`, 'purple');
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

function renderDetailReminder(id) {
    const habit = state.habits.find(h => String(h.id) === String(id));
    if (!habit) { return; }

    const reminders = loadReminders();
    const isEnabled = !!reminders[id];
    const toggle = document.getElementById('detail-reminder-toggle');
    if (toggle) { toggle.checked = isEnabled; }

    const timeSub = document.getElementById('detail-reminder-time');
    if (timeSub) {
        timeSub.textContent = getNotificationTime(habit.time) + ' (' + getTimeLabel(habit.time) + ')';
    }

    // Show the in-detail permission banner if permission not yet determined
    const perm = notificationPermission();
    const detailBanner = document.getElementById('reminder-permission-banner');
    if (detailBanner) {
        const shouldShow = perm !== 'granted' && perm !== 'denied' && perm !== 'unsupported';
        detailBanner.classList.toggle('show', shouldShow);
    }
}

// Request permission after onboarding (best-effort, no-op if not supported).
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

// Returns the ISO date string for the most recent Monday (start of current week).
function currentWeekOf() {
    const d = new Date();
    const day = d.getDay(); // 0 = Sunday, 1 = Monday, ...
    const diff = (day === 0) ? -6 : 1 - day;
    d.setDate(d.getDate() + diff);
    return d.toISOString().slice(0, 10);
}

function loadLastReviewedWeek() {
    try { return localStorage.getItem('atomicme_last_reviewed_week') || null; } catch(e) { return null; }
}

function saveLastReviewedWeek(weekOf) {
    try { localStorage.setItem('atomicme_last_reviewed_week', weekOf); } catch(e) {}
}

// Returns true if the weekly review overlay should appear.
function shouldShowWeeklyReview() {
    if (!state.user || state.habits.length === 0) { return false; }

    const thisWeek = currentWeekOf();
    const lastReviewed = loadLastReviewedWeek();

    // Already reviewed or skipped this week — do not show again.
    if (lastReviewed === thisWeek) { return false; }

    const now = new Date();
    const dayOfWeek = now.getDay(); // 0 = Sunday

    // Show on Sunday (day 0) or if it has been 7+ days since last review.
    if (dayOfWeek === 0) { return true; }

    if (lastReviewed) {
        const lastDate = new Date(lastReviewed);
        const daysSince = Math.floor((now - lastDate) / 86400000);
        if (daysSince >= 7) { return true; }
    }

    return false;
}

function maybeShowWeeklyReview() {
    if (!shouldShowWeeklyReview()) { return; }
    // Delay so it appears after home renders.
    setTimeout(openWeeklyReview, 1200);
}

function openWeeklyReview() {
    if (!state.user) { return; }

    // Build the weekly habit completion summary.
    const weekOf = currentWeekOf();
    const weekStart = new Date(weekOf);
    const days = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(weekStart);
        d.setDate(weekStart.getDate() + i);
        days.push(d.toISOString().slice(0, 10));
    }

    const listEl = document.getElementById('wr-habit-list');
    listEl.innerHTML = '';

    state.habits.forEach(habit => {
        const doneCount = days.filter(d => (state.completions[d] || []).some(id => String(id) === String(habit.id))).length;
        const pct = Math.round((doneCount / 7) * 100);
        const pctClass = pct >= 71 ? 'good' : pct >= 43 ? 'ok' : 'low';
        const row = document.createElement('div');
        row.className = 'wr-habit-row';
        row.innerHTML = `
            <div class="wr-habit-left"><span>${habit.emoji}</span><span>${habit.name}</span></div>
            <div class="wr-habit-pct ${pctClass}">${doneCount}/7 days</div>
        `;
        listEl.appendChild(row);
    });

    document.getElementById('wr-note').value = '';
    document.getElementById('weekly-review-overlay').classList.add('show');
}

function closeWeeklyReview() {
    document.getElementById('weekly-review-overlay').classList.remove('show');
}

function skipWeeklyReview() {
    saveLastReviewedWeek(currentWeekOf());
    closeWeeklyReview();
    showToast('Review skipped. See you next week!');
}

async function saveWeeklyReview() {
    const note = document.getElementById('wr-note').value.trim();
    const weekOf = currentWeekOf();

    // Optimistic dismiss.
    saveLastReviewedWeek(weekOf);
    closeWeeklyReview();
    showToast('Reflection saved!', 'purple');

    try {
        await api('POST', '/api/reflections', { week_of: weekOf, note });
    } catch(e) { /* non-critical — local state already updated */ }
}
</script>
</body>
</html>

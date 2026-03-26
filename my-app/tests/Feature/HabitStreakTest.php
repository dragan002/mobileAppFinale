<?php

use App\Models\Habit;
use App\Models\HabitCompletion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper to create a habit with minimal required fields.
function makeHabit(): Habit
{
    return Habit::create([
        'name' => 'Test Habit',
        'emoji' => '✅',
        'color' => '#1e3a2f',
        'time_of_day' => 'morning',
        'difficulty' => 'medium',
    ]);
}

// Helper to record completions on specific dates.
function complete(Habit $habit, array $dates): void
{
    foreach ($dates as $date) {
        HabitCompletion::create([
            'habit_id' => $habit->id,
            'completed_date' => $date,
        ]);
    }
}

describe('Habit::calculateStreak()', function () {
    it('returns zero when there are no completions', function () {
        $habit = makeHabit();

        expect($habit->calculateStreak())->toBe(0);
    });

    it('counts consecutive days ending today', function () {
        $habit = makeHabit();
        complete($habit, [
            today()->toDateString(),
            today()->subDay()->toDateString(),
            today()->subDays(2)->toDateString(),
        ]);

        expect($habit->calculateStreak())->toBe(3);
    });

    it('counts consecutive days ending yesterday when today is not yet done', function () {
        $habit = makeHabit();
        // Today not completed — grace absorbs the gap to yesterday.
        complete($habit, [
            today()->subDay()->toDateString(),
            today()->subDays(2)->toDateString(),
            today()->subDays(3)->toDateString(),
        ]);

        expect($habit->calculateStreak())->toBe(3);
    });

    it('keeps the streak alive across a single missed day inside the streak', function () {
        $habit = makeHabit();
        // Today done, yesterday missed (grace), two days ago done, three days ago done.
        complete($habit, [
            today()->toDateString(),
            today()->subDays(2)->toDateString(),
            today()->subDays(3)->toDateString(),
        ]);

        expect($habit->calculateStreak())->toBe(3);
    });

    it('resets the streak when two consecutive days are missed inside the streak', function () {
        $habit = makeHabit();
        // Today done, yesterday and two days ago both missed, three days ago done.
        complete($habit, [
            today()->toDateString(),
            today()->subDays(3)->toDateString(),
            today()->subDays(4)->toDateString(),
        ]);

        expect($habit->calculateStreak())->toBe(1);
    });

    it('does not allow the grace day to be used more than once', function () {
        $habit = makeHabit();
        // Two separate single-day gaps — only the first one gets grace.
        complete($habit, [
            today()->toDateString(),
            today()->subDays(2)->toDateString(),  // gap at -1 (grace used here)
            today()->subDays(4)->toDateString(),  // gap at -3 (no grace left)
            today()->subDays(5)->toDateString(),
        ]);

        // Streak should include today + grace + subDays(2) then stop at the second gap.
        expect($habit->calculateStreak())->toBe(2);
    });

    it('returns one when only today is completed', function () {
        $habit = makeHabit();
        complete($habit, [today()->toDateString()]);

        expect($habit->calculateStreak())->toBe(1);
    });
});

describe('Habit::calculateBestStreak()', function () {
    it('returns zero when there are no completions', function () {
        $habit = makeHabit();

        expect($habit->calculateBestStreak())->toBe(0);
    });

    it('counts a fully consecutive run', function () {
        $habit = makeHabit();
        complete($habit, [
            '2026-01-01',
            '2026-01-02',
            '2026-01-03',
            '2026-01-04',
        ]);

        expect($habit->calculateBestStreak())->toBe(4);
    });

    it('keeps the best streak alive across a single missed day', function () {
        $habit = makeHabit();
        // Jan 1, Jan 2, (Jan 3 missed — grace), Jan 4, Jan 5.
        complete($habit, [
            '2026-01-01',
            '2026-01-02',
            '2026-01-04',
            '2026-01-05',
        ]);

        expect($habit->calculateBestStreak())->toBe(4);
    });

    it('resets the best streak when two consecutive days are missed', function () {
        $habit = makeHabit();
        // Jan 1, Jan 2, (Jan 3 and 4 missed), Jan 5.
        complete($habit, [
            '2026-01-01',
            '2026-01-02',
            '2026-01-05',
        ]);

        expect($habit->calculateBestStreak())->toBe(2);
    });

    it('does not allow the grace to be used more than once in a single run', function () {
        $habit = makeHabit();
        // Jan 1, (Jan 2 missed — grace), Jan 3, (Jan 4 missed — no grace left), Jan 5.
        complete($habit, [
            '2026-01-01',
            '2026-01-03',
            '2026-01-05',
        ]);

        // Grace bridges Jan 1-3 (streak=2), then Jan 4 gap breaks it.
        // Jan 5 starts a new streak of 1. Best = 2.
        expect($habit->calculateBestStreak())->toBe(2);
    });

    it('returns the longer of two separate runs', function () {
        $habit = makeHabit();
        // Short run: Jan 1-2. Long run with grace: Feb 1, (Feb 2 missed), Feb 3, Feb 4, Feb 5.
        complete($habit, [
            '2026-01-01',
            '2026-01-02',
            '2026-02-01',
            '2026-02-03',
            '2026-02-04',
            '2026-02-05',
        ]);

        expect($habit->calculateBestStreak())->toBe(4);
    });
});

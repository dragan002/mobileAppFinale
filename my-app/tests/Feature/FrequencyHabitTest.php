<?php

use App\Models\Habit;
use App\Models\HabitCompletion;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Helpers ─────────────────────────────────────────────────────────────────

function makeFreqHabit(int $targetDaysPerWeek = 3): Habit
{
    return Habit::create([
        'name' => 'Frequency Habit',
        'emoji' => '🏋️',
        'color' => '#1e3a2f',
        'time_of_day' => 'morning',
        'difficulty' => 'medium',
        'target_days_per_week' => $targetDaysPerWeek,
    ]);
}

function completeFreq(Habit $habit, array $dates): void
{
    foreach ($dates as $date) {
        HabitCompletion::create([
            'habit_id' => $habit->id,
            'completed_date' => $date,
        ]);
    }
}

// ── Migration sanity ─────────────────────────────────────────────────────────

describe('target_days_per_week column', function () {
    it('defaults to 7 for daily habits', function () {
        $habit = Habit::create([
            'name' => 'Daily',
            'emoji' => '⭐',
            'color' => '#1e3a2f',
            'time_of_day' => 'morning',
            'difficulty' => 'medium',
        ]);

        expect($habit->target_days_per_week)->toBe(7);
    });

    it('stores a custom frequency', function () {
        $habit = makeFreqHabit(3);

        expect($habit->target_days_per_week)->toBe(3);
    });
});

// ── calculateStreakData() — daily habits ────────────────────────────────────

describe('calculateStreakData() for daily habits (target = 7)', function () {
    it('returns days unit for daily habits', function () {
        $habit = makeFreqHabit(7);
        completeFreq($habit, [today()->toDateString()]);

        $data = $habit->calculateStreakData();

        expect($data['unit'])->toBe('days');
        expect($data['value'])->toBe(1);
    });

    it('backward-compatible: calculateStreak() still returns integer', function () {
        $habit = makeFreqHabit(7);
        completeFreq($habit, [
            today()->toDateString(),
            today()->subDay()->toDateString(),
        ]);

        expect($habit->calculateStreak())->toBe(2);
    });
});

// ── calculateStreakData() — frequency habits ────────────────────────────────

describe('calculateStreakData() for frequency habits (target < 7)', function () {
    it('returns weeks unit for frequency habits', function () {
        $habit = makeFreqHabit(3);

        $data = $habit->calculateStreakData();

        expect($data['unit'])->toBe('weeks');
    });

    it('returns zero when there are no completions', function () {
        $habit = makeFreqHabit(3);

        $data = $habit->calculateStreakData();

        expect($data['value'])->toBe(0);
    });

    it('counts a week where the target is fully met', function () {
        $habit = makeFreqHabit(3);

        $weekStart = Carbon::today()->startOfWeek();
        completeFreq($habit, [
            $weekStart->toDateString(),
            $weekStart->copy()->addDay()->toDateString(),
            $weekStart->copy()->addDays(2)->toDateString(),
        ]);

        $data = $habit->calculateStreakData();

        expect($data['value'])->toBe(1);
    });

    it('does not count a week where the target is two or more short', function () {
        $habit = makeFreqHabit(3);

        $weekStart = Carbon::today()->startOfWeek();
        completeFreq($habit, [
            $weekStart->toDateString(),
        ]);

        $data = $habit->calculateStreakData();

        expect($data['value'])->toBe(0);
    });

    it('applies grace when exactly one day short of target within a week', function () {
        $habit = makeFreqHabit(3);

        $weekStart = Carbon::today()->startOfWeek();
        completeFreq($habit, [
            $weekStart->toDateString(),
            $weekStart->copy()->addDay()->toDateString(),
        ]);

        $data = $habit->calculateStreakData();

        expect($data['value'])->toBe(1);
        expect($data['graceDayActive'])->toBeTrue();
    });

    it('grace day can only be used once across consecutive weeks', function () {
        $habit = makeFreqHabit(3);

        $thisWeekStart = Carbon::today()->startOfWeek();
        $lastWeekStart = $thisWeekStart->copy()->subWeek();
        $twoWeeksAgo = $thisWeekStart->copy()->subWeeks(2);

        // This week: 2/3 (one short — uses the grace day)
        completeFreq($habit, [
            $thisWeekStart->toDateString(),
            $thisWeekStart->copy()->addDay()->toDateString(),
        ]);

        // Last week: 2/3 (one short — grace already spent, streak breaks here)
        completeFreq($habit, [
            $lastWeekStart->toDateString(),
            $lastWeekStart->copy()->addDay()->toDateString(),
        ]);

        // Two weeks ago: 3/3 (fully met, but unreachable because last week broke the streak)
        completeFreq($habit, [
            $twoWeeksAgo->toDateString(),
            $twoWeeksAgo->copy()->addDay()->toDateString(),
            $twoWeeksAgo->copy()->addDays(2)->toDateString(),
        ]);

        $data = $habit->calculateStreakData();

        // Walk backwards: this week (grace=1) → last week (no grace, break). Streak = 1.
        expect($data['value'])->toBe(1);
    });

    it('counts multiple consecutive weeks where target is met', function () {
        $habit = makeFreqHabit(2);

        $thisWeekStart = Carbon::today()->startOfWeek();

        for ($w = 0; $w < 3; $w++) {
            $ws = $thisWeekStart->copy()->subWeeks($w);
            completeFreq($habit, [
                $ws->toDateString(),
                $ws->copy()->addDay()->toDateString(),
            ]);
        }

        $data = $habit->calculateStreakData();

        expect($data['value'])->toBe(3);
    });
});

// ── calculateBestStreakData() — frequency habits ────────────────────────────

describe('calculateBestStreakData() for frequency habits', function () {
    it('returns zero with no completions', function () {
        $habit = makeFreqHabit(3);

        $data = $habit->calculateBestStreakData();

        expect($data['value'])->toBe(0);
        expect($data['unit'])->toBe('weeks');
    });

    it('tracks the best run across multiple separate periods', function () {
        $habit = makeFreqHabit(2);

        // Period 1 (two weeks met): Jan 6 and Jan 13 weeks
        completeFreq($habit, ['2026-01-06', '2026-01-07', '2026-01-13', '2026-01-14']);

        // Period 2 (three weeks met): Feb 3, Feb 10, Feb 17 weeks
        completeFreq($habit, [
            '2026-02-03', '2026-02-04',
            '2026-02-10', '2026-02-11',
            '2026-02-17', '2026-02-18',
        ]);

        $data = $habit->calculateBestStreakData();

        expect($data['value'])->toBe(3);
    });
});

// ── HabitController store/update ─────────────────────────────────────────────

describe('POST /api/habits with targetDaysPerWeek', function () {
    it('saves the target frequency on creation', function () {
        $response = $this->postJson('/api/habits', [
            'name' => 'Run 3x',
            'emoji' => '🏃',
            'color' => '#1e3a2f',
            'targetDaysPerWeek' => 3,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('targetDaysPerWeek', 3);

        expect(Habit::first()->target_days_per_week)->toBe(3);
    });

    it('defaults to 7 when targetDaysPerWeek is not provided', function () {
        $response = $this->postJson('/api/habits', [
            'name' => 'Daily walk',
            'emoji' => '🚶',
            'color' => '#1e3a2f',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('targetDaysPerWeek', 7);
    });

    it('rejects targetDaysPerWeek outside 1-7 range', function () {
        $response = $this->postJson('/api/habits', [
            'name' => 'Bad Habit',
            'emoji' => '❌',
            'color' => '#1e3a2f',
            'targetDaysPerWeek' => 8,
        ]);

        $response->assertUnprocessable();
    });
});

describe('PUT /api/habits/{habit} with targetDaysPerWeek', function () {
    it('updates the frequency on an existing habit', function () {
        $habit = makeFreqHabit(3);

        $response = $this->putJson("/api/habits/{$habit->id}", [
            'name' => 'Run',
            'emoji' => '🏃',
            'color' => '#1e3a2f',
            'targetDaysPerWeek' => 5,
        ]);

        $response->assertOk();
        $response->assertJsonPath('targetDaysPerWeek', 5);

        expect($habit->fresh()->target_days_per_week)->toBe(5);
    });
});

// ── CompletionController toggle — streakData in response ────────────────────

describe('POST /api/completions/toggle streakData', function () {
    it('returns streakData with weeks unit for frequency habits', function () {
        $habit = makeFreqHabit(2);

        $response = $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);

        $response->assertOk();
        $response->assertJsonStructure(['streakData' => ['value', 'unit', 'graceDayActive']]);
        $response->assertJsonPath('streakData.unit', 'weeks');
    });

    it('returns streakData with days unit for daily habits', function () {
        $habit = makeFreqHabit(7);

        $response = $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);

        $response->assertOk();
        $response->assertJsonPath('streakData.unit', 'days');
    });

    it('returns a daily milestone when a daily habit crosses a day threshold', function () {
        $habit = makeFreqHabit(7);

        // Build 6-day streak then toggle today to hit 7
        $dates = array_map(
            fn ($i) => today()->subDays($i)->toDateString(),
            range(1, 6)
        );
        completeFreq($habit, $dates);

        $response = $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);

        $response->assertOk();
        $response->assertJsonPath('milestone', 7);
        $response->assertJsonPath('streakData.unit', 'days');
    });
});

// ── StateController — streakData included in /api/state ──────────────────────

describe('GET /api/state includes streakData', function () {
    it('includes streakData and bestStreakData for each habit', function () {
        UserProfile::create([
            'name' => 'Tester',
            'identity' => 'athlete',
            'identity_label' => 'The Athlete',
            'identity_icon' => '🏃',
        ]);

        $habit = makeFreqHabit(3);

        $response = $this->getJson('/api/state');

        $response->assertOk();
        $response->assertJsonStructure(['streakData', 'bestStreakData']);

        $streakData = $response->json('streakData');
        expect($streakData)->toHaveKey((string) $habit->id);
        expect($streakData[(string) $habit->id])->toHaveKeys(['value', 'unit', 'graceDayActive']);
        expect($streakData[(string) $habit->id]['unit'])->toBe('weeks');
    });
});

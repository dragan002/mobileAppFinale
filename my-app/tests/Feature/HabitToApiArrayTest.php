<?php

use App\Models\Habit;
use App\Models\HabitCompletion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────────────────────
//  Helpers
// ─────────────────────────────────────────────────────────────

function apiArrayHabit(array $overrides = []): Habit
{
    return Habit::create(array_merge([
        'name' => 'Read Books',
        'emoji' => '📚',
        'color' => '#1e1a3a',
        'time_of_day' => 'evening',
        'difficulty' => 'easy',
    ], $overrides));
}

// ─────────────────────────────────────────────────────────────
//  Test 5 — Habit::toApiArray() with pre-computed data
// ─────────────────────────────────────────────────────────────

describe('Habit::toApiArray()', function () {
    it('returns the correct structure when pre-computed streak data is passed', function () {
        $habit = apiArrayHabit();

        $streakData = [
            'value' => 5,
            'unit' => 'days',
            'graceDayActive' => false,
        ];

        $bestStreakData = [
            'value' => 10,
            'unit' => 'days',
        ];

        $result = $habit->toApiArray($streakData, $bestStreakData);

        expect($result)->toMatchArray([
            'id' => $habit->id,
            'name' => 'Read Books',
            'emoji' => '📚',
            'color' => '#1e1a3a',
            'time' => 'evening',
            'diff' => 'easy',
            'streakData' => $streakData,
            'bestStreakData' => $bestStreakData,
            'targetDaysPerWeek' => 7,
        ]);
    });

    it('maps DB column names to camelCase JS field names', function () {
        $habit = apiArrayHabit([
            'why' => 'To grow',
            'two_min_version' => 'Just read one page',
            'stack' => 'After dinner',
            'bundle' => 'With tea',
            'reward' => 'Nice snack',
            'duration' => '20 minutes',
            'reminder_time' => '20:00',
        ]);

        $result = $habit->toApiArray(
            ['value' => 0, 'unit' => 'days', 'graceDayActive' => false],
            ['value' => 0, 'unit' => 'days']
        );

        expect($result)->toMatchArray([
            'why' => 'To grow',
            'twoMin' => 'Just read one page',
            'stack' => 'After dinner',
            'bundle' => 'With tea',
            'reward' => 'Nice snack',
            'duration' => '20 minutes',
            'reminderTime' => '20:00',
        ]);

        // Verify the raw DB column names are NOT exposed directly
        expect($result)->not->toHaveKey('time_of_day');
        expect($result)->not->toHaveKey('two_min_version');
        expect($result)->not->toHaveKey('reminder_time');
    });

    it('returns empty strings for optional nullable fields when not set', function () {
        $habit = apiArrayHabit();
        $result = $habit->toApiArray(
            ['value' => 0, 'unit' => 'days', 'graceDayActive' => false],
            ['value' => 0, 'unit' => 'days']
        );

        expect($result['why'])->toBe('');
        expect($result['twoMin'])->toBe('');
        expect($result['stack'])->toBe('');
        expect($result['bundle'])->toBe('');
        expect($result['reward'])->toBe('');
        expect($result['duration'])->toBe('');
        expect($result['reminderTime'])->toBe('');
    });

    it('does not issue streak-related DB queries when pre-computed data is supplied', function () {
        $habit = apiArrayHabit();

        $streakData = ['value' => 3, 'unit' => 'days', 'graceDayActive' => false];
        $bestStreakData = ['value' => 7, 'unit' => 'days'];

        DB::enableQueryLog();
        $habit->toApiArray($streakData, $bestStreakData);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // No query should be selecting completions ordered by completed_date for streak computation
        $streakRelatedQueries = array_filter(
            $queries,
            fn ($q) => str_contains(strtolower($q['query']), 'completed_date')
                && str_contains(strtolower($q['query']), 'order')
        );

        expect($streakRelatedQueries)->toBeEmpty();
    });

    it('falls back to live DB queries when no pre-computed data is provided', function () {
        $habit = apiArrayHabit();

        HabitCompletion::create([
            'habit_id' => $habit->id,
            'completed_date' => today()->toDateString(),
        ]);

        DB::enableQueryLog();
        $result = $habit->toApiArray(); // no pre-computed data
        DB::disableQueryLog();

        // At least one query must have been issued to calculate the streak
        expect(DB::getQueryLog())->not->toBeEmpty();

        // The computed streak should reflect the single completion
        expect($result['streakData']['value'])->toBe(1);
        expect($result['bestStreakData']['value'])->toBe(1);
    });

    it('includes a phase key with all required sub-keys', function () {
        $habit = apiArrayHabit();
        $result = $habit->toApiArray(
            ['value' => 1, 'unit' => 'days', 'graceDayActive' => false],
            ['value' => 1, 'unit' => 'days']
        );

        expect($result)->toHaveKey('phase');
        expect($result['phase'])->toHaveKeys(['phase', 'label', 'description', 'icon']);
    });

    it('includes categoryId as null when no category is assigned', function () {
        $habit = apiArrayHabit();
        $result = $habit->toApiArray(
            ['value' => 0, 'unit' => 'days', 'graceDayActive' => false],
            ['value' => 0, 'unit' => 'days']
        );

        expect($result)->toHaveKey('categoryId');
        expect($result['categoryId'])->toBeNull();
    });

    it('includes createdAt formatted as Y-m-d', function () {
        $habit = apiArrayHabit();
        $result = $habit->toApiArray(
            ['value' => 0, 'unit' => 'days', 'graceDayActive' => false],
            ['value' => 0, 'unit' => 'days']
        );

        expect($result['createdAt'])->toMatch('/^\d{4}-\d{2}-\d{2}$/');
    });
});

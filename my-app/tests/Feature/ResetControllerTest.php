<?php

use App\Models\Category;
use App\Models\Habit;
use App\Models\HabitCompletion;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeProfileForReset(): UserProfile
{
    return UserProfile::create([
        'name'           => 'Reset User',
        'identity'       => 'athlete',
        'identity_label' => 'The Athlete',
        'identity_icon'  => '🏃',
    ]);
}

function makeHabitForReset(): Habit
{
    return Habit::create([
        'name'               => 'Test Habit',
        'emoji'              => '🏃',
        'color'              => '#1e3a2f',
        'time_of_day'        => 'morning',
        'why'                => '',
        'two_min_version'    => '',
        'stack'              => '',
        'bundle'             => '',
        'reward'             => '',
        'difficulty'         => 'medium',
    ]);
}

describe('DELETE /api/reset', function () {
    it('deletes all habit_completions, habits, and user_profile rows', function () {
        $profile = makeProfileForReset();
        $habit   = makeHabitForReset();
        HabitCompletion::create(['habit_id' => $habit->id, 'completed_date' => '2026-03-25']);

        $response = $this->deleteJson('/api/reset');

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseCount('user_profile', 0);
        $this->assertDatabaseCount('habits', 0);
        $this->assertDatabaseCount('habit_completions', 0);
    });

    it('returns ok when there is no data to delete', function () {
        $response = $this->deleteJson('/api/reset');

        $response->assertOk()->assertJson(['ok' => true]);
    });

    it('deletes multiple habits and their completions', function () {
        makeProfileForReset();
        $habit1 = makeHabitForReset();
        $habit2 = Habit::create([
            'name'               => 'Second Habit',
            'emoji'              => '📚',
            'color'              => '#1e1a3a',
            'time_of_day'        => 'evening',
            'why'                => '',
            'two_min_version'    => '',
            'stack'              => '',
            'bundle'             => '',
            'reward'             => '',
            'difficulty'         => 'easy',
        ]);

        HabitCompletion::create(['habit_id' => $habit1->id, 'completed_date' => '2026-03-24']);
        HabitCompletion::create(['habit_id' => $habit1->id, 'completed_date' => '2026-03-25']);
        HabitCompletion::create(['habit_id' => $habit2->id, 'completed_date' => '2026-03-25']);

        $this->deleteJson('/api/reset')->assertOk();

        $this->assertDatabaseCount('habits', 0);
        $this->assertDatabaseCount('habit_completions', 0);
        $this->assertDatabaseCount('user_profile', 0);
    });

    it('does not delete preset categories on reset', function () {
        // Seed presets
        $presets = [
            ['name' => 'Morning Routine', 'color' => '#f97316', 'sort_order' => 1],
            ['name' => 'Evening Routine', 'color' => '#7c3aed', 'sort_order' => 2],
        ];

        foreach ($presets as $preset) {
            Category::create([
                ...$preset,
                'user_profile_id' => null,
                'is_preset' => true,
            ]);
        }

        makeProfileForReset();
        makeHabitForReset();

        $presetCount = Category::where('is_preset', true)->count();
        expect($presetCount)->toBe(2);

        $this->deleteJson('/api/reset')->assertOk();

        // Presets should still exist
        expect(Category::where('is_preset', true)->count())->toBe($presetCount);
    });

    it('deletes user created categories on reset', function () {
        $profile = makeProfileForReset();

        $userCategory = Category::create([
            'user_profile_id' => $profile->id,
            'name' => 'Custom',
            'color' => '#ff0000',
            'is_preset' => false,
        ]);

        expect(Category::where('is_preset', false)->count())->toBe(1);

        $this->deleteJson('/api/reset')->assertOk();

        expect(Category::where('is_preset', false)->count())->toBe(0);
    });
});

<?php

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
        'habit_stack'        => '',
        'temptation_bundle'  => '',
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
            'habit_stack'        => '',
            'temptation_bundle'  => '',
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
});

<?php

use App\Models\Habit;
use App\Models\HabitCompletion;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─────────────────────────────────────────────────────────────
//  Shared helpers (prefixed to avoid collisions with other files)
// ─────────────────────────────────────────────────────────────

function flowUser(string $name = 'Alex', string $identity = 'athlete'): UserProfile
{
    return UserProfile::create([
        'name' => $name,
        'identity' => $identity,
        'identity_label' => 'The Athlete',
        'identity_icon' => '🏃',
    ]);
}

function flowHabit(string $name = 'Morning Run'): Habit
{
    return Habit::create([
        'name' => $name,
        'emoji' => '🏃',
        'color' => '#1e3a2f',
        'time_of_day' => 'morning',
        'difficulty' => 'medium',
    ]);
}

// ─────────────────────────────────────────────────────────────
//  Test 1 — DELETE /api/reset clears all user data
// ─────────────────────────────────────────────────────────────

describe('DELETE /api/reset — clears all data', function () {
    it('deletes user profile, habits, and completions', function () {
        $user = flowUser();
        $habit = flowHabit();
        HabitCompletion::create([
            'habit_id' => $habit->id,
            'completed_date' => today()->toDateString(),
        ]);

        $this->deleteJson('/api/reset')->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseCount('user_profile', 0);
        $this->assertDatabaseCount('habits', 0);
        $this->assertDatabaseCount('habit_completions', 0);
    });

    it('subsequent GET /api/state returns user null after reset', function () {
        flowUser();
        flowHabit();

        $this->deleteJson('/api/reset')->assertOk();

        $this->getJson('/api/state')
            ->assertOk()
            ->assertJson(['user' => null]);
    });

    it('returns ok when there is nothing to delete', function () {
        $this->deleteJson('/api/reset')
            ->assertOk()
            ->assertJson(['ok' => true]);
    });
});

// ─────────────────────────────────────────────────────────────
//  Test 2 — POST /api/setup creates / updates user profile
// ─────────────────────────────────────────────────────────────

describe('POST /api/setup — user profile creation', function () {
    it('creates a user profile with all fields', function () {
        $this->postJson('/api/setup', [
            'name' => 'Alex',
            'identity' => 'athlete',
            'identityLabel' => 'The Athlete',
            'identityIcon' => '🏃',
        ])->assertOk()->assertJson(['ok' => true, 'name' => 'Alex']);

        $this->assertDatabaseHas('user_profile', [
            'name' => 'Alex',
            'identity' => 'athlete',
            'identity_label' => 'The Athlete',
            'identity_icon' => '🏃',
        ]);
    });

    it('subsequent GET /api/state returns the created user', function () {
        $this->postJson('/api/setup', [
            'name' => 'Alex',
            'identity' => 'athlete',
            'identityLabel' => 'The Athlete',
            'identityIcon' => '🏃',
        ])->assertOk();

        $this->getJson('/api/state')
            ->assertOk()
            ->assertJsonPath('user.name', 'Alex')
            ->assertJsonPath('user.identity', 'athlete')
            ->assertJsonPath('user.identityLabel', 'The Athlete')
            ->assertJsonPath('user.identityIcon', '🏃');
    });

    it('updates an existing profile on second call (upsert at id=1)', function () {
        $this->postJson('/api/setup', [
            'name' => 'Alex',
            'identity' => 'athlete',
            'identityLabel' => 'The Athlete',
            'identityIcon' => '🏃',
        ])->assertOk();

        $this->postJson('/api/setup', [
            'name' => 'Alex Updated',
            'identity' => 'scholar',
            'identityLabel' => 'The Scholar',
            'identityIcon' => '📚',
        ])->assertOk()->assertJson(['ok' => true, 'name' => 'Alex Updated']);

        $this->assertDatabaseCount('user_profile', 1);
        $this->assertDatabaseHas('user_profile', [
            'name' => 'Alex Updated',
            'identity' => 'scholar',
        ]);
    });

    it('returns 422 when name is missing', function () {
        $this->postJson('/api/setup', [
            'identity' => 'athlete',
            'identityLabel' => 'The Athlete',
            'identityIcon' => '🏃',
        ])->assertUnprocessable();
    });

    it('returns 422 when identity is missing', function () {
        $this->postJson('/api/setup', [
            'name' => 'Alex',
            'identityLabel' => 'The Athlete',
            'identityIcon' => '🏃',
        ])->assertUnprocessable();
    });

    it('returns 422 when identityLabel is missing', function () {
        $this->postJson('/api/setup', [
            'name' => 'Alex',
            'identity' => 'athlete',
            'identityIcon' => '🏃',
        ])->assertUnprocessable();
    });

    it('returns 422 when identityIcon is missing', function () {
        $this->postJson('/api/setup', [
            'name' => 'Alex',
            'identity' => 'athlete',
            'identityLabel' => 'The Athlete',
        ])->assertUnprocessable();
    });
});

// ─────────────────────────────────────────────────────────────
//  Test 3 — Full reset-then-re-onboard flow
// ─────────────────────────────────────────────────────────────

describe('full reset-then-re-onboard flow', function () {
    it('re-setup works cleanly after reset and state reflects new user', function () {
        // Step 1: create initial user, habit, and completion
        $this->postJson('/api/setup', [
            'name' => 'First User',
            'identity' => 'athlete',
            'identityLabel' => 'The Athlete',
            'identityIcon' => '🏃',
        ])->assertOk();

        $habit = flowHabit('Initial Habit');

        $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id])
            ->assertOk();

        $this->assertDatabaseCount('user_profile', 1);
        $this->assertDatabaseCount('habits', 1);
        $this->assertDatabaseCount('habit_completions', 1);

        // Step 2: reset everything
        $this->deleteJson('/api/reset')->assertOk();

        $this->assertDatabaseCount('user_profile', 0);
        $this->assertDatabaseCount('habits', 0);
        $this->assertDatabaseCount('habit_completions', 0);

        // Step 3: re-onboard as a new user
        $this->postJson('/api/setup', [
            'name' => 'Second User',
            'identity' => 'scholar',
            'identityLabel' => 'The Scholar',
            'identityIcon' => '📚',
        ])->assertOk()->assertJson(['ok' => true, 'name' => 'Second User']);

        // Step 4: state must reflect the new user, not the old one
        $this->getJson('/api/state')
            ->assertOk()
            ->assertJsonPath('user.name', 'Second User')
            ->assertJsonPath('user.identity', 'scholar')
            ->assertJsonPath('habits', [])
            ->assertJsonPath('completions', []);
    });

    it('completions are isolated to the re-created user after reset', function () {
        // First session: create user + habit + completion
        $this->postJson('/api/setup', [
            'name' => 'Old User',
            'identity' => 'athlete',
            'identityLabel' => 'The Athlete',
            'identityIcon' => '🏃',
        ])->assertOk();

        $oldHabit = flowHabit('Old Habit');
        $this->postJson('/api/completions/toggle', ['habit_id' => $oldHabit->id])->assertOk();

        // Reset
        $this->deleteJson('/api/reset')->assertOk();

        // Second session: new user and habit
        $this->postJson('/api/setup', [
            'name' => 'New User',
            'identity' => 'artist',
            'identityLabel' => 'The Artist',
            'identityIcon' => '🎨',
        ])->assertOk();

        flowHabit('New Habit');

        $state = $this->getJson('/api/state')->assertOk();

        // No completions inherited from the old session
        expect($state->json('completions'))->toBe([]);
        expect($state->json('habits'))->toHaveCount(1);
        expect($state->json('habits.0.name'))->toBe('New Habit');
    });
});

// ─────────────────────────────────────────────────────────────
//  Test 4 — GET /api/state returns all expected top-level keys
// ─────────────────────────────────────────────────────────────

describe('GET /api/state — response shape', function () {
    it('returns all expected keys when a user and habits exist', function () {
        flowUser();
        $habit = flowHabit();
        HabitCompletion::create([
            'habit_id' => $habit->id,
            'completed_date' => today()->toDateString(),
        ]);

        $this->getJson('/api/state')
            ->assertOk()
            ->assertJsonStructure([
                'user' => [
                    'name',
                    'identity',
                    'identityLabel',
                    'identityIcon',
                    'createdAt',
                ],
                'habits',
                'completions',
                'completionNotes',
                'streaks',
                'bestStreaks',
                'streakData',
                'bestStreakData',
                'categories',
                'achievements',
            ]);
    });

    it('returns user null when no user profile exists', function () {
        $this->getJson('/api/state')
            ->assertOk()
            ->assertExactJson(['user' => null]);
    });

    it('returns empty arrays for collections when user has no habits', function () {
        flowUser();

        $state = $this->getJson('/api/state')->assertOk();

        expect($state->json('habits'))->toBe([]);
        expect($state->json('completions'))->toBe([]);
        expect($state->json('completionNotes'))->toBe([]);
        expect($state->json('streaks'))->toBe([]);
        expect($state->json('bestStreaks'))->toBe([]);
        expect($state->json('achievements'))->toBe([]);
    });

    it('streaks and bestStreaks are keyed by habit id', function () {
        flowUser();
        $habit = flowHabit();
        HabitCompletion::create([
            'habit_id' => $habit->id,
            'completed_date' => today()->toDateString(),
        ]);

        $state = $this->getJson('/api/state')->assertOk();

        expect($state->json('streaks'))->toHaveKey((string) $habit->id);
        expect($state->json('bestStreaks'))->toHaveKey((string) $habit->id);
        expect($state->json('streakData'))->toHaveKey((string) $habit->id);
        expect($state->json('bestStreakData'))->toHaveKey((string) $habit->id);
    });

    it('completions map is keyed by date and contains the habit id', function () {
        flowUser();
        $habit = flowHabit();
        $date = today()->toDateString();
        HabitCompletion::create(['habit_id' => $habit->id, 'completed_date' => $date]);

        $state = $this->getJson('/api/state')->assertOk();

        expect($state->json("completions.{$date}"))->toContain($habit->id);
    });
});

<?php

use App\Models\Achievement;
use App\Models\Habit;
use App\Models\HabitCompletion;
use App\Models\UserAchievement;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeUser(): UserProfile
{
    return UserProfile::create([
        'name' => 'Test User',
        'identity' => 'athlete',
        'identity_label' => 'The Athlete',
        'identity_icon' => '🏃',
    ]);
}

function makeHabitForAchievement(string $name = 'Test Habit'): Habit
{
    return Habit::create([
        'name' => $name,
        'emoji' => '✅',
        'color' => '#1e3a2f',
        'time_of_day' => 'morning',
        'difficulty' => 'medium',
    ]);
}

// ══════════════════════════════════════════════════════
//  UserProfile helpers
// ══════════════════════════════════════════════════════
describe('UserProfile achievement helpers', function () {
    it('unlocks an achievement idempotently', function () {
        $user = makeUser();

        $result1 = $user->unlockAchievement('perfect_day');
        $result2 = $user->unlockAchievement('perfect_day');

        expect($result1)->toBeInstanceOf(Achievement::class)
            ->and($result1->code)->toBe('perfect_day')
            ->and($result2)->toBeNull();
    });

    it('returns null when unlocking a non-existent code', function () {
        $user = makeUser();

        $result = $user->unlockAchievement('non_existent_code');

        expect($result)->toBeNull();
    });

    it('reports hasAchievement correctly', function () {
        $user = makeUser();

        expect($user->hasAchievement('perfect_day'))->toBeFalse();

        $user->unlockAchievement('perfect_day');

        expect($user->hasAchievement('perfect_day'))->toBeTrue();
    });
});

// ══════════════════════════════════════════════════════
//  /api/state includes achievements
// ══════════════════════════════════════════════════════
describe('GET /api/state', function () {
    it('includes an empty achievements array when none are unlocked', function () {
        makeUser();
        makeHabitForAchievement();

        $response = $this->getJson('/api/state');

        $response->assertSuccessful()
            ->assertJsonPath('achievements', []);
    });

    it('includes earned achievements in state', function () {
        $user = makeUser();
        $user->unlockAchievement('perfect_day');

        $response = $this->getJson('/api/state');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'achievements')
            ->assertJsonPath('achievements.0.code', 'perfect_day');
    });
});

// ══════════════════════════════════════════════════════
//  POST /api/completions/toggle — achievement unlocking
// ══════════════════════════════════════════════════════
describe('POST /api/completions/toggle achievement unlocks', function () {
    it('returns null achievement when no condition is met', function () {
        makeUser();
        $habit1 = makeHabitForAchievement('Habit 1');
        makeHabitForAchievement('Habit 2'); // second habit left uncompleted — prevents perfect_day

        // Only completing habit1 with habit2 uncompleted: no achievement condition is met
        $response = $this->postJson('/api/completions/toggle', ['habit_id' => $habit1->id]);

        $response->assertSuccessful()
            ->assertJsonPath('achievement', null);
    });

    it('unlocks perfect_day when all habits are completed today', function () {
        makeUser();
        $habit = makeHabitForAchievement();

        $response = $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);

        $response->assertSuccessful()
            ->assertJsonPath('achievement.code', 'perfect_day');
    });

    it('does not unlock perfect_day when uncompleting', function () {
        makeUser();
        $habit = makeHabitForAchievement();

        // Complete first
        $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);

        // Uncomplete — should not return achievement
        $response = $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);

        $response->assertSuccessful()
            ->assertJsonPath('achievement', null);
    });

    it('does not unlock perfect_day when some habits are not completed', function () {
        makeUser();
        $habit1 = makeHabitForAchievement('Habit 1');
        makeHabitForAchievement('Habit 2'); // not completed

        $response = $this->postJson('/api/completions/toggle', ['habit_id' => $habit1->id]);

        $response->assertSuccessful()
            ->assertJsonPath('achievement', null);
    });

    it('unlocks habit_builder when 3 habits exist', function () {
        makeUser();
        $habit1 = makeHabitForAchievement('Habit 1');
        $habit2 = makeHabitForAchievement('Habit 2');
        $habit3 = makeHabitForAchievement('Habit 3');

        // Complete habits 1 and 2 first (don't trigger habit_builder yet)
        HabitCompletion::create(['habit_id' => $habit1->id, 'completed_date' => today()->subDay()->format('Y-m-d')]);
        HabitCompletion::create(['habit_id' => $habit2->id, 'completed_date' => today()->subDay()->format('Y-m-d')]);

        // Completing habit 3 should trigger habit_builder check
        $response = $this->postJson('/api/completions/toggle', ['habit_id' => $habit3->id]);

        $response->assertSuccessful()
            ->assertJsonPath('achievement.code', 'habit_builder');
    });

    it('unlocks comeback when rebuilding a broken streak', function () {
        makeUser();
        $habit = makeHabitForAchievement();
        makeHabitForAchievement('Other Habit'); // left uncompleted — prevents perfect_day from firing first

        // Record a historical completion (before yesterday) to establish history
        HabitCompletion::create([
            'habit_id' => $habit->id,
            'completed_date' => today()->subDays(3)->format('Y-m-d'),
        ]);
        // No completion yesterday (streak is broken)

        // Today's completion should trigger comeback check
        $response = $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);

        $response->assertSuccessful()
            ->assertJsonPath('achievement.code', 'comeback');
    });

    it('does not unlock comeback on first ever completion', function () {
        makeUser();
        $habit = makeHabitForAchievement();

        // No historical completions at all — pure first-time
        $response = $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);

        // perfect_day may unlock but not comeback
        $achievementCode = $response->json('achievement.code');
        expect($achievementCode)->not->toBe('comeback');
    });

    it('achievement is idempotent — second trigger does not re-unlock', function () {
        makeUser();
        $habit = makeHabitForAchievement();

        // First completion unlocks perfect_day
        $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);

        expect(UserAchievement::count())->toBe(1);

        // Uncomplete then re-complete — should not create a second row
        $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);
        $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);

        expect(UserAchievement::count())->toBe(1);
    });

    it('response always includes achievement key', function () {
        makeUser();
        $habit = makeHabitForAchievement();

        $response = $this->postJson('/api/completions/toggle', ['habit_id' => $habit->id]);

        $response->assertSuccessful()
            ->assertJsonStructure(['completed', 'streak', 'milestone', 'achievement']);
    });
});

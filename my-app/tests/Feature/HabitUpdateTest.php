<?php

use App\Models\Habit;
use App\Models\HabitCompletion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('PUT /api/habits/{habit}', function () {
    it('updates a habit and returns the updated api array', function () {
        $habit = Habit::create([
            'name' => 'Original Name',
            'emoji' => '🏃',
            'color' => '#1e3a2f',
            'time_of_day' => 'morning',
            'difficulty' => 'medium',
        ]);

        $response = $this->putJson("/api/habits/{$habit->id}", [
            'name' => 'Updated Name',
            'emoji' => '📚',
            'color' => '#1e1a3a',
            'time' => 'evening',
            'why' => 'To grow',
            'diff' => 'hard',
        ]);

        $response->assertOk()
            ->assertJson([
                'id' => $habit->id,
                'name' => 'Updated Name',
                'emoji' => '📚',
                'color' => '#1e1a3a',
                'time' => 'evening',
                'why' => 'To grow',
                'diff' => 'hard',
            ]);
    });

    it('does not change the habit id', function () {
        $habit = Habit::create([
            'name' => 'My Habit',
            'emoji' => '💪',
            'color' => '#1e3a2f',
            'time_of_day' => 'morning',
            'difficulty' => 'medium',
        ]);

        $originalId = $habit->id;

        $this->putJson("/api/habits/{$originalId}", [
            'name' => 'Renamed',
            'emoji' => '💧',
            'color' => '#1a3a3a',
        ])->assertOk()->assertJson(['id' => $originalId]);

        expect(Habit::find($originalId))->not->toBeNull();
    });

    it('does not delete completions when a habit is edited', function () {
        $habit = Habit::create([
            'name' => 'Run',
            'emoji' => '🏃',
            'color' => '#1e3a2f',
            'time_of_day' => 'morning',
            'difficulty' => 'medium',
        ]);

        HabitCompletion::create([
            'habit_id' => $habit->id,
            'completed_date' => today()->toDateString(),
        ]);

        $this->putJson("/api/habits/{$habit->id}", [
            'name' => 'Updated Run',
            'emoji' => '🏃',
            'color' => '#1e3a2f',
        ])->assertOk();

        expect(HabitCompletion::where('habit_id', $habit->id)->count())->toBe(1);
    });

    it('returns 422 when name is missing', function () {
        $habit = Habit::create([
            'name' => 'Run',
            'emoji' => '🏃',
            'color' => '#1e3a2f',
            'time_of_day' => 'morning',
            'difficulty' => 'medium',
        ]);

        $this->putJson("/api/habits/{$habit->id}", [
            'emoji' => '🏃',
            'color' => '#1e3a2f',
        ])->assertUnprocessable();
    });

    it('returns 422 when emoji is missing', function () {
        $habit = Habit::create([
            'name' => 'Run',
            'emoji' => '🏃',
            'color' => '#1e3a2f',
            'time_of_day' => 'morning',
            'difficulty' => 'medium',
        ]);

        $this->putJson("/api/habits/{$habit->id}", [
            'name' => 'Run',
            'color' => '#1e3a2f',
        ])->assertUnprocessable();
    });

    it('returns 404 for a non-existent habit', function () {
        $this->putJson('/api/habits/99999', [
            'name' => 'Ghost',
            'emoji' => '👻',
            'color' => '#1e3a2f',
        ])->assertNotFound();
    });

    it('updates all 4-laws fields correctly', function () {
        $habit = Habit::create([
            'name' => 'Meditate',
            'emoji' => '🧘',
            'color' => '#1e3a2f',
            'time_of_day' => 'morning',
            'difficulty' => 'medium',
        ]);

        $this->putJson("/api/habits/{$habit->id}", [
            'name' => 'Meditate Daily',
            'emoji' => '🧘',
            'color' => '#1e3a2f',
            'why' => 'Find calm',
            'bundle' => 'With morning coffee',
            'twoMin' => 'Just sit and breathe',
            'stack' => 'After I wake up',
            'duration' => '10 minutes',
            'reward' => 'Nice breakfast',
            'diff' => 'easy',
            'time' => 'morning',
        ])->assertOk()
            ->assertJson([
                'why' => 'Find calm',
                'bundle' => 'With morning coffee',
                'twoMin' => 'Just sit and breathe',
                'stack' => 'After I wake up',
                'duration' => '10 minutes',
                'reward' => 'Nice breakfast',
                'diff' => 'easy',
            ]);
    });
});

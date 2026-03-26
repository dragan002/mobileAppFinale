<?php

use App\Models\Habit;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Reminder fields in API responses', function () {
    it('includes time_of_day as time in the habit creation response', function () {
        $response = $this->postJson('/api/habits', [
            'name' => 'Morning Run',
            'emoji' => '🏃',
            'color' => '#1e3a2f',
            'time' => 'morning',
        ]);

        $response->assertCreated()
            ->assertJsonPath('time', 'morning');
    });

    it('includes time for each time_of_day value', function (string $time) {
        $response = $this->postJson('/api/habits', [
            'name' => 'Test Habit',
            'emoji' => '✅',
            'color' => '#1e3a2f',
            'time' => $time,
        ]);

        $response->assertCreated()
            ->assertJsonPath('time', $time);
    })->with(['morning', 'afternoon', 'evening', 'anytime']);

    it('includes time in the /api/state habits array', function () {
        UserProfile::create([
            'name' => 'Tester',
            'identity' => 'athlete',
            'identity_label' => 'The Athlete',
            'identity_icon' => '🏃',
        ]);

        Habit::create([
            'name' => 'Evening Read',
            'emoji' => '📚',
            'color' => '#1e1a3a',
            'time_of_day' => 'evening',
            'difficulty' => 'medium',
        ]);

        $response = $this->getJson('/api/state');

        $response->assertOk()
            ->assertJsonPath('habits.0.time', 'evening');
    });

    it('preserves time_of_day when updating a habit', function () {
        $habit = Habit::create([
            'name' => 'Old Name',
            'emoji' => '🏃',
            'color' => '#1e3a2f',
            'time_of_day' => 'morning',
            'difficulty' => 'medium',
        ]);

        $response = $this->putJson("/api/habits/{$habit->id}", [
            'name' => 'Updated Name',
            'emoji' => '🏃',
            'color' => '#1e3a2f',
            'time' => 'evening',
        ]);

        $response->assertOk()
            ->assertJsonPath('time', 'evening');
    });

    it('defaults time to morning when not provided during creation', function () {
        $response = $this->postJson('/api/habits', [
            'name' => 'No Time Habit',
            'emoji' => '✅',
            'color' => '#1e3a2f',
        ]);

        $response->assertCreated()
            ->assertJsonPath('time', 'morning');
    });
});

<?php

use App\Models\Habit;
use App\Models\HabitCompletion;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    UserProfile::create([
        'id' => 1,
        'name' => 'Test User',
        'identity' => 'athlete',
        'identity_label' => 'The Athlete',
        'identity_icon' => '🏃',
    ]);
});

it('returns weekly rates for last 5 weeks', function () {
    $habit = Habit::create([
        'name' => 'Morning Run',
        'emoji' => '🏃',
        'color' => '#1e3a2f',
        'time_of_day' => 'morning',
        'difficulty' => 'medium',
        'created_at' => Carbon::now()->subWeeks(5),
    ]);

    // Add completions for current week
    for ($i = 0; $i < 3; $i++) {
        HabitCompletion::create([
            'habit_id' => $habit->id,
            'completed_date' => Carbon::now()->subDays($i),
        ]);
    }

    $response = $this->getJson('/api/analytics');

    $response->assertSuccessful();
    $weeklyRates = $response->json('weeklyRates');
    expect($weeklyRates)->toHaveCount(5);
    expect($weeklyRates[0])->toBeGreaterThanOrEqual(0);
    expect($weeklyRates[0])->toBeLessThanOrEqual(100);
});

it('returns monthly rates for last 12 months', function () {
    $habit = Habit::create([
        'name' => 'Morning Run',
        'emoji' => '🏃',
        'color' => '#1e3a2f',
        'time_of_day' => 'morning',
        'difficulty' => 'medium',
        'created_at' => Carbon::now()->subMonths(12),
    ]);

    HabitCompletion::create([
        'habit_id' => $habit->id,
        'completed_date' => Carbon::now()->format('Y-m-d'),
    ]);

    $response = $this->getJson('/api/analytics');

    $response->assertSuccessful();
    $monthlyRates = $response->json('monthlyRates');
    expect($monthlyRates)->toHaveCount(12);
    expect($monthlyRates[0])->toBeGreaterThanOrEqual(0);
    expect($monthlyRates[0])->toBeLessThanOrEqual(100);
});

it('returns all time rate', function () {
    $habit = Habit::create([
        'name' => 'Morning Run',
        'emoji' => '🏃',
        'color' => '#1e3a2f',
        'time_of_day' => 'morning',
        'difficulty' => 'medium',
    ]);

    HabitCompletion::create([
        'habit_id' => $habit->id,
        'completed_date' => Carbon::now()->format('Y-m-d'),
    ]);

    $response = $this->getJson('/api/analytics');

    $response->assertSuccessful();
    expect($response->json('allTimeRate'))->toBeGreaterThanOrEqual(0);
    expect($response->json('allTimeRate'))->toBeLessThanOrEqual(100);
});

it('returns zeros when there are no completions', function () {
    Habit::create([
        'name' => 'Morning Run',
        'emoji' => '🏃',
        'color' => '#1e3a2f',
        'time_of_day' => 'morning',
        'difficulty' => 'medium',
    ]);

    $response = $this->getJson('/api/analytics');

    $response->assertSuccessful();
    expect($response->json('allTimeRate'))->toBe(0);
});

it('returns empty arrays when no habits exist', function () {
    $response = $this->getJson('/api/analytics');

    $response->assertSuccessful();
    expect($response->json('weeklyRates'))->toHaveCount(5);
    expect($response->json('monthlyRates'))->toHaveCount(12);
    expect($response->json('allTimeRate'))->toBe(0);
});

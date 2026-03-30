<?php

use App\Models\UserProfile;
use App\Models\WeeklyReflection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeReflectionUser(): UserProfile
{
    return UserProfile::create([
        'name' => 'Test User',
        'identity' => 'athlete',
        'identity_label' => 'The Athlete',
        'identity_icon' => '🏃',
    ]);
}

describe('POST /api/reflections', function () {
    it('stores a new reflection for the current week', function () {
        makeReflectionUser();

        $response = $this->postJson('/api/reflections', [
            'week_of' => '2026-03-23',
            'note' => 'Good week overall.',
        ]);

        $response->assertOk()->assertJson(['ok' => true, 'week_of' => '2026-03-23']);

        $reflection = WeeklyReflection::first();
        expect($reflection)->not->toBeNull();
        expect($reflection->week_of)->toBe('2026-03-23');
        expect($reflection->note)->toBe('Good week overall.');
    });

    it('upserts when a reflection for that week already exists', function () {
        $user = makeReflectionUser();

        WeeklyReflection::create([
            'user_profile_id' => $user->id,
            'week_of' => '2026-03-23',
            'note' => 'First note.',
        ]);

        $response = $this->postJson('/api/reflections', [
            'week_of' => '2026-03-23',
            'note' => 'Updated note.',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseCount('weekly_reflections', 1);
        $updated = WeeklyReflection::first();
        expect($updated->week_of)->toBe('2026-03-23');
        expect($updated->note)->toBe('Updated note.');
    });

    it('stores a reflection with an empty note', function () {
        makeReflectionUser();

        $response = $this->postJson('/api/reflections', [
            'week_of' => '2026-03-23',
            'note' => '',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);

        expect(WeeklyReflection::count())->toBe(1);
    });

    it('stores a reflection when note is omitted', function () {
        makeReflectionUser();

        $response = $this->postJson('/api/reflections', [
            'week_of' => '2026-03-23',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);
    });

    it('returns 422 when week_of is missing', function () {
        makeReflectionUser();

        $response = $this->postJson('/api/reflections', [
            'note' => 'Missing date.',
        ]);

        $response->assertUnprocessable();
    });

    it('returns 422 when week_of is not a valid date', function () {
        makeReflectionUser();

        $response = $this->postJson('/api/reflections', [
            'week_of' => 'not-a-date',
            'note' => 'Bad date.',
        ]);

        $response->assertUnprocessable();
    });

    it('returns 422 when there is no user profile', function () {
        $response = $this->postJson('/api/reflections', [
            'week_of' => '2026-03-23',
            'note' => 'No user.',
        ]);

        $response->assertUnprocessable();
    });

    it('returns 422 when note exceeds 2000 characters', function () {
        makeReflectionUser();

        $response = $this->postJson('/api/reflections', [
            'week_of' => '2026-03-23',
            'note' => str_repeat('x', 2001),
        ]);

        $response->assertUnprocessable();
    });

    it('stores separate reflections for different weeks', function () {
        makeReflectionUser();

        $this->postJson('/api/reflections', ['week_of' => '2026-03-16', 'note' => 'Week 1']);
        $this->postJson('/api/reflections', ['week_of' => '2026-03-23', 'note' => 'Week 2']);

        $this->assertDatabaseCount('weekly_reflections', 2);
    });
});

<?php

use App\Models\Category;
use App\Models\Habit;
use App\Models\UserProfile;
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

    // Seed preset categories
    $presets = [
        ['name' => 'Morning Routine', 'color' => '#f97316', 'sort_order' => 1],
        ['name' => 'Evening Routine', 'color' => '#7c3aed', 'sort_order' => 2],
        ['name' => 'Health & Fitness', 'color' => '#22c55e', 'sort_order' => 3],
        ['name' => 'Learning & Growth', 'color' => '#3b82f6', 'sort_order' => 4],
        ['name' => 'Work & Productivity', 'color' => '#eab308', 'sort_order' => 5],
    ];

    foreach ($presets as $preset) {
        Category::create([
            ...$preset,
            'user_profile_id' => null,
            'is_preset' => true,
        ]);
    }
});

it('returns preset categories from state endpoint', function () {
    $response = $this->getJson('/api/state');

    $response->assertSuccessful();
    $categories = $response->json('categories');
    expect($categories)->toHaveCount(5);
    expect(array_column($categories, 'name'))->toContain('Morning Routine', 'Evening Routine');
});

it('includes user created categories in state response', function () {
    Category::create([
        'user_profile_id' => 1,
        'name' => 'Custom Category',
        'color' => '#ff0000',
        'is_preset' => false,
        'sort_order' => 10,
    ]);

    $response = $this->getJson('/api/state');

    $response->assertSuccessful();
    $categories = $response->json('categories');
    expect($categories)->toHaveCount(6); // 5 presets + 1 custom
    expect(collect($categories)->pluck('name'))->toContain('Custom Category');
});

it('creates a custom category', function () {
    $response = $this->postJson('/api/categories', [
        'name' => 'My Custom Category',
        'color' => '#ff0000',
    ]);

    $response->assertStatus(201);
    expect($response->json('name'))->toBe('My Custom Category');
    expect($response->json('color'))->toBe('#ff0000');
    expect($response->json('isPreset'))->toBe(false);

    $this->assertDatabaseHas('categories', [
        'name' => 'My Custom Category',
        'user_profile_id' => 1,
    ]);
});

it('returns 422 when category name is missing', function () {
    $response = $this->postJson('/api/categories', [
        'color' => '#ff0000',
    ]);

    $response->assertUnprocessable();
});

it('prevents deleting preset categories', function () {
    $preset = Category::where('is_preset', true)->first();

    $response = $this->deleteJson("/api/categories/{$preset->id}");

    $response->assertForbidden();
    expect(Category::find($preset->id))->not->toBeNull();
});

it('deletes user category', function () {
    $category = Category::create([
        'user_profile_id' => 1,
        'name' => 'To Delete',
        'color' => '#ff0000',
        'is_preset' => false,
        'sort_order' => 10,
    ]);

    $response = $this->deleteJson("/api/categories/{$category->id}");

    $response->assertSuccessful();
    expect(Category::find($category->id))->toBeNull();
});

it('assigns category to habit on create', function () {
    $category = Category::where('is_preset', true)->first();

    $response = $this->postJson('/api/habits', [
        'name' => 'Morning Run',
        'emoji' => '🏃',
        'color' => '#1e3a2f',
        'categoryId' => $category->id,
    ]);

    $response->assertSuccessful();
    expect($response->json('categoryId'))->toBe($category->id);

    $this->assertDatabaseHas('habits', [
        'name' => 'Morning Run',
        'category_id' => $category->id,
    ]);
});

it('assigns category to habit on update', function () {
    $habit = Habit::create([
        'name' => 'Morning Run',
        'emoji' => '🏃',
        'color' => '#1e3a2f',
        'time_of_day' => 'morning',
        'difficulty' => 'medium',
    ]);

    $category = Category::where('is_preset', true)->first();

    $response = $this->putJson("/api/habits/{$habit->id}", [
        'name' => 'Morning Run',
        'emoji' => '🏃',
        'color' => '#1e3a2f',
        'categoryId' => $category->id,
    ]);

    $response->assertSuccessful();
    expect($response->json('categoryId'))->toBe($category->id);

    $this->assertDatabaseHas('habits', [
        'id' => $habit->id,
        'category_id' => $category->id,
    ]);
});

it('nullifies habit category_id when category is deleted', function () {
    $category = Category::create([
        'user_profile_id' => 1,
        'name' => 'Custom',
        'color' => '#ff0000',
        'is_preset' => false,
        'sort_order' => 10,
    ]);

    $habit = Habit::create([
        'name' => 'Habit',
        'emoji' => '🏃',
        'color' => '#1e3a2f',
        'category_id' => $category->id,
        'time_of_day' => 'morning',
        'difficulty' => 'medium',
    ]);

    $this->deleteJson("/api/categories/{$category->id}");

    $habit->refresh();
    expect($habit->category_id)->toBeNull();
});

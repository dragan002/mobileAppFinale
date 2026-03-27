<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if presets don't already exist (idempotent)
        if (Category::where('is_preset', true)->exists()) {
            return;
        }

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
    }
}

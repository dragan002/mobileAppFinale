<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_profile_id' => null,
            'name' => $this->faker->word(),
            'color' => $this->faker->hexColor(),
            'is_preset' => false,
            'sort_order' => 0,
        ];
    }

    public function preset(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_preset' => true,
                'user_profile_id' => null,
            ];
        });
    }

    public function custom(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_preset' => false,
                'user_profile_id' => 1,
            ];
        });
    }
}

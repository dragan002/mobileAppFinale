<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('achievements')->insertOrIgnore([
            [
                'code' => 'streak_30',
                'name' => '30-Day Streak',
                'description' => 'Reach a 30-day streak on any habit',
                'is_prestige' => false,
                'icon' => '🏆',
                'criteria_text' => 'Achieve a 30-day streak on any habit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'streak_60',
                'name' => '60-Day Streak',
                'description' => 'Reach a 60-day streak on any habit',
                'is_prestige' => false,
                'icon' => '⚡',
                'criteria_text' => 'Achieve a 60-day streak on any habit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('achievements')->whereIn('code', ['streak_30', 'streak_60'])->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description');
            $table->boolean('is_prestige')->default(false);
            $table->string('icon');
            $table->string('criteria_text');
            $table->timestamps();
        });

        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_profile_id');
            $table->unsignedBigInteger('achievement_id');
            $table->timestamp('unlocked_at');
            $table->timestamps();

            $table->unique(['user_profile_id', 'achievement_id']);
            $table->foreign('user_profile_id')->references('id')->on('user_profile')->cascadeOnDelete();
            $table->foreign('achievement_id')->references('id')->on('achievements')->cascadeOnDelete();
        });

        // Seed the 7 achievement definitions
        DB::table('achievements')->insert([
            [
                'code' => 'perfect_day',
                'name' => 'Perfect Day',
                'description' => 'Complete all habits in one day',
                'is_prestige' => false,
                'icon' => '⭐',
                'criteria_text' => 'Complete all your habits today',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'perfect_week',
                'name' => 'Perfect Week',
                'description' => 'Complete all habits for all 7 days',
                'is_prestige' => false,
                'icon' => '📅',
                'criteria_text' => 'Complete all habits every day this week',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'habit_builder',
                'name' => 'Habit Builder',
                'description' => 'Create your 3rd and 5th habits',
                'is_prestige' => false,
                'icon' => '🔨',
                'criteria_text' => 'Create 3+ habits',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'comeback',
                'name' => 'Comeback',
                'description' => 'Rebuild a streak after it broke',
                'is_prestige' => false,
                'icon' => '🔥',
                'criteria_text' => 'Break a streak, then rebuild it',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'one_percent_club',
                'name' => 'The 1% Club',
                'description' => '365 consecutive days on one habit',
                'is_prestige' => true,
                'icon' => '💎',
                'criteria_text' => 'Achieve 365-day streak on any habit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'atomic_identity',
                'name' => 'Atomic Identity',
                'description' => 'All habits in Identity phase',
                'is_prestige' => true,
                'icon' => '⚛️',
                'criteria_text' => 'Reach Identity phase on all habits',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'perfect_quarter',
                'name' => 'Perfect Quarter',
                'description' => '90 days straight, zero missed days',
                'is_prestige' => true,
                'icon' => '👑',
                'criteria_text' => 'Complete 90 days with no grace days used',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
    }
};

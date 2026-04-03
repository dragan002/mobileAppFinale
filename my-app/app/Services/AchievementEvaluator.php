<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\Habit;
use App\Models\HabitCompletion;
use App\Models\UserProfile;
use Carbon\Carbon;

class AchievementEvaluator
{
    /**
     * Evaluate all achievement conditions for the given user after a completion toggle.
     * Returns the first newly unlocked Achievement, or null if nothing was unlocked.
     */
    public function evaluate(UserProfile $user, int $toggledHabitId, bool $wasCompleted): ?Achievement
    {
        // Only evaluate when completing (not uncompleting)
        if ($wasCompleted) {
            return null;
        }

        $checks = [
            'perfect_day' => fn () => $this->checkPerfectDay($user),
            'perfect_week' => fn () => $this->checkPerfectWeek($user),
            'habit_builder' => fn () => $this->checkHabitBuilder($user),
            'comeback' => fn () => $this->checkComeback($user, $toggledHabitId),
            'streak_30' => fn () => $this->checkStreakMilestone($toggledHabitId, 30),
            'streak_60' => fn () => $this->checkStreakMilestone($toggledHabitId, 60),
            'one_percent_club' => fn () => $this->checkOnePercentClub($user),
            'atomic_identity' => fn () => $this->checkAtomicIdentity($user),
            'perfect_quarter' => fn () => $this->checkPerfectQuarter($user, $toggledHabitId),
        ];

        foreach ($checks as $code => $check) {
            if ($check()) {
                $unlocked = $user->unlockAchievement($code);
                if ($unlocked) {
                    return $unlocked;
                }
            }
        }

        return null;
    }

    /**
     * Perfect Day: all habits completed today.
     */
    private function checkPerfectDay(UserProfile $user): bool
    {
        $habits = Habit::all();

        if ($habits->isEmpty()) {
            return false;
        }

        $today = Carbon::today()->format('Y-m-d');
        $completedTodayHabitIds = HabitCompletion::where('completed_date', $today)
            ->pluck('habit_id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        foreach ($habits as $habit) {
            if (! in_array($habit->id, $completedTodayHabitIds, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Perfect Week: all habits completed every day of the current week (Sun–Sat).
     */
    private function checkPerfectWeek(UserProfile $user): bool
    {
        $habits = Habit::all();

        if ($habits->isEmpty()) {
            return false;
        }

        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);
        $weekEnd = $weekStart->copy()->addDays(6);

        $requiredDays = [];
        $cursor = $weekStart->copy();
        while ($cursor->lte($weekEnd)) {
            $requiredDays[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        // Only check days up to today
        $today = Carbon::today()->format('Y-m-d');
        $requiredDays = array_filter($requiredDays, fn ($d) => $d <= $today);

        if (empty($requiredDays)) {
            return false;
        }

        foreach ($requiredDays as $day) {
            $completedIds = HabitCompletion::where('completed_date', $day)
                ->pluck('habit_id')
                ->map(fn ($id) => (int) $id)
                ->toArray();

            foreach ($habits as $habit) {
                if (! in_array($habit->id, $completedIds, true)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Habit Builder: unlocked when the user has created 3 or more habits.
     * (Unlocks once at 3, idempotent if already held.)
     */
    private function checkHabitBuilder(UserProfile $user): bool
    {
        return Habit::count() >= 3;
    }

    /**
     * Comeback: the habit had a streak of 0 yesterday and > 0 today,
     * meaning it was previously broken and is now being rebuilt.
     */
    private function checkComeback(UserProfile $user, int $habitId): bool
    {
        $habit = Habit::find($habitId);

        if (! $habit) {
            return false;
        }

        $today = Carbon::today();
        $yesterday = $today->copy()->subDay()->format('Y-m-d');
        $twoDaysAgo = $today->copy()->subDays(2)->format('Y-m-d');

        // Must NOT have completed yesterday (streak was broken)
        $completedYesterday = HabitCompletion::where('habit_id', $habitId)
            ->where('completed_date', $yesterday)
            ->exists();

        if ($completedYesterday) {
            return false;
        }

        // Must have completed at some point before (has history beyond today)
        $hasHistoricalCompletion = HabitCompletion::where('habit_id', $habitId)
            ->where('completed_date', '<', $today->format('Y-m-d'))
            ->where('completed_date', '<', $yesterday)
            ->exists();

        return $hasHistoricalCompletion;
    }

    /**
     * Streak milestone: the toggled habit's current streak has reached the given threshold.
     */
    private function checkStreakMilestone(int $habitId, int $threshold): bool
    {
        $habit = Habit::find($habitId);

        if (! $habit) {
            return false;
        }

        return $habit->calculateStreak() >= $threshold;
    }

    /**
     * The 1% Club: any single habit has a current streak >= 365.
     */
    private function checkOnePercentClub(UserProfile $user): bool
    {
        $habits = Habit::all();

        foreach ($habits as $habit) {
            if ($habit->calculateStreak() >= 365) {
                return true;
            }
        }

        return false;
    }

    /**
     * Atomic Identity: all habits are in the 'identity' phase simultaneously.
     */
    private function checkAtomicIdentity(UserProfile $user): bool
    {
        $habits = Habit::all();

        if ($habits->isEmpty()) {
            return false;
        }

        foreach ($habits as $habit) {
            $phase = $habit->calculatePhase();
            if ($phase['phase'] !== 'identity') {
                return false;
            }
        }

        return true;
    }

    /**
     * Perfect Quarter: the toggled habit has 90 consecutive completions with no gaps
     * (i.e., the streak covers exactly 90 days with no grace days used).
     */
    private function checkPerfectQuarter(UserProfile $user, int $habitId): bool
    {
        $habit = Habit::find($habitId);

        if (! $habit) {
            return false;
        }

        // Fetch last 90 completions ordered desc
        $completions = HabitCompletion::where('habit_id', $habitId)
            ->orderBy('completed_date', 'desc')
            ->take(90)
            ->pluck('completed_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        if (count($completions) < 90) {
            return false;
        }

        // Walk back from today — every single day must be present (no gaps at all)
        $expected = Carbon::today();
        foreach ($completions as $date) {
            if ($date !== $expected->format('Y-m-d')) {
                return false;
            }
            $expected->subDay();
        }

        return true;
    }
}

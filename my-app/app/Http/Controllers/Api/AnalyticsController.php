<?php

namespace App\Http\Controllers\Api;

use App\Models\Habit;
use App\Models\HabitCompletion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class AnalyticsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'weeklyRates' => $this->calculateWeeklyRates(),
            'monthlyRates' => $this->calculateMonthlyRates(),
            'allTimeRate' => $this->calculateAllTimeRate(),
        ]);
    }

    private function calculateWeeklyRates(): array
    {
        $rates = [];

        for ($weekOffset = 0; $weekOffset <= 4; $weekOffset++) {
            $weekStart = Carbon::now()
                ->subWeeks($weekOffset)
                ->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            $habits = Habit::whereBetween('created_at', [$weekStart, $weekEnd])->orWhere('created_at', '<=', $weekStart)->get();

            if ($habits->isEmpty()) {
                $rates[] = 0;
                continue;
            }

            $possibleCompletions = 0;
            $actualCompletions = 0;

            foreach ($habits as $habit) {
                // Count days the habit existed in this week
                $habitStart = Carbon::parse($habit->created_at)->max($weekStart);
                $dayCount = $habitStart->diffInDays(Carbon::parse($habit->created_at) <= $weekEnd ? $weekEnd : $habit->created_at) + 1;
                $possibleCompletions += max(0, $dayCount);

                // Count actual completions for this habit in this week
                $completions = HabitCompletion::where('habit_id', $habit->id)
                    ->whereBetween('completed_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                    ->count();

                $actualCompletions += $completions;
            }

            $rate = $possibleCompletions > 0
                ? round(($actualCompletions / $possibleCompletions) * 100)
                : 0;

            $rates[] = $rate;
        }

        return array_reverse($rates); // index 0 = current week
    }

    private function calculateMonthlyRates(): array
    {
        $rates = [];

        for ($monthOffset = 0; $monthOffset <= 11; $monthOffset++) {
            $monthStart = Carbon::now()
                ->subMonths($monthOffset)
                ->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $habits = Habit::where('created_at', '<=', $monthEnd)->get();

            if ($habits->isEmpty()) {
                $rates[] = 0;
                continue;
            }

            $possibleCompletions = 0;
            $actualCompletions = 0;

            foreach ($habits as $habit) {
                // Count days the habit existed in this month
                $habitStart = Carbon::parse($habit->created_at)->max($monthStart);
                $dayCount = $habitStart->diffInDays(Carbon::parse($habit->created_at) <= $monthEnd ? $monthEnd : $habit->created_at) + 1;
                $possibleCompletions += max(0, $dayCount);

                // Count actual completions for this habit in this month
                $completions = HabitCompletion::where('habit_id', $habit->id)
                    ->whereBetween('completed_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
                    ->count();

                $actualCompletions += $completions;
            }

            $rate = $possibleCompletions > 0
                ? round(($actualCompletions / $possibleCompletions) * 100)
                : 0;

            $rates[] = $rate;
        }

        return array_reverse($rates); // index 0 = current month
    }

    private function calculateAllTimeRate(): float
    {
        $habits = Habit::all();

        if ($habits->isEmpty()) {
            return 0;
        }

        $possibleCompletions = 0;
        $actualCompletions = 0;

        foreach ($habits as $habit) {
            $dayCount = Carbon::parse($habit->created_at)->diffInDays(Carbon::now()) + 1;
            $possibleCompletions += $dayCount;

            $completions = $habit->completions()->count();
            $actualCompletions += $completions;
        }

        return $possibleCompletions > 0
            ? round(($actualCompletions / $possibleCompletions) * 100, 1)
            : 0;
    }
}

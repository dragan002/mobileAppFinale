<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\HabitCompletion;
use App\Models\UserProfile;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class StateController extends Controller
{
    public function index(): JsonResponse
    {
        $user = UserProfile::first();

        if (! $user) {
            return response()->json(['user' => null]);
        }

        $habits = Habit::orderBy('created_at')->get();

        // Last 90 days of completions (enough for stats + heatmap)
        $since = Carbon::today()->subDays(89)->format('Y-m-d');
        $completionRows = HabitCompletion::where('completed_date', '>=', $since)
            ->orderBy('completed_date')
            ->get(['habit_id', 'completed_date']);

        // Format: { 'YYYY-MM-DD': [habitId, ...] }
        $completionsMap = [];
        foreach ($completionRows as $c) {
            $date = Carbon::parse($c->completed_date)->format('Y-m-d');
            $completionsMap[$date][] = $c->habit_id;
        }

        // Current streaks and best streaks per habit
        $streaks = [];
        $bestStreaks = [];
        foreach ($habits as $habit) {
            $streaks[$habit->id] = $habit->calculateStreak();
            $bestStreaks[$habit->id] = $habit->calculateBestStreak();
        }

        return response()->json([
            'user' => [
                'name' => $user->name,
                'identity' => $user->identity,
                'identityLabel' => $user->identity_label,
                'identityIcon' => $user->identity_icon,
                'createdAt' => $user->created_at?->toDateString(),
            ],
            'habits' => $habits->map->toApiArray()->values(),
            'completions' => $completionsMap,
            'streaks' => $streaks,
            'bestStreaks' => $bestStreaks,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Habit;
use App\Models\HabitCompletion;
use App\Models\UserAchievement;
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
            ->get(['habit_id', 'completed_date', 'note']);

        // Format: { 'YYYY-MM-DD': [habitId, ...] }
        $completionsMap = [];
        $completionNotes = []; // Format: { 'YYYY-MM-DD:habitId': 'note text' }
        foreach ($completionRows as $c) {
            $date = Carbon::parse($c->completed_date)->format('Y-m-d');
            $completionsMap[$date][] = $c->habit_id;

            if ($c->note) {
                $completionNotes["{$date}:{$c->habit_id}"] = $c->note;
            }
        }

        // Current streaks and best streaks per habit
        $streaks = [];
        $bestStreaks = [];
        $streakData = [];
        $bestStreakData = [];
        foreach ($habits as $habit) {
            $sd = $habit->calculateStreakData();
            $bsd = $habit->calculateBestStreakData();
            $streaks[$habit->id] = $sd['value'];
            $bestStreaks[$habit->id] = $bsd['value'];
            $streakData[$habit->id] = $sd;
            $bestStreakData[$habit->id] = $bsd;
        }

        $categories = Category::query()
            ->where(function ($q) use ($user) {
                $q->where('is_preset', true);
                $q->orWhere('user_profile_id', $user->id);
            })
            ->orderBy('sort_order')
            ->get();

        // Load earned achievements for this user
        $achievements = UserAchievement::where('user_profile_id', $user->id)
            ->with('achievement')
            ->get()
            ->map(fn ($ua) => [
                'code' => $ua->achievement->code,
                'unlocked_at' => $ua->unlocked_at->toDateTimeString(),
            ])
            ->values();

        return response()->json([
            'user' => [
                'name' => $user->name,
                'identity' => $user->identity,
                'identityLabel' => $user->identity_label,
                'identityIcon' => $user->identity_icon,
                'createdAt' => $user->created_at?->toDateString(),
            ],
            'habits' => $habits->map(function ($habit) use ($streakData, $bestStreakData) {
                return $habit->toApiArray(
                    $streakData[$habit->id] ?? null,
                    $bestStreakData[$habit->id] ?? null
                );
            })->values(),
            'completions' => $completionsMap,
            'completionNotes' => $completionNotes,
            'streaks' => $streaks,
            'bestStreaks' => $bestStreaks,
            'streakData' => $streakData,
            'bestStreakData' => $bestStreakData,
            'categories' => $categories->map->toApiArray()->values(),
            'achievements' => $achievements,
        ]);
    }
}

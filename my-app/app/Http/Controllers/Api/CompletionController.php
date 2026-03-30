<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\HabitCompletion;
use App\Models\UserProfile;
use App\Services\AchievementEvaluator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompletionController extends Controller
{
    private const DAILY_MILESTONES = [7, 14, 21, 30, 60, 90, 100];

    private const WEEKLY_MILESTONES = [4, 9, 13, 26, 52];

    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'habit_id' => ['required', 'integer', 'exists:habits,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $habitId = (int) $request->habit_id;
        $today = Carbon::today()->format('Y-m-d');
        $note = $request->input('note');

        $existing = HabitCompletion::where('habit_id', $habitId)
            ->where('completed_date', $today)
            ->first();

        if ($existing) {
            $existing->delete();
            $completed = false;
        } else {
            HabitCompletion::create([
                'habit_id' => $habitId,
                'completed_date' => $today,
                'note' => $note,
            ]);
            $completed = true;
        }

        $habit = Habit::find($habitId);
        $streakData = $habit->calculateStreakData();
        $streakValue = $streakData['value'];
        $streakUnit = $streakData['unit'];

        $milestone = null;
        $milestoneLabel = null;

        if ($completed) {
            $milestones = $streakUnit === 'weeks' ? self::WEEKLY_MILESTONES : self::DAILY_MILESTONES;
            if (in_array($streakValue, $milestones, true)) {
                $milestone = $streakValue;
                $milestoneLabel = $streakUnit === 'weeks'
                    ? $streakValue.' Week Streak!'
                    : $streakValue.' Day Streak!';
            }
        }

        // Evaluate achievement unlocks on completion (not on uncomplete)
        $achievementPayload = null;
        $user = UserProfile::first();
        if ($user && $completed) {
            $evaluator = new AchievementEvaluator;
            $unlocked = $evaluator->evaluate($user, $habitId, wasCompleted: false);
            if ($unlocked) {
                $achievementPayload = [
                    'code' => $unlocked->code,
                    'name' => $unlocked->name,
                    'icon' => $unlocked->icon,
                    'is_prestige' => $unlocked->is_prestige,
                    'description' => $unlocked->description,
                ];
            }
        }

        return response()->json([
            'completed' => $completed,
            'streak' => $streakValue,
            'streakData' => $streakData,
            'milestone' => $milestone,
            'milestoneLabel' => $milestoneLabel,
            'achievement' => $achievementPayload,
        ]);
    }

    public function saveNote(Request $request): JsonResponse
    {
        $request->validate([
            'habit_id' => ['required', 'integer', 'exists:habits,id'],
            'note' => ['required', 'string', 'max:500'],
        ]);

        $habitId = (int) $request->habit_id;
        $today = Carbon::today()->format('Y-m-d');
        $note = $request->input('note');

        $completion = HabitCompletion::where('habit_id', $habitId)
            ->where('completed_date', $today)
            ->first();

        if (! $completion) {
            return response()->json(['error' => 'Completion not found for today'], 404);
        }

        $completion->update(['note' => $note]);

        return response()->json([
            'success' => true,
            'note' => $note,
        ]);
    }
}

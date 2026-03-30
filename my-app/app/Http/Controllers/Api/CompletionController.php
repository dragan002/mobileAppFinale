<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\HabitCompletion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompletionController extends Controller
{
    private const MILESTONES = [7, 14, 21, 30, 60, 90, 100];

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
        $streak = $habit->calculateStreak();

        $milestone = null;
        $milestoneLabel = null;

        if ($completed && in_array($streak, self::MILESTONES, true)) {
            $milestone = $streak;
            $milestoneLabel = $streak.' Day Streak!';
        }

        return response()->json([
            'completed' => $completed,
            'streak' => $streak,
            'milestone' => $milestone,
            'milestoneLabel' => $milestoneLabel,
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

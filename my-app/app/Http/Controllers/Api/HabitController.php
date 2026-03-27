<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'emoji' => ['required', 'string'],
            'color' => ['required', 'string'],
        ]);

        $habit = Habit::create([
            'name' => $request->name,
            'emoji' => $request->emoji,
            'color' => $request->color,
            'time_of_day' => $request->input('time', 'morning'),
            'why' => $request->input('why'),
            'bundle' => $request->input('bundle'),
            'two_min_version' => $request->input('twoMin'),
            'stack' => $request->input('stack'),
            'duration' => $request->input('duration'),
            'reward' => $request->input('reward'),
            'difficulty' => $request->input('diff', 'medium'),
            'category_id' => $request->input('categoryId'),
            'reminder_time' => $request->input('reminderTime'),
        ]);

        return response()->json($habit->toApiArray(), 201);
    }

    public function update(Request $request, Habit $habit): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'emoji' => ['required', 'string'],
            'color' => ['required', 'string'],
        ]);

        $habit->update([
            'name' => $request->name,
            'emoji' => $request->emoji,
            'color' => $request->color,
            'time_of_day' => $request->input('time', $habit->time_of_day),
            'why' => $request->input('why'),
            'bundle' => $request->input('bundle'),
            'two_min_version' => $request->input('twoMin'),
            'stack' => $request->input('stack'),
            'duration' => $request->input('duration'),
            'reward' => $request->input('reward'),
            'difficulty' => $request->input('diff', $habit->difficulty),
            'category_id' => $request->input('categoryId', $habit->category_id),
            'reminder_time' => $request->input('reminderTime', $habit->reminder_time),
        ]);

        return response()->json($habit->fresh()->toApiArray());
    }

    public function destroy(Habit $habit): JsonResponse
    {
        $habit->delete();

        return response()->json(['ok' => true]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\HabitCompletion;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;

class ResetController extends Controller
{
    public function destroy(): JsonResponse
    {
        // Delete in foreign-key order
        HabitCompletion::query()->delete();
        Habit::query()->delete();
        UserProfile::query()->delete();

        return response()->json(['ok' => true]);
    }
}

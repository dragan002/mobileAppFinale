<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use App\Models\WeeklyReflection;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReflectionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'week_of' => ['required', 'date_format:Y-m-d'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = UserProfile::first();

        if (! $user) {
            return response()->json(['error' => 'No user profile found.'], 422);
        }

        $weekOf = Carbon::parse($request->week_of)->format('Y-m-d');

        $reflection = WeeklyReflection::where('user_profile_id', $user->id)
            ->where('week_of', $weekOf)
            ->first();

        if ($reflection) {
            $reflection->update(['note' => $request->input('note', '')]);
        } else {
            $reflection = WeeklyReflection::create([
                'user_profile_id' => $user->id,
                'week_of' => $weekOf,
                'note' => $request->input('note', ''),
            ]);
        }

        return response()->json([
            'ok' => true,
            'week_of' => $weekOf,
        ]);
    }
}

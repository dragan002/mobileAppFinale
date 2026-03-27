<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $user = UserProfile::first();
        $categories = Category::query()
            ->where(function ($q) use ($user) {
                $q->where('is_preset', true);
                if ($user) {
                    $q->orWhere('user_profile_id', $user->id);
                }
            })
            ->orderBy('sort_order')
            ->get();

        return response()->json(
            $categories->map->toApiArray()->values()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'required|string',
        ]);

        $user = UserProfile::first();

        if (! $user) {
            return response()->json(['error' => 'User not found'], 422);
        }

        $maxSortOrder = Category::where('user_profile_id', $user->id)->max('sort_order') ?? 0;

        $category = Category::create([
            ...$validated,
            'user_profile_id' => $user->id,
            'is_preset' => false,
            'sort_order' => $maxSortOrder + 1,
        ]);

        return response()->json($category->toApiArray(), 201);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->is_preset) {
            return response()->json(['error' => 'Cannot delete preset categories'], 403);
        }

        $category->delete();

        return response()->json(['ok' => true]);
    }
}

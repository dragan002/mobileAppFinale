<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SetupController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:50'],
            'identity'      => ['required', 'string'],
            'identityLabel' => ['required', 'string'],
            'identityIcon'  => ['required', 'string'],
        ]);

        $profile = UserProfile::updateOrCreate(
            ['id' => 1],
            [
                'name'           => $request->name,
                'identity'       => $request->identity,
                'identity_label' => $request->identityLabel,
                'identity_icon'  => $request->identityIcon,
            ]
        );

        return response()->json(['ok' => true, 'name' => $profile->name]);
    }
}

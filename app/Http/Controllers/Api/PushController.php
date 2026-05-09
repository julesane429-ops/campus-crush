<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'expo_token' => 'required|string',
        ]);

        $request->user()->update(['expo_push_token' => $request->expo_token]);

        return response()->json(['message' => 'Token enregistré.']);
    }

    public function unregister(Request $request): JsonResponse
    {
        $request->user()->update(['expo_push_token' => null]);

        return response()->json(['message' => 'Token supprimé.']);
    }
}

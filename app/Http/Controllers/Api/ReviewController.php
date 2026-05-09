<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $review = Review::where('user_id', $request->user()->id)->first();
        return response()->json(['review' => $review]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:500',
        ]);

        $user = $request->user();

        $review = Review::updateOrCreate(
            ['user_id' => $user->id],
            ['rating' => $request->rating, 'comment' => $request->comment, 'status' => 'pending', 'is_featured' => false],
        );

        $created = $review->wasRecentlyCreated;
        return response()->json(
            ['message' => $created ? 'Avis envoyé. Merci !' : 'Avis mis à jour.', 'review' => $review],
            $created ? 201 : 200,
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $user   = $request->user();
        $review = Review::where('user_id', $user->id)->first();

        if (!$review) {
            return response()->json(['message' => 'Aucun avis trouvé.'], 404);
        }

        $review->delete();

        return response()->json(['message' => 'Avis supprimé.']);
    }
}

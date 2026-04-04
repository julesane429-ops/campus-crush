<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Soumettre ou modifier son avis.
     */
    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:500',
        ]);

        $user = Auth::user();

        Review::updateOrCreate(
            ['user_id' => $user->id],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
                'status' => 'pending', // Repasse en attente si modifié
                'is_featured' => false,
            ]
        );

        return back()->with('success', 'Merci pour ton avis ! Il sera visible après validation.');
    }

    /**
     * Supprimer son avis.
     */
    public function destroy()
    {
        Review::where('user_id', Auth::id())->delete();
        return back()->with('success', 'Avis supprimé.');
    }
}

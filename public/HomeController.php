<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Si connecté, aller au swipe
        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return Auth::user()->hasProfile()
                ? redirect()->route('swipe')
                : redirect()->route('profile.create');
        }

        // Première visite → onboarding
        if (!$request->cookie('cc_visited')) {
            return response()
                ->view('onboarding')
                ->cookie('cc_visited', '1', 60 * 24 * 365); // 1 an
        }

        // Visiteur déjà venu → page d'accueil
        return view('home');
    }
}

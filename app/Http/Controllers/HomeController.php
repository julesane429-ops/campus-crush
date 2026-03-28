<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        // Si l'utilisateur est connecté et a un profil, rediriger vers swipe
        if (auth()->check() && auth()->user()->hasProfile()) {
            return redirect()->route('swipe');
        }

        return view('home');
    }
}

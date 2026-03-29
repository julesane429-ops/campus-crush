<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'prenom'   => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(6)],
            'terms' => 'required|accepted',
        ]);

        $user = User::create([
            'name'     => $validated['prenom'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // 🎁 Créer l'essai gratuit de 30 jours
        Subscription::createTrial($user->id);
        
        Auth::login($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success'  => true,
                'redirect' => route('profile.create'),
            ]);
        }

        return redirect()->route('profile.create');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            // Vérifier si banni
            if (Auth::user()->isBanned()) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'email' => 'Votre compte a été suspendu. Contactez le support.',
                ]);
            }

            $request->session()->regenerate();

            if (Auth::user()->isAdmin()) {
                return redirect()->intended('/admin');
            }

            $redirect = Auth::user()->hasProfile() ? '/swipe' : '/profile/create';
            return redirect()->intended($redirect);
        }

        return back()->withErrors([
            'email' => 'Identifiants invalides.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

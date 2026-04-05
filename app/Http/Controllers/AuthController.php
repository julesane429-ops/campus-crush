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
    public function showRegister(Request $request)
    {
        // Mémoriser le code parrain en session si l'URL contient ?ref=XXXX
        if ($request->has('ref')) {
            session(['referral_code' => $request->input('ref')]);
        }

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

        // Générer le slug du profil public
        $baseSlug = \Illuminate\Support\Str::slug($user->name);
        $slug = $baseSlug;
        $i = 1;
        while (User::where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }
        $user->update(['slug' => $slug]);

        // Lier les crushes anonymes reçus avant l'inscription
\App\Models\AnonymousCrush::where('target_identifier', $user->email)
    ->whereNull('target_user_id')
    ->update(['target_user_id' => $user->id]);
    
        // 🎁 Créer l'essai gratuit de 30 jours
        Subscription::createTrial($user->id);

        // Générer le code de parrainage de ce nouvel utilisateur
        $user->update([
            'referral_code' => strtoupper(substr(md5($user->id . $user->email), 0, 8)),
        ]);

        // Si un code parrain est présent dans la session ou la requête
        $refCode = $request->input('ref') ?? session('referral_code');
        if ($refCode) {
            $referrer = \App\Models\User::where('referral_code', $refCode)
                ->where('id', '!=', $user->id)
                ->first();

            if ($referrer) {
                $user->update(['referred_by' => $referrer->id]);
                \App\Models\Referral::create([
                    'referrer_id' => $referrer->id,
                    'referred_id' => $user->id,
                    'rewarded'    => false,
                ]);
            }
            session()->forget('referral_code');
        }

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

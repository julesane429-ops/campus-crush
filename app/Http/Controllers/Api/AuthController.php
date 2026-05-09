<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prenom'   => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => 'required|accepted',
        ]);

        $user = User::create([
            'name'     => $validated['prenom'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Générer le slug du profil public
        $baseSlug = Str::slug($user->name);
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

        // Créer l'essai gratuit de 30 jours
        Subscription::createTrial($user->id);

        // Générer le code de parrainage
        $user->update([
            'referral_code' => strtoupper(substr(md5($user->id . $user->email), 0, 8)),
        ]);

        // Traiter le code parrain si fourni
        $refCode = $request->input('ref');
        if ($refCode) {
            $referrer = User::where('referral_code', $refCode)
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
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Identifiants invalides.'], 401);
        }

        $user = Auth::user();

        if ($user->isBanned()) {
            Auth::logout();
            return response()->json([
                'message' => 'Votre compte a été suspendu. Contactez le support.',
                'error'   => 'banned',
            ], 403);
        }

        // Révoquer les anciens tokens mobiles pour éviter la prolifération
        $user->tokens()->where('name', 'mobile')->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->formatUser($user->load('profile', 'subscription')),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile.universityModel', 'subscription');

        return response()->json($this->formatUser($user));
    }

    private function formatUser(User $user): array
    {
        $profile = $user->relationLoaded('profile') ? $user->profile : null;
        $sub     = $user->relationLoaded('subscription') ? $user->subscription : null;

        return [
            'id'                  => $user->id,
            'name'                => $user->name,
            'email'               => $user->email,
            'slug'                => $user->slug,
            'referral_code'       => $user->referral_code,
            'is_admin'            => $user->is_admin,
            'streak_days'         => $user->streak_days,
            'streak_badge'        => $user->streak_badge,
            'ai_chat_unlocked'    => $user->ai_chat_unlocked,
            'has_profile'         => $profile !== null,
            'profile'             => $profile ? [
                'id'              => $profile->id,
                'age'             => $profile->age,
                'gender'          => $profile->gender,
                'ufr'             => $profile->ufr,
                'promotion'       => $profile->promotion,
                'level'           => $profile->level,
                'bio'             => $profile->bio,
                'interests'       => $profile->interests_array,
                'photo_url'       => $profile->photo_url,
                'university'      => $profile->university_name,
                'university_id'   => $profile->university_id,
                'badge'           => $profile->badge,
                'is_boosted'      => $profile->isBoosted(),
                'boosted_until'   => $profile->boosted_until?->toISOString(),
                'last_seen_at'    => $profile->last_seen_at?->toISOString(),
            ] : null,
            'subscription'        => $sub ? [
                'status'          => $sub->status,
                'is_active'       => $sub->isActive(),
                'is_trial'        => $sub->isTrial(),
                'days_remaining'  => $sub->daysRemaining(),
                'ends_at'         => ($sub->status === 'trial' ? $sub->trial_ends_at : $sub->ends_at)?->toISOString(),
            ] : null,
        ];
    }
}

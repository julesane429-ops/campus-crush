<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Models\Profile;
use App\Models\Like;
use App\Models\Matche;
use App\Models\University;

class ProfileController extends Controller
{
    public function create()
    {
        if (Auth::user()->profile) {
            return redirect()->route('swipe');
        }

        $universities = University::where('is_active', true)->orderBy('short_name')->get();

        return view('profile.create', compact('universities'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Non connecté'], 401);
            }
            return redirect()->route('login');
        }

        if ($user->profile) {
            if ($request->expectsJson()) return response()->json(['success' => true]);
            return redirect()->route('swipe');
        }

        $validated = $request->validate([
            'age'            => 'required|integer|min:17|max:60',
            'gender'         => 'required|in:homme,femme',
            'ufr'            => 'required|string|max:20',
            'level'          => 'required|string|in:L1,L2,L3,M1,M2',
            'bio'            => 'required|string|max:200',
            'promotion'      => 'nullable|string|max:10',
            'photo'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'interests'      => 'nullable|string|max:500',
            'university_id'  => 'nullable|exists:universities,id',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
            $photoPath = $request->file('photo')->store('profiles', $disk);
        }

        // Déterminer l'université
        $universityId = $validated['university_id'] ?? null;
        $universityName = 'UGB';

        if ($universityId) {
            $uni = University::find($universityId);
            $universityName = $uni ? $uni->short_name : 'UGB';
        }

        $profile = Profile::create([
            'user_id'        => $user->id,
            'age'            => $validated['age'],
            'gender'         => $validated['gender'],
            'ufr'            => $validated['ufr'],
            'level'          => $validated['level'],
            'bio'            => $validated['bio'],
            'promotion'      => $validated['promotion'] ?? null,
            'photo'          => $photoPath,
            'interests'      => $validated['interests'] ?? null,
            'field_of_study' => 'Non précisé',
            'university'     => $universityName,
            'university_id'  => $universityId,
        ]);

        // 👑🏆 Attribution automatique des badges Campus Queen / King
        // On exclut les profils seedés (photo avatars/F* ou avatars/H*)
        // pour ne compter que les vraies inscriptions
        $queenLimit = 100;
        $kingLimit  = 50;

        if ($profile->gender === 'femme') {
            $realFemales = \App\Models\Profile::where('gender', 'femme')
                ->where(function ($q) {
                    $q->whereNull('photo')
                        ->orWhere('photo', 'NOT LIKE', 'avatars/F%');
                })->count();

            if ($realFemales <= $queenLimit) {
                $profile->update(['badge' => 'queen']);
            }
        } elseif ($profile->gender === 'homme') {
            $realMales = \App\Models\Profile::where('gender', 'homme')
                ->where(function ($q) {
                    $q->whereNull('photo')
                        ->orWhere('photo', 'NOT LIKE', 'avatars/H%');
                })->count();

            if ($realMales <= $kingLimit) {
                $profile->update(['badge' => 'king']);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'redirect' => route('swipe')]);
        }

        // 🎁 Récompenser le parrain si ce filleul a été parrainé
        $referral = \App\Models\Referral::where('referred_id', $user->id)
            ->where('rewarded', false)
            ->first();

        if ($referral) {
            // Accorder 7 jours de premium au parrain
            $referrerSub = \App\Models\Subscription::where('user_id', $referral->referrer_id)
                ->latest()->first();

            if ($referrerSub) {
                $endDate = $referrerSub->ends_at && $referrerSub->ends_at->isFuture()
                    ? $referrerSub->ends_at
                    : now();

                $referrerSub->update([
                    'ends_at'       => $endDate->addDays(7),
                    'trial_ends_at' => $referrerSub->status === 'trial'
                        ? ($referrerSub->trial_ends_at?->isFuture() ? $referrerSub->trial_ends_at->addDays(7) : now()->addDays(7))
                        : $referrerSub->trial_ends_at,
                ]);
            }

            $referral->update([
                'rewarded'    => true,
                'rewarded_at' => now(),
            ]);
        }

        return redirect()->route('swipe');
    }

    public function show()
    {
        $user = Auth::user();
        $profile = $user->profile;
        if (!$profile) return redirect()->route('profile.create');

        $likesCount = Like::where('liked_user_id', $user->id)->count();
        $matchesCount = Matche::forUser($user->id)->count();
        $lastMatch = Matche::forUser($user->id)->latest()->first();

        return view('profile.show', [
            'user' => $user,
            'profile' => $profile,
            'likesCount' => $likesCount,
            'matchesCount' => $matchesCount,
            'lastMatch' => $lastMatch,
        ]);
    }

    public function edit()
    {
        $user = Auth::user();
        $profile = $user->profile;
        if (!$profile) return redirect()->route('profile.create');

        $universities = University::where('is_active', true)->orderBy('short_name')->get();

        return view('profile.edit', compact('profile', 'universities'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $profile = $user->profile;
        if (!$profile) return redirect()->route('profile.create');

        $validated = $request->validate([
            'age'            => 'required|integer|min:17|max:60',
            'gender'         => 'required|in:homme,femme',
            'ufr'            => 'required|string|max:20',
            'level'          => 'required|string|in:L1,L2,L3,M1,M2',
            'bio'            => 'nullable|string|max:200',
            'promotion'      => 'nullable|string|max:10',
            'photo'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'university_id'  => 'nullable|exists:universities,id',
        ]);

        $data = [
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'ufr' => $validated['ufr'],
            'level' => $validated['level'],
            'bio' => $validated['bio'],
            'promotion' => $validated['promotion'] ?? $profile->promotion,
        ];

        if (isset($validated['university_id'])) {
            $data['university_id'] = $validated['university_id'];
            $uni = University::find($validated['university_id']);
            if ($uni) $data['university'] = $uni->short_name;
        }

        if ($request->hasFile('photo')) {
            $diskDel = config('filesystems.default') === 's3' ? 's3' : 'public';
            if ($profile->photo) Storage::disk($diskDel)->delete($profile->photo);
            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
            $data['photo'] = $request->file('photo')->store('profiles', $disk);
        }

        $profile->update($data);

        return redirect()->route('profile.show')->with('success', 'Profil mis à jour.');
    }

    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', ['password' => ['required', 'current_password']]);
        $user = $request->user();
        $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
        if ($user->profile?->photo) Storage::disk($disk)->delete($user->profile->photo);
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return Redirect::to('/');
    }
}

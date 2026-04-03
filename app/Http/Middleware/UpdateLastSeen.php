<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Met à jour la dernière activité de l'utilisateur.
     * ✅ Fix bug #2 : throttle à 30 secondes via cache pour éviter
     * une écriture en base à chaque requête HTTP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->profile) {
            $userId   = Auth::id();
            $cacheKey = "last_seen_{$userId}";

            // N'écrire en base que si la clé cache n'existe pas encore
            if (!Cache::has($cacheKey)) {
                Auth::user()->profile->update([
                    'last_seen_at' => now(),
                ]);

                // Bloquer les prochaines écritures pendant 30 secondes
                Cache::put($cacheKey, true, now()->addSeconds(30));
            }
        }

        return $next($request);
    }
}
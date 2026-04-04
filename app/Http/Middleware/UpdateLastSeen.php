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
     * Throttled: max 1 write toutes les 30 secondes par utilisateur.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->profile) {
            $cacheKey = 'last_seen_' . Auth::id();

            // Ne met à jour que toutes les 30 secondes
            if (!Cache::has($cacheKey)) {
                Auth::user()->profile->update([
                    'last_seen_at' => now(),
                ]);
                Cache::put($cacheKey, true, 30);
            }
        }

        return $next($request);
    }
}

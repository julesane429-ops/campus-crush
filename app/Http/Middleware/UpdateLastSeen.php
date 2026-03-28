<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Met à jour la dernière activité de l'utilisateur.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->profile) {
            Auth::user()->profile->update([
                'last_seen_at' => now(),
            ]);
        }

        return $next($request);
    }
}

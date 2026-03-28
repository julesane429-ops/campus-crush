<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileExists
{
    /**
     * Redirige vers la création de profil si l'utilisateur n'en a pas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Les admins passent toujours
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->profile) {
            if (!$request->routeIs('profile.create') && !$request->routeIs('profile.store')) {
                return redirect()->route('profile.create')
                    ->with('info', 'Veuillez d\'abord créer votre profil.');
            }
        }

        return $next($request);
    }
}

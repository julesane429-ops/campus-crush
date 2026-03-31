<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            // Update max toutes les 30 secondes pour éviter trop de queries
            $user = Auth::user();
            if (!$user->last_seen_at || $user->last_seen_at->diffInSeconds(now()) > 30) {
                $user->update(['last_seen_at' => now()]);
            }
        }

        return $next($request);
    }
}
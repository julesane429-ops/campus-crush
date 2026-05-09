<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isBanned()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error'   => 'banned',
                    'message' => 'Votre compte a été suspendu. Contactez le support.',
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte a été suspendu. Contactez le support.']);
        }

        return $next($request);
    }
}

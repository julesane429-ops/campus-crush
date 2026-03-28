<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Routes qui ne nécessitent PAS d'abonnement actif.
     */
    private array $excludedRoutes = [
        'subscription.index',
        'subscription.pay',
        'subscription.confirm',
        'profile.show',
        'profile.edit',
        'profile.update',
        'profile.create',
        'profile.store',
        'profile.destroy',
        'settings',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Les admins ont accès sans abonnement
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Vérifier si la route est exclue
        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, $this->excludedRoutes)) {
            return $next($request);
        }

        // Vérifier l'abonnement
        $sub = $user->getOrCreateSubscription();

        if (!$sub->isActive()) {
            return redirect()->route('subscription.index')
                ->with('error', 'Votre abonnement a expiré. Renouvelez pour continuer.');
        }

        return $next($request);
    }
}

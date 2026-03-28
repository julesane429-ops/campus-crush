<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushController extends Controller
{
    /**
     * Enregistrer un abonnement push.
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|url',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        $user = Auth::user();

        PushSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'endpoint' => $request->endpoint,
            ],
            [
                'p256dh' => $request->keys['p256dh'],
                'auth' => $request->keys['auth'],
                'content_encoding' => $request->contentEncoding ?? 'aesgcm',
            ]
        );

        return response()->json(['success' => true, 'message' => 'Notifications activées']);
    }

    /**
     * Supprimer un abonnement push.
     */
    public function unsubscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|url',
        ]);

        PushSubscription::where('user_id', Auth::id())
            ->where('endpoint', $request->endpoint)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Notifications désactivées']);
    }

    /**
     * Retourner la clé publique VAPID (nécessaire côté JS).
     */
    public function vapidPublicKey()
    {
        return response()->json([
            'publicKey' => config('webpush.vapid.public_key'),
        ]);
    }
}

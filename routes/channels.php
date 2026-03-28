<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Matche;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Channels de diffusion pour le temps réel.
| - chat.{matchId}  : Presence channel pour les conversations
| - user.{userId}   : Private channel pour les notifications personnelles
|
*/

/**
 * Canal de présence pour le chat.
 * Seuls les 2 utilisateurs du match peuvent rejoindre.
 */
Broadcast::channel('chat.{matchId}', function ($user, $matchId) {
    $match = Matche::find($matchId);

    if (!$match) {
        return false;
    }

    if ($user->id === $match->user1_id || $user->id === $match->user2_id) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'photo' => $user->profile?->photo_url,
        ];
    }

    return false;
});

/**
 * Canal privé pour les notifications de l'utilisateur.
 */
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

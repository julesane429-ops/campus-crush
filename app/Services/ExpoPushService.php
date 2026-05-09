<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushService
{
    private const EXPO_PUSH_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * Envoyer une notification push via l'API Expo.
     */
    public function send(User $user, string $title, string $body, array $data = [], string $sound = 'default'): bool
    {
        $token = $user->expo_push_token;

        if (!$token || !str_starts_with($token, 'ExponentPushToken[')) {
            return false;
        }

        try {
            $response = Http::post(self::EXPO_PUSH_URL, [
                'to'    => $token,
                'title' => $title,
                'body'  => $body,
                'data'  => $data,
                'sound' => $sound,
            ]);

            if ($response->failed()) {
                Log::warning('ExpoPush failed', ['user' => $user->id, 'response' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('ExpoPush exception', ['user' => $user->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function notifyNewMatch(User $user, string $otherName): void
    {
        $this->send($user, '❤️ Nouveau match !', "Tu as matché avec {$otherName} !", ['type' => 'new_match']);
    }

    public function notifyNewMessage(User $user, string $senderName, string $preview, int $matchId): void
    {
        $this->send($user, "💬 {$senderName}", $preview, ['type' => 'new_message', 'match_id' => $matchId]);
    }

    public function notifyNewLike(User $user): void
    {
        $this->send($user, '⭐ Quelqu\'un t\'a liké !', 'Ouvre l\'app pour voir qui.', ['type' => 'new_like']);
    }
}

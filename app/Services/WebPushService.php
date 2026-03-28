<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushService
{
    private ?WebPush $webPush = null;

    private function getWebPush(): WebPush
    {
        if ($this->webPush === null) {
            $auth = [
                'VAPID' => [
                    'subject' => config('app.url'),
                    'publicKey' => config('webpush.vapid.public_key'),
                    'privateKey' => config('webpush.vapid.private_key'),
                ],
            ];

            $this->webPush = new WebPush($auth);
            $this->webPush->setAutomaticPadding(true);
        }

        return $this->webPush;
    }

    /**
     * Envoyer une notification push à un utilisateur.
     */
    public function sendToUser(User $user, string $title, string $body, ?string $url = null, ?string $icon = null): void
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/',
            'icon' => $icon ?? '/images/icons/icon-192x192.png',
            'badge' => '/images/icons/icon-72x72.png',
        ]);

        $webPush = $this->getWebPush();

        foreach ($subscriptions as $sub) {
            try {
                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->p256dh,
                    'authToken' => $sub->auth,
                    'contentEncoding' => $sub->content_encoding ?? 'aesgcm',
                ]);

                $webPush->queueNotification($subscription, $payload);
            } catch (\Exception $e) {
                Log::warning('Push subscription invalid', ['id' => $sub->id, 'error' => $e->getMessage()]);
            }
        }

        // Envoyer toutes les notifications en file
        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                $endpoint = $report->getRequest()->getUri()->__toString();
                Log::info('Push failed', ['endpoint' => $endpoint, 'reason' => $report->getReason()]);

                // Supprimer les abonnements expirés (410 Gone)
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $endpoint)->delete();
                    Log::info('Expired push subscription removed');
                }
            }
        }
    }

    /**
     * Envoyer une notification de nouveau match.
     */
    public function notifyNewMatch(User $user, string $matchName): void
    {
        $this->sendToUser(
            $user,
            '💕 Nouveau Match !',
            "{$matchName} t'a aussi liké ! Envoie-lui un message 😏",
            '/matches'
        );
    }

    /**
     * Envoyer une notification de nouveau message.
     */
    public function notifyNewMessage(User $user, string $senderName, string $preview, int $matchId): void
    {
        $this->sendToUser(
            $user,
            $senderName,
            $preview,
            "/messages/{$matchId}"
        );
    }
}

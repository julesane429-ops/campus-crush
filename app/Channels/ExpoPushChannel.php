<?php

namespace App\Channels;

use App\Services\ExpoPushService;
use Illuminate\Notifications\Notification;

class ExpoPushChannel
{
    public function __construct(private ExpoPushService $expo) {}

    public function send(mixed $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toExpoPush')) return;

        [$title, $body, $data] = $notification->toExpoPush($notifiable);
        $this->expo->send($notifiable, $title, $body, $data);
    }
}

<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public static function send(User $user, string $title, string $message, string $type = 'system', ?string $url = null): Notification
    {
        return $user->notifications()->create(compact('title', 'message', 'type', 'url'));
    }
}

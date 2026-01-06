<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function store(array $array): Notification
    {
        $notification = Notification::create([
            'sender_id' => auth()->id(),
            'date' => now(),
            ...$array,
        ]);

        return $notification;
    }

    public function update(Notification $notification, array $array): Notification
    {
        $notification->update($array);

        return $notification;
    }
}

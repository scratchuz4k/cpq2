<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function store(array $array): Notification
    {
        $system = User::where('email', 'system@cpq.cloudmarket.com')->first();
        $notification = Notification::create([
            'sender_id' => auth()->id() ?? $system->id,
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

    public function markAllAsRead(User $user): void
    {
        foreach ($user->notifications()->where('is_read', false)->get() as $notification) {
            $notification->update(['is_read' => true]);
        }
    }
}

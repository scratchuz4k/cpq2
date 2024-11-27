<?php

namespace App\Events;

use App\Models\Quote;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuoteStatusEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Quote $quote;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct(Quote $quote, $oldStatus, $newStatus)
    {
        $this->quote = $quote;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}

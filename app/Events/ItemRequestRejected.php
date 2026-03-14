<?php

namespace App\Events;

use App\Models\ItemRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ItemRequestRejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ItemRequest $itemRequest

    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\ItemRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ItemRequestCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ItemRequest $itemRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'mdi-bell-ring',
            'message' => $this->itemRequest->requester->name.' meminta barang',
            'url' => route('items.requests', [
                'search' => $this->itemRequest->requester->name,
                'status' => 'pending',
            ]),
        ];
    }
}

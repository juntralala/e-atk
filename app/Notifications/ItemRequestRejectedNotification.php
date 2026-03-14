<?php

namespace App\Notifications;

use App\Models\ItemRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ItemRequestRejectedNotification extends Notification
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
            'icon' => 'mdi-close-circle',
            'message' => 'Permintaan barang kamu ditolak dengan catatan: '.$this->itemRequest->responder_notes,
            'url' => route('items.requests'),
        ];
    }
}

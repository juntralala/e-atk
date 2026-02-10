<?php

namespace App\Notifications;

use App\Models\ItemRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ItemRequestAcceptedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ItemRequest $itemRequest
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $note = " " . $this->itemRequest?->responder_notes ?? "";
        return [
            'icon' => 'mdi-check-circle  ',
            'message' => "Permintaan barang kamu diterima$note",
            'url' => route('items.requests')
        ];
    }
}

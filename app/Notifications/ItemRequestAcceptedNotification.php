<?php

namespace App\Notifications;

use App\Models\ItemRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ItemRequestAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ItemRequest $itemRequest
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', WebPushChannel::class];
    }

    public function toArray(object $notifiable): array
    {
        $note = $this->itemRequest?->responder_notes ? 'dengan catatan '.$this->itemRequest?->responder_notes : '';

        return [
            'icon' => 'mdi-check-circle  ',
            'message' => "Permintaan barang kamu diterima $note",
            'url' => route('items.requests'),
        ];
    }

    public function toWebPush(object $notifiable)
    {
        $note = $this->itemRequest?->responder_notes ? 'dengan catatan '.$this->itemRequest?->responder_notes : '';

        return (new WebPushMessage)
            ->title('Permintaan barang diterima')
            ->body("Permintaan barang dan akan segera diantarkan $note")
            ->data([
                'url' => route('items.requests', ['status' => 'accepted']),
            ]);
    }
}

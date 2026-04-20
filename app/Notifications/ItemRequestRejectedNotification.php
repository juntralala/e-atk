<?php

namespace App\Notifications;

use App\Models\ItemRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ItemRequestRejectedNotification extends Notification implements ShouldQueue
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
        return [
            'icon' => 'mdi-close-circle',
            'message' => 'Permintaan barang kamu ditolak dengan catatan: '.$this->itemRequest->responder_notes,
            'url' => route('items.requests'),
        ];
    }

    public function toWebPush(object $notifiable)
    {
        $rejector = $this->itemRequest->responder()->select('id', 'name')->first()->name;
        $note = $this->itemRequest?->responder_notes ? ", dengan catatan dari $rejector \"".$this->itemRequest->responder_notes.'"' : '';

        return (new WebPushMessage)
            ->title('Permintaan barang ditolak!')
            ->body('Permintaan barang ditolak '.$note)
            ->data([
                'url' => route('items.requests', ['status' => 'rejected']),
            ]);
    }
}

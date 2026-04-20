<?php

namespace App\Notifications;

use App\Models\ItemRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ItemRequestCreatedNotification extends Notification implements ShouldQueue
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
            'icon' => 'mdi-bell-ring',
            'message' => $this->itemRequest->requester->name.' meminta barang',
            'url' => route('items.requests', [
                'search' => $this->itemRequest->requester->name,
                'status' => 'pending',
            ]),
        ];
    }

    public function toWebPush(object $notifiable)
    {
        $details = $this->itemRequest->itemRequestDetails()
            ->with('item:id,name')
            ->select(['id', 'item_id'])
            ->get();
        $itemNames = $details->pluck('item.name')->filter()->join(', ');

        return (new WebPushMessage)
            ->title('Permintaan barang baru!')
            ->body($this->itemRequest->requester->name.' meminta '.$itemNames)
            ->data([
                'url' => route('items.requests', ['status' => 'pending']),
            ]);
    }
}

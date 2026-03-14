<?php

namespace App\Listeners;

use App\Events\ItemRequestAccepted;
use App\Events\ItemRequestCreated;
use App\Events\ItemRequestRejected;
use App\Models\User;
use App\Notifications\ItemRequestAcceptedNotification;
use App\Notifications\ItemRequestCreatedNotification;
use App\Notifications\ItemRequestRejectedNotification;

class SendItemRequestNotification
{
    public function __construct() {}

    public function handle(ItemRequestCreated|ItemRequestAccepted|ItemRequestRejected $event): void
    {
        if ($event instanceof ItemRequestCreated) {
            /** @var \Illuminate\Database\Eloquent\Collection<User> */
            $users = User::whereHas('role', function ($q) {
                $q->whereIn('name', ['petugas', 'administrator']);
            })->get();
            foreach ($users as $user) {
                $user->notify(new ItemRequestCreatedNotification($event->itemRequest));
            }
        } elseif ($event instanceof ItemRequestAccepted) {
            $event->itemRequest
                ->requester
                ->notify(new ItemRequestAcceptedNotification($event->itemRequest));
        } elseif ($event instanceof ItemRequestRejected) {
            $event->itemRequest
                ->requester
                ->notify(new ItemRequestRejectedNotification($event->itemRequest));
        }
    }
}

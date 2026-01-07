<?php

namespace App\Listeners;

use App\Events\LoginFailed;
use App\Models\User;
use App\Notifications\InvalidLogin;

class NotifLoginFailed
{
    public function __construct()
    {}

    public function handle(LoginFailed $event): void
    {
        $users = User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->get();
        foreach($users as $user) {
            $user->notify(new InvalidLogin($event->username));
        }
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvalidLogin extends Notification
{
    // use Queueable;

    public function __construct(
        public $username
    )
    {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'mdi-alert',
            'message' => "Seseorang telah mencoba login pada akun dengan username \"$this->username\", tetapi gagal!",
            'url' => null
        ];
    }
}
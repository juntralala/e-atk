<?php

namespace App\Dto\Response;

use Illuminate\Support\Carbon;

class Notification
{
    public function __construct(
        public ?string $id,
        public ?string $title,
        public string $message,
        public ?string $url,
        public ?string $icon,
        public Carbon $created_at,
        public ?Carbon $read_at,

        public $meta = null
    )
    {}
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ItemRequest extends Model
{
    use HasUuids;

    protected $table = 'item_requests';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'requester_id',
        'status',
        'request_date',
        'responder_id',
        'responder_notes',
        'responded_at'
    ];

    public function casts()
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    public function itemRequestDetails(): HasMany
    {
        return $this->hasMany(ItemRequestDetail::class);
    }

}
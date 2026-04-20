<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PushSubcription extends Model
{
    use HasUuids;

    protected $table = 'push_subcriptions';

    protected $keyType = 'string';

    public $timestamps = true;

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

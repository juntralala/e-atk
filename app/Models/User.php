<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'role_id',
        'profile_photo_path',
        'password'
    ];

    protected $hidden = [
        'password',
    ];

    protected $with = ['role'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function itemAdditions(){
        return $this->hasMany(ItemAddition::class);
    }

    public function itemRequests() {
        return $this->hasMany(ItemRequest::class, 'requester_id', 'id');
    }

    public function respondedItemRequests() {
        return $this->hasMany(ItemRequest::class, 'responder_id', 'id');
    }
}

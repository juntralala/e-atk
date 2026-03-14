<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \Illuminate\Database\Eloquent\Collection<ItemAdditionDetail> $itemAdditionDetails
 */
class ItemAddition extends Model
{
    use HasUuids;

    protected $table = 'item_additions';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'user_id',
        'addition_date',
    ];

    public function casts(): array
    {
        return [
            'addition_date' => 'date',
            'created_at' => 'datetime',
        ];

    }

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_at ??= now();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function itemAdditionDetails()
    {
        return $this->hasMany(ItemAdditionDetail::class);
    }
}

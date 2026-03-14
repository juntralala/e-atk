<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \Carbon\Carbon $adddition_date
 */
class ItemAdditionDetail extends Model
{
    use HasUuids;

    protected $table = 'item_addition_details';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'item_addition_id',
        'item_id',
        'quantity',
        'price',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_at ??= now();
        });
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function itemAddition()
    {
        return $this->belongsTo(ItemAddition::class, 'item_addition_id', 'id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentDetail extends Model
{
    use HasUuids;

    protected $fillable = [
        'stock_adjustment_id',
        'item_id',
        'old_stock',
        'new_stock',
        'price',
        'reason_id',
    ];

    public function stockAdjustment()
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function reason()
    {
        return $this->belongsTo(StockAdjustmentReason::class, 'reason_id');
    }
}

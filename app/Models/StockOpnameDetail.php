<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StockOpnameDetail extends Model
{
    use HasUuids;

    protected $fillable = [
        'stock_opname_id',
        'item_id',
        'system_stock',
        'physical_stock',
        'reason_id',
        'remark',
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function reason()
    {
        return $this->belongsTo(OpnameReason::class, 'reason_id');
    }
}

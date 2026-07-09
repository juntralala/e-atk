<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAdjustmentReason extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'reason',
    ];

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustmentDetail::class, 'reason_id');
    }
}

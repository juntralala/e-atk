<?php

namespace App\Models;

use App\Enums\StockAdjustmentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'stock_opname_id',
        'status',
        'notes',
        'responder_id',
        'responded_at',
    ];

    protected $casts = [
        'status' => StockAdjustmentStatus::class,
        'responded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function details()
    {
        return $this->hasMany(StockAdjustmentDetail::class);
    }
}

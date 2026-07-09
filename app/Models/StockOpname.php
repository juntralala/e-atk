<?php

namespace App\Models;

use App\Enums\OpnameStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasUuids;

    protected $table = 'stock_opname';

    protected $fillable = [
        'opname_date',
        'user_id',
        'responder_id',
        'status',
    ];

    protected $casts = [
        'opname' => 'datetime',
        'status' => OpnameStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    public function stockOpnameDetails()
    {
        return $this->hasMany(StockOpnameDetail::class);
    }

    public function stockAdjustment()
    {
        return $this->hasOne(StockAdjustment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpnameReason extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['reason'];

    public function stockOpnameDetails()
    {
        return $this->hasMany(StockOpnameDetail::class, 'reason_id');
    }

    public function scopeSortedWithOthersAsLast($query)
    {
        $others = 'Lainnya';

        return $query->orderByRaw("reason = '$others' ASC")->orderBy('reason');
    }
}

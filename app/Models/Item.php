<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'items';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['name', 'unit_id', 'specification_name', 'stock', 'price'];

    // PENTING: Tambahkan ini agar accessor ikut di-serialize
    protected $appends = ['spesification_name'];

    // Untuk backward compatibility saat create/update
    public function fill(array $attributes)
    {
        if (isset($attributes['spesification_name'])) {
            $attributes['specification_name'] = $attributes['spesification_name'];
            unset($attributes['spesification_name']);
        }

        return parent::fill($attributes);
    }

    // ACCESSOR: untuk baca $item->spesification_name
    public function getSpesificationNameAttribute()
    {
        return $this->attributes['specification_name'] ?? null;
    }

    // MUTATOR: untuk set $item->spesification_name = 'value'
    public function setSpesificationNameAttribute($value)
    {
        $this->attributes['specification_name'] = $value;
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function itemAdditionDetails()
    {
        return $this->hasMany(ItemAddition::class);
    }

    public function itemRequestDetails()
    {
        return $this->hasMany(ItemRequestDetail::class);
    }
}

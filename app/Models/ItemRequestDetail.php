<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ItemRequestDetail extends Model
{
    use HasUuids;
    
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'item_request_id',
        'item_id',
        'requested_quantity',
        'responded_quantity', // ← nama baru di database
        'price'
    ];
    
    // Agar accessor ikut di-serialize ke JSON
    protected $appends = ['received_quantity'];
    
    // Override fill() untuk backward compatibility saat create/update
    public function fill(array $attributes)
    {
        // Konversi nama lama ke nama baru
        if (isset($attributes['received_quantity'])) {
            $attributes['responded_quantity'] = $attributes['received_quantity'];
            unset($attributes['received_quantity']);
        }
        
        return parent::fill($attributes);
    }
    
    // ACCESSOR: untuk baca $model->received_quantity (nama lama)
    public function getReceivedQuantityAttribute()
    {
        return $this->attributes['responded_quantity'] ?? null;
    }
    
    // MUTATOR: untuk set $model->received_quantity = value
    public function setReceivedQuantityAttribute($value)
    {
        $this->attributes['responded_quantity'] = $value;
    }
    
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    
    public function itemRequest()
    {
        return $this->belongsTo(ItemRequest::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $timestamps = true;

    public $incrementing = false;

    protected $fillable = [
        'logo',
        'icon',
        'application_name',
        'institution_name',
        'institution_address',
        'institution_phone',
    ];
}

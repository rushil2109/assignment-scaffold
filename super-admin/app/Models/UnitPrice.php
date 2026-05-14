<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitPrice extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'asset_code',
        'date',
        'price',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:6',
    ];
}

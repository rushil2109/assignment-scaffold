<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holding extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'account_id',
        'asset_code',
        'units',
        'unit_price',
        'balance',
        'effective_date',
    ];

    protected $casts = [
        'units' => 'decimal:6',
        'unit_price' => 'decimal:6',
        'balance' => 'decimal:2',
        'effective_date' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}

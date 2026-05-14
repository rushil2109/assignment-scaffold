<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestmentProfile extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'account_id',
        'asset_code',
        'percentage',
        'is_current',
        'effective_from',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'is_current' => 'boolean',
        'effective_from' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'admin_id',
        'email',
        'mobile',
        'preferred_name',
        'residential_address',
        'postal_address',
    ];

    protected $casts = [
        'residential_address' => 'array',
        'postal_address' => 'array',
    ];

    public function account(): HasOne
    {
        return $this->hasOne(Account::class);
    }
}

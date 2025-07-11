<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_cart_value',
        'max_uses_per_user',
        'first_time_user_only',
        'start_at',
        'expires_at',
        'is_active',
        'description'
    ];


    protected $casts = [
        'start_at' => 'datetime',
        'expires_at' => 'datetime',
        'first_time_user_only' => 'boolean',
        'is_active' => 'boolean',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralUsage extends Model
{
    use HasFactory;    
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    
    protected $fillable = [
        'referral_code_id',
        'referred_user_id',
        'status',
    ];

    protected $casts = [
        'referral_code_id' => 'integer',
        'referred_user_id' => 'integer',
    ];

    // Relationship to the referral code that was used
    public function referralCode()
    {
        return $this->belongsTo(ReferralCode::class);
    }
}
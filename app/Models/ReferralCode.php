<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class ReferralCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referral_code',
    ];


    //Relationship to all usage records of this referral code
    public function usages()
    {
        return $this->hasMany(ReferralUsage::class);
    }

    //Get completed referral usages count
    public function completedUsagesCount()
    {
        return $this->usages()->where('status', ReferralUsage::STATUS_COMPLETED)->count();
    }

    //Get pending referral usages count
    public function pendingUsagesCount()
    {
        return $this->usages()->where('status', ReferralUsage::STATUS_PENDING)->count();
    }

    //Generate a unique referral code
    public static function generateUniqueCode($length = 8)
    {
        $attempts = 0;
        $maxAttempts = 100;
        
        do {
            $code = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, $length);
            $attempts++;
        } while (self::where('referral_code', $code)->exists() && $attempts < $maxAttempts);
        
        if ($attempts >= $maxAttempts) {
            throw new Exception('Unable to generate unique referral code after ' . $maxAttempts . ' attempts');
        }
        
        return $code;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRetrunProductModel extends Model
{
    use HasFactory;
    protected $table = 'purchase_return_product';
    protected $fillable = [
      
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

    ];

    public function purchase()
    {
        return $this->belongsTo(PurchaseReturnModel::class, 'pr_id', 'id');
    }
}

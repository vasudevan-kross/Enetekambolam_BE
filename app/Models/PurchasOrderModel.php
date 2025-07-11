<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasOrderModel extends Model
{
    use HasFactory;
    protected $table = 'purchars_order';
    protected $fillable = [
      
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

    ];

    public function products()
    {
        return $this->hasMany(PurchaseProductModel::class, 'purchase_id', 'id');
    }
}

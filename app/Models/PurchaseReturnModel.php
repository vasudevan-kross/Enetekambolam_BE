<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnModel extends Model
{
    use HasFactory;
    protected $table = 'purchase_return';
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
        return $this->hasMany(PurchaseRetrunProductModel::class, 'pr_id', 'id');
    }
}

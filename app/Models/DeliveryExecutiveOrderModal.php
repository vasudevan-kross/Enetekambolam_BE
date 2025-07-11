<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryExecutiveOrderModal extends Model
{
    use HasFactory;

    // Define the table name if it's not following convention
    protected $table = 'delivery_executive_orders';

    // Define fillable fields for mass assignment
    
    protected $fillable = [
      
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

    ];

}

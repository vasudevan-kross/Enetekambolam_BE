<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubOderDeliveyModel extends Model
{
    use HasFactory;
    protected $table = 'subscribed_order_delivery';
    protected $fillable = [ 'order_id', 'entry_user_id', 'date', 'payment_mode', 'delivery_notes', 'created_at', 'updated_at', 'executive_id',];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [

    ];
}

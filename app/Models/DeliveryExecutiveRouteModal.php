<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryExecutiveRouteModal extends Model
{
    use HasFactory;

    // Define the table name if it's not following convention
    protected $table = 'delivery_executive_route';

    // Define fillable fields for mass assignment
    protected $fillable = [
        'delivery_executive_id',
        'delivery_route_id',
        'max_customers',
        'max_orders',
        'priority',
        'is_active'
    ];

    // Define relationships
    public function deliveryExecutive()
    {
        return $this->belongsTo(DeliveryExecutive::class, 'delivery_executive_id');
    }

    public function deliveryRoute()
    {
        return $this->belongsTo(DeliveryRoutes::class, 'delivery_route_id');
    }
}

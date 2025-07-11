<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryRoutes extends Model
{
    use HasFactory;
    protected $table = 'delivery_routes';
    protected $fillable = [
        'route_name',
        'pincode',
        'city_name',
        'latitude',
        'longitude',
        'locations',
        'is_active',
    ];

    // Cast the locations column as an array
    protected $casts = [
        'locations' => 'array',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'is_active' => 'boolean',
    ];

    public function executiveRoutes()
    {
        return $this->hasMany(DeliveryExecutiveRouteModal::class, 'delivery_route_id');
    }
}

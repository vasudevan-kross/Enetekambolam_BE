<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;


class DeliveryExecutive extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'delivery_executive';
    protected $fillable = ['name', 'email', 'password', 'phone_no1', 'executive_id'];
    protected $hidden = ['password'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    public function routes()
    {
        return $this->hasMany(DeliveryExecutiveRouteModal::class, 'delivery_executive_id');
    }
}

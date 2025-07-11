<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    use HasFactory;
    protected $table = 'product';
    protected $fillable = [
        'sku',
        'title',
        'qty_text',
        'stock_qty',
        'sub_cat_id',
        'price',
        'tax',
        'mrp',
        'offer_text',
        'description',
        'disclaimer',
        'subscription',
        'expire_days',
        'storage_type',
        'min_cart_qty',
        'max_cart_qty',
        'daily_sales_limit',
        'is_active',
        'status',
        'vendor_id',
        'approved_by',
        'purchase_price',
        'margin_percent',
        'margin_amt',
        'margin_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];
}

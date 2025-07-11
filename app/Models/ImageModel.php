<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageModel extends Model
{
    use HasFactory;

    protected $table = 'images'; // Make sure the correct table name is used.

    // Add the fields you want to allow for mass assignment
    protected $fillable = [
        'table_name',   // Column for the table name
        'table_id',     // Column for the table id (e.g., product ID)
        'image',        // Column for the image file name
        'image_type',   // Column for image type (e.g., 1 for product image)
        'updated_at',   // Column for updated_at (this can also be handled automatically)
    ];

    // You can also define $hidden to hide certain fields during serialization if necessary
    protected $hidden = [
        // Add columns to hide from serialization if required
    ];
}

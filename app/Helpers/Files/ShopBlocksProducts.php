<?php

namespace App\Helpers\Files;

use MongoDB\Laravel\Eloquent\Model;

class ShopBlocksProducts extends Model
{
    protected $connection = 'mongodb'; // Use the MongoDB connection

    protected $collection = 'products'; // Specify the collection name

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $guarded = [];

    protected $primaryKey = 'sku';
}

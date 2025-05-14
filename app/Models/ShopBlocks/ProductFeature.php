<?php

namespace App\Models\ShopBlocks;

use MongoDB\Laravel\Eloquent\Model;

class ProductFeature extends Model
{
    protected $connection = 'mongodb_shopblocks'; // Use the MongoDB connection

    protected $collection = 'product_features'; // Specify the collection name

    protected $hidden = ['product_id', '_id'];

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

namespace App\Models\ShopBlocks;

use MongoDB\Laravel\Eloquent\Model as MongodbModel;

class Model extends MongodbModel
{
    protected $connection = 'mongodb_shopblocks'; // Use the MongoDB connection

    protected $collection = 'brands'; // Specify the collection name

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $fillable = ['name'];

    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

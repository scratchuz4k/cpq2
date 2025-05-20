<?php

namespace App\Models\ShopBlocks;

use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mongodb_shopblocks'; // Use the MongoDB connection

    protected $collection = 'products'; // Specify the collection name

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $guarded = [];

    public function features()
    {
        return $this->hasMany(ProductFeature::class);
    }

    public function brand()
    {
        return $this->hasOne(Brand::class);
    }

    public function model()
    {
        return $this->hasOne(Model::class);
    }
}

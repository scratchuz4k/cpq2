<?php

namespace App\Models\ShopBlocks;

use MongoDB\Laravel\Eloquent\Model;

class Category extends Model
{
    protected $connection = 'mongodb_shopblocks'; // Use the MongoDB connection

    protected $collection = 'categories'; // Specify the collection name

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the parent user of this user.
     */
    public function parent()
    {
        return $this->hasOne(Category::class, 'id', 'parent_id');
    }
}

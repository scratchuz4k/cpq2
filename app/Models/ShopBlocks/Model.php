<?php

namespace App\Models\ShopBlocks;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    protected $connection = 'mysql_shopblocks'; // Use the MySQL connection

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $fillable = ['code', 'name', 'brand_id'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

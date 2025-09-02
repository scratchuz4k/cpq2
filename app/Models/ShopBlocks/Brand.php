<?php

namespace App\Models\ShopBlocks;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $connection = 'mysql_shopblocks'; // Use the MySQL connection

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $fillable = ['code', 'long_description', 'meta_description', 'meta_title', 'name', 'short_description', 'slug'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}

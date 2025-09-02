<?php

namespace App\Models\ShopBlocks;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Product extends EloquentModel
{
    protected $connection = 'mysql_shopblocks'; // Use the MySQL connection

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $fillable = ['sku', 'code', 'slug', 'name', 'description', 'stock', 'primary_image', 'images', 'price', 'price_plan_id', 'brand_id', 'model_id', 'category_id', 'updated', 'expire_at'];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'array',
        ];
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function model()
    {
        return $this->belongsTo(Model::class);
    }
}

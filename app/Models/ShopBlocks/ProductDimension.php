<?php

namespace App\Models\ShopBlocks;

use Illuminate\Database\Eloquent\Model;

class ProductDimension extends Model
{
    protected $connection = 'mysql_shopblocks'; // Use the MySQL connection

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $fillable = ['dimension_type_id', 'dimension_id', 'product_id'];

    public function dimension()
    {
        return $this->belongsTo(Dimension::class);
    }

    public function dimensionType()
    {
        return $this->belongsTo(DimensionType::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

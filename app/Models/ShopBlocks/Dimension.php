<?php

namespace App\Models\ShopBlocks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dimension extends Model
{
    protected $connection = 'mysql_shopblocks'; // Use the MySQL connection

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $fillable = ['dimension_type_id', 'value'];

    public function dimensionType()
    {
        return $this->belongsTo(DimensionType::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_dimensions');
    }
}

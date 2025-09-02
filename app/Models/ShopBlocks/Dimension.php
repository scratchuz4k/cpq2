<?php

namespace App\Models\ShopBlocks;

use Illuminate\Database\Eloquent\Model;

class Dimension extends Model
{
    protected $connection = 'mysql_shopblocks'; // Use the MySQL connection

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $fillable = ['dimension_type_id', 'value'];
}

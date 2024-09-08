<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrandMerchant extends Pivot
{
    use HasFactory;

    public $timestamps = false;
}

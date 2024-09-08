<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantSupplier extends Pivot
{
    use HasFactory;

    public $timestamps = false;
}

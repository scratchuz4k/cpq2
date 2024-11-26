<?php

namespace App\Models;

use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuoteDiscount extends Model
{
    use SetTenantId;

    protected $fillable = ["discountable_type", "discountable_id", "discount", "tenant_id"];

}

<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;

class ProductBundle extends Model
{
    use SetTenantId;

    protected $fillable = [
        'product_id',
        'item_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function item()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }
}

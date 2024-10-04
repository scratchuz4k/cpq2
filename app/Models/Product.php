<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'supplier_id',
        'brand_id'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }
}

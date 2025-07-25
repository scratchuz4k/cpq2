<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = [
        'configurable_type',
        'configurable_id',
        'key',
        'value',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function configurable()
    {
        return $this->morphTo('configurable');
    }
}

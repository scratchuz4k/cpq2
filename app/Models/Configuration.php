<?php

namespace App\Models;

use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Configuration  extends Model
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

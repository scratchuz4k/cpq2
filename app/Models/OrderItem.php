<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function save(array $options = []): bool
    {
        if (empty($this->tenant_id)) {
            $this->tenant_id = app('currentTenant')->id;
        }

        return parent::save($options);
    }
}

<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Models\Supplier;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spa extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = [
        "rule_type",
        "field_1",
        "field_2",
        "operation",
        "discount_value",
        "discount_percentage",
    ];


    protected function casts(): array
    {
        return [
            'data' => 'object',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

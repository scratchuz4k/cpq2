<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spa extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = [
        'rule_id',
        'field_1',
        'field_2',
        'operation_id',
        'value',
        'percentage',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(SpaRule::class);
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(SpaOperation::class);
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

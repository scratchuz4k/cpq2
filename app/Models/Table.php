<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Table extends Model
{
    use SetTenantId;

    protected $fillable = [
        'code',
        'group_name',
        'group',
        'columns',
        'tableable_type',
        'tableable_id',
    ];

    protected function casts(): array
    {
        return [
            'columns' => 'object',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tableable(): MorphTo
    {
        return $this->morphTo();
    }
}

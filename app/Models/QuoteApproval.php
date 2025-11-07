<?php

namespace App\Models;

use App\Events\QuoteApprovalEvent;
use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteApproval extends Model
{
    use SetTenantId;

    protected $fillable = [
        'status',
        'comments',
        'flow',
        'quote_id',
        'approver_id',
        'blocker_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::saved(function ($quoteApproval) {
            if ($quoteApproval->isDirty('status')) {
                QuoteApprovalEvent::dispatch($quoteApproval);
            }
        });
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id', 'id');
    }

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_id', 'id');
    }
}

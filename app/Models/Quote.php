<?php

namespace App\Models;

use App\Enums\QuoteStatusesEnum;
use App\Events\QuoteStatusEvent;
use App\Models\Scopes\TenantScope;
use App\Trait\HasConfigurations;
use App\Trait\RegisterActivity;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Quote extends Model
{
    use HasConfigurations, HasFactory, RegisterActivity, SetTenantId, SoftDeletes;

    protected $fillable = [
        'account_id',
        'solution_type_id',
        'address_line1',
        'address_postcode',
        'address_position',
        'address_city',
        'address_country',
        'address_data',
        'tenant_id',
        'name',
        'total',
        'total_tax',
        'total_gross',
        'commission_value',
        'commission_rate',
        'margin',
        'status',
        'user_id',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'address_position' => 'array',
            'address_data' => 'array',
            'margin' => 'float',
            'total' => 'float',
            'total_tax' => 'float',
            'total_gross' => 'float',
            'commission_value' => 'float',
            'commission_rate ' => 'float',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($quote) {
            if (! $quote->uuid) {
                $quote->uuid = (string) Str::uuid();
            }
        });

        static::saving(function (Quote $quote) {
            $quote->status = Quote::resolveStatus($quote);
        });

        static::saved(function ($quote) {
            if ($quote->isDirty('status')) {
                QuoteStatusEvent::dispatch($quote);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function solutionType(): BelongsTo
    {
        return $this->belongsTo(SolutionType::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class)->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(QuoteApproval::class)->orderBy('quote_approvals.id', 'desc');
    }

    public function itemsTrashed(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->withTrashed()->whereNotNull('deleted_at');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(QuoteDiscount::class);
    }

    public function sharedLink(): MorphOne
    {
        return $this->morphOne(SharedLink::class, 'shareable');
    }

    public function computeTotals()
    {
        $this->refresh();
        $raw_total = 0;

        $this->total = 0;
        $this->total_tax = 0;
        $this->total_gross = 0;

        foreach ($this->items as $item) {
            $raw_total += ($item->raw_price * $item->quantity * $item->contract_length);

            $this->total += $item->total;
            $this->total_tax += $item->total_tax;
            $this->total_gross += $item->total_gross;
        }
        $this->total = format_currency($this->total);
        $this->total_tax = format_currency($this->total_tax);
        $this->total_gross = format_currency($this->total_gross);

        $total_cost = $raw_total;
        $margin_value = $this->total - $total_cost;

        $this->margin = $this->total ? format_currency(($margin_value / $this->total) * 100) : 0;
        $this->commission_value = format_currency($margin_value * ($this->commission_rate / 100));
    }

    public static function resolveStatus(Quote $quote): string
    {
        $statuses_to_ignore = [
            QuoteStatusesEnum::PENDING->name,
            QuoteStatusesEnum::APPROVED->name,
            QuoteStatusesEnum::COMPLETED->name,
            QuoteStatusesEnum::EXPIRED->name,
        ];

        if (in_array($quote->status, $statuses_to_ignore)) {
            return $quote->status;
        }

        if ($quote->items->count()) {
            return QuoteStatusesEnum::QUOTE->name;
        }

        if (! empty($quote->address_data)) {
            return QuoteStatusesEnum::PRICE->name;
        }

        return QuoteStatusesEnum::CONFIGURE->name;
    }

    public function isBlocked(): string
    {
        $block_statuses = [
            QuoteStatusesEnum::PENDING->name,
            QuoteStatusesEnum::APPROVED->name,
            QuoteStatusesEnum::COMPLETED->name,
            QuoteStatusesEnum::EXPIRED->name,
        ];

        if (in_array($this->status, $block_statuses)) {
            return true;
        }

        return false;
    }
}

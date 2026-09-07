<?php

namespace App\Models;

use App\Enums\QuoteStatusesEnum;
use App\Events\QuoteStatusEvent;
use App\Models\Scopes\TenantScope;
use App\Trait\HasConfigurations;
use App\Trait\RegisterActivity;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'total_discount',
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
            'total_discount' => 'float',
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

    /**
     * CPQ-530: a quote is visible to a grouped user only when the user shares a
     * group with both the quote's creator and the quote's account. Ungrouped
     * users are unrestricted.
     */
    public function scopeVisibleToGroups(Builder $query, User $user): Builder
    {
        if (! $user->isGrouped()) {
            return $query;
        }

        $groupIds = $user->groupIds();

        return $query
            ->whereHas('user.groups', fn ($q) => $q->whereIn('groups.id', $groupIds))
            ->whereHas('account.groups', fn ($q) => $q->whereIn('groups.id', $groupIds));
    }

    public function isVisibleToGroups(User $user): bool
    {
        if (! $user->isGrouped()) {
            return true;
        }

        return static::withoutGlobalScopes()
            ->whereKey($this->getKey())
            ->visibleToGroups($user)
            ->exists();
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
        return $this->hasMany(QuoteItem::class)
            ->orderBy('group')
            ->orderBy('id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(QuoteApproval::class)->orderBy('quote_approvals.id', 'desc');
    }

    public function latestApproval(): HasOne
    {
        return $this->approvals()->one()->latestOfMany();
    }

    public function itemsTrashed(): HasMany
    {
        return $this->hasMany(QuoteItem::class)
            ->withTrashed()
            ->whereNotNull('deleted_at')
            ->orderBy('group')
            ->orderBy('id');
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
        $this->total = 0;
        $total_cost = 0;
        $totalDiscount = 0;

        $priceplans = PricePlan::get();
        foreach ($priceplans as $priceplan) {
            $temp = $this->items->where('pricePlan.code', $priceplan->code);
            if (! $temp->count()) {
                $this->discounts->where('discountable_type', "App\Models\PricePlan")->where('discountable_id', $priceplan->id)->each->deleteQuietly();
            }
        }

        if (! $this->items->count()) {
            $this->discounts->where('discountable_type', "App\Models\Quote")->where('discountable_id', $this->id)->each->deleteQuietly();
        }

        $discounts = collect();
        $discounts->push(...$this->discounts->where('discountable_type', "App\Models\PricePlan"));
        $discounts->push(...$this->discounts->where('discountable_type', "App\Models\Quote"));

        foreach ($discounts as $discount) {
            $discount->value = 0;
            $discount->total = 0;
        }

        foreach ($this->items as $item) {
            $total_cost += ($item->raw_price * $item->quantity * $item->contract_length);

            $itemPrice = $item->price;

            foreach ($discounts as $discount) {
                // sequential percentage discount
                if ($discount->discountable_type == 'App\Models\PricePlan' && $discount->discountable_id != $item->pricePlan->id) {
                    continue;
                }

                $discountValue = $itemPrice * ($discount->discount / 100);
                $itemPrice = $itemPrice - $discountValue; // new base for next discount

                // accumulate into this discount (per item)
                $lineDiscount = $discountValue * $item->quantity;
                $lineTotal = $discountValue * $item->quantity * $item->contract_length;

                $discount->value += $lineDiscount;
                $discount->total += $lineTotal;
                $totalDiscount += $lineTotal;
            }

            $this->total += $item->total;
        }

        // persist discounts
        foreach ($discounts as $discount) {
            $discount->saveQuietly();
        }

        $this->total = $this->total - $totalDiscount;
        $this->total_discount = $totalDiscount;

        $margin_value = ($this->total) - $total_cost;

        $this->margin = $this->total ? ($margin_value / ($this->total) * 100) : 0;
        $this->commission_value = $margin_value * ($this->commission_rate / 100);
    }

    public static function resolveStatus(Quote $quote): string
    {
        $statuses_to_ignore = [
            QuoteStatusesEnum::PENDING->name,
            QuoteStatusesEnum::APPROVED->name,
            QuoteStatusesEnum::COMPLETED->name,
            QuoteStatusesEnum::SUBMITTED->name,
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
            QuoteStatusesEnum::SUBMITTED->name,
            QuoteStatusesEnum::EXPIRED->name,
        ];

        if (in_array($this->status, $block_statuses)) {
            return true;
        }

        return false;
    }
}

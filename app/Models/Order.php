<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        "account_id",
        "solution_type_id",
        "address_line1",
        "address_postcode",
        "tenant_id",
        "name"
    ];
    private static $solution_types = [
        "3" => 'Connectivity - Ethernet & Fibre',
        "4" => 'Mobile',
        // "1" => 'Connectivity - Ethernet Point to Point',
        // "2" => 'Connectivity - Fibre',
    ];

    protected $appends = ['status'];

    public function getStatusAttribute()
    {
        if ($this->items()->count()) {
            return 'QUOTE';
        }
        if (!empty($record->address_data)) {
            return 'PRICE';
        }

        return 'CONFIGURE';
    }

    public static function getSolutionTypes(): array
    {
        $option = app('currentTenant')->solutions ?? '';
        $solution_types = static::$solution_types;
        $allowed = explode(',', $option);
        $allowed = array_map('trim', $allowed);
        $tenant_solutions = array_filter($allowed);
        $solution_map = static::getSolutionMap();

        foreach ($solution_map as $slug => $id) {
            if (!empty($tenant_solutions) && !in_array($slug, $tenant_solutions)) {
                unset($solution_types[$id]);
            }
        }

        return $solution_types;
    }

    public static function getSolutionTypeName($id): string
    {
        return self::$solution_types[$id] ?? "Unknown";
    }

    public static function getSolutionMap()
    {
        $solution_map = [];

        foreach (static::$solution_types as $solution_id => $solution_name) {
            $solution_key = (string) Str::of($solution_name)->slug()->replace('-', '_')->upper();
            $solution_map[$solution_key] = $solution_id;
        }

        return $solution_map;
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
        static::addGlobalScope(new UserScope);
    }

    public function save(array $options = []): bool
    {
        if (empty($this->user_id)) {
            $this->user_id = auth()->user()->id;
        }

        return parent::save($options);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function totals()
    {
        $subtotal = 0;
        $subtotalOneoff = 0;

        foreach ($this->items as $item) {
            $lineTotal = (float) $item['quantity'] * (float) $item->data->priceMonthly;
            $lineTotalOneoff = (float) $item['quantity'] * (float) $item->data->priceInstall;
            $subtotal += $lineTotal;
            $subtotalOneoff += $lineTotalOneoff;
        }

        $totals['monthly_net'] = $subtotal;
        $totals['monthly_vat'] = $subtotal * 0.2;
        $totals['monthly_gross'] = $subtotal * 1.2;
        $totals['oneoff_net'] = $subtotalOneoff;
        $totals['oneoff_vat'] = $subtotalOneoff * 0.2;
        $totals['oneoff_gross'] = $subtotalOneoff * 1.2;

        return $totals;
    }

    public function status()
    {
        if ($this->items()->count()) {
            return 'QUOTE';
        } elseif (!empty($record->address_data)) {
            return 'PRICE';
        } else {
            return 'CONFIGURE';
        }
    }
}

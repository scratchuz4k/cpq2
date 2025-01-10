<?php

namespace App\Models;

use App\Models\PricePlan;
use App\Trait\SetTenantId;
use App\Helpers\Prices\PriceRules;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Price extends Model
{
    use HasFactory, SetTenantId;

    protected static PriceRules $priceRules;

    public function __construct()
    {
        parent::__construct();
        static::$priceRules = new PriceRules();
    }
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TenantScope);

        static::retrieved(function ($model) {
            //TODO: switch to a account saved in the user model
            $account = Account::find(request() ? request()->input("account_id", null) : null);
            static::$priceRules->find($model->product, $model->product->supplier, $account);
            $model->setAttribute('net_spa', static::$priceRules->apply((float)$model->net));
            $model->setAttribute('active_spas', static::$priceRules->getAppliedSpas());
            $model->calculate();
        });

        static::saving(function ($model) {
            unset($model->attributes['net_spa']);
            unset($model->attributes['active_spas']);
            $model->calculate();
        });

        static::creating(function (Model $model) {
            // Check if tenant_id is not set and the user is authenticated
            if (auth()->check() && !$model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
            $model->calculate();
        });
    }

    protected $fillable = [
        "code",
        "tax_rate",
        "net",
        "product_id",
        "tenant_id"
    ];


    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, "product_id", "id");
    }

    public function calculate()
    {
        $this->tax = $this->net * ($this->tax_rate / 100);
        $this->gross = $this->net_spa * (1 + ($this->tax_rate / 100));
    }
}

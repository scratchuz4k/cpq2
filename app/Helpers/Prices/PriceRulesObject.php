<?php

namespace App\Helpers\Prices;

use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PriceRulesObject
{
    public Collection $spas;

    public ?Product $product;

    public ?User $user;

    public ?Supplier $supplier;

    public ?Account $account;

    public Tenant $tenant;

    public function __construct(
        public ?Product $product = null,
        public ?User $user = null,
        public ?Supplier $supplier = null,
        public ?Account $account = null
    ) {
        $this->tenant = Auth::check() ? Auth::user()->currentTenant : Tenant::first();
        $this->spas = collect([]);
    }
}

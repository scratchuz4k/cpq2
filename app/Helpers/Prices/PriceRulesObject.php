<?php

namespace App\Helpers\Prices;

use App\Models\User;
use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Collection;

class PriceRulesObject
{
    public Collection $spas;
    public ?Product $product;
    public ?User $user;
    public ?Supplier $supplier;
    public ?Account $account;

    public function __construct(
        $product = null,
        $user = null,
        $supplier = null,
        $account = null
    ) {
        $this->product = $product;
        $this->user = $user;
        $this->supplier = $supplier;
        $this->account = $account;
        $this->spas = collect([]);
    }
}

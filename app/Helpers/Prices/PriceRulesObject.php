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
    public ?int $product_id;
    public ?int $user_id;
    public ?int $supplier_id;
    public ?int $account_id;

    public function __construct(
        $product_id = null,
        $user_id = null,
        $supplier_id = null,
        $account_id = null
    ) {
        $this->product_id = $product_id;
        $this->user_id = $user_id;
        $this->supplier_id = $supplier_id;
        $this->account_id = $account_id;
        $this->spas = collect([]);
    }
}

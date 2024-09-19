<?php

namespace App\Helpers\Prices;

use App\Models\User;
use App\Models\Price;
use Illuminate\Support\Facades\Auth;

class SpaFromAncestorsRule
{
    public function handle(Price $price, $next)
    {
        $user = User::ancestors()->where("id", Auth::user()->id)->first();
        if (!$user->parent) {
            next($price);
        }        
        
        next($price);
    }
}

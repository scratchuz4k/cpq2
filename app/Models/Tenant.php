<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class Tenant extends \Spatie\Multitenancy\Models\Tenant
{
    use HasFactory, HasApiTokens;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}

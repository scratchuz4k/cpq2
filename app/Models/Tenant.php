<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class Tenant extends \Spatie\Multitenancy\Models\Tenant
{
    use HasFactory, HasApiTokens;
    protected $fillable = ['name', 'domain', 'database', 'config_id'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function file(): HasOne
    {
        return $this->hasOne(Resource::class, 'id', 'config_id');
    }
}

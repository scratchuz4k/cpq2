<?php

namespace App\Models;

use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory, SetTenantId;

    protected $fillable = [
        'owner_id',
        'name',
    ];

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'group_account');
    }
}

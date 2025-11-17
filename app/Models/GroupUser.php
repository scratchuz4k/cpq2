<?php

namespace App\Models;

use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupUser extends Pivot
{
    use HasFactory, SetTenantId;

    protected $fillable = [
        'user_id',
        'group_id',
        'tenant_id',
    ];
}

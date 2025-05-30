<?php

namespace App\Models;

use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupAccount extends Pivot
{
    use HasFactory, SetTenantId;

    protected $fillable = [
        'account_id',
        'group_id',
        'tenant_id',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}

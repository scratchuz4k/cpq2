<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupAccount extends Model
{
    use HasFactory, SetTenantId;

    protected $table = 'group_account';

    protected $fillable = [
        'account_id',
        'group_id',
        'tenant_id',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}

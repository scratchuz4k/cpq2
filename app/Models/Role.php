<?php

namespace App\Models;

use App\Traits\RegisterActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Models\Role as ModelsRole;

class Role extends ModelsRole
{

    protected $fillable = [
        'name',
        'guard_name',
    ];


    public static function tree()
    {
        return static::with(implode('.', array_fill(0, 4, 'children')))->where('parent_id', null)->get();
    }
}
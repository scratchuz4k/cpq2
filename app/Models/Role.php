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

    /**
     * Get the parent user of this user.
     */
    public function parent()
    {
        return $this->hasOne(Role::class, 'id', 'parent_id');
    }

    /**
     * Get the children users of this user.
     */
    public function children()
    {
        return $this->hasMany(Role::class, 'parent_id', 'id');
    }

    public static function ancestors($id)
    {
        return static::with([implode('.', array_fill(0, 10, 'parent'))])->where('id', $id)->get();
    }

    public static function descendants($id)
    {
        return static::with([implode('.', array_fill(0, 10, 'children'))])->where('id', $id)->get();
    }
}

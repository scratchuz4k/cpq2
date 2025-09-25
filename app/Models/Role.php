<?php

namespace App\Models;

use App\Trait\SetTenantId;
use Spatie\Permission\Models\Role as ModelsRole;

class Role extends ModelsRole
{
    use SetTenantId;

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

    public static function descendants()
    {
        $temp_roles = auth()->user()->roles()->get();
        $roles = collect([]);
        do {
            $current = $temp_roles->pop();
            $roles->push($current);
            $temp_roles->push(...$current->children);
        } while (count($temp_roles));

        return $roles;
    }
}

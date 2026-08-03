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
        'parent_id',
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
        while (count($temp_roles)) {
            $current = $temp_roles->pop();
            $roles->push($current);
            $temp_roles->push(...$current->children);
        }

        return $roles;
    }

    /**
     * Get the ids of every descendant of this role (children, grandchildren, ...).
     * Used to prevent circular parent assignments.
     */
    public function getDescendantIds(): array
    {
        $ids = [];
        $queue = $this->children()->pluck('id')->all();

        while ($queue) {
            $id = array_shift($queue);

            if (in_array($id, $ids, true)) {
                continue;
            }

            $ids[] = $id;
            $queue = array_merge($queue, static::where('parent_id', $id)->pluck('id')->all());
        }

        return $ids;
    }
}

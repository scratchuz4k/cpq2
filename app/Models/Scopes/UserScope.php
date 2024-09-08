<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $descendantIds = User::descendants(auth()->id());
        $descendantIds[] = auth()->id(); // Include the current user's id
        $builder->whereIn('user_id', $descendantIds);
    }
}

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
        $userDescedents = User::descendants(auth()->id());
        $ids = collect([]);
        do {
            $current = $userDescedents->pop();
            $ids->push($current->id);
            $userDescedents->push(...$current->children);
        } while (count($userDescedents));

        $builder->whereIn('user_id', $ids);
    }
}

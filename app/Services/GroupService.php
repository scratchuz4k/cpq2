<?php

namespace App\Services;

use App\Models\Group;
use Illuminate\Support\Facades\Auth;

class GroupService
{
    public function store(array $array): Group
    {
        $group = Group::create([
            'owner_id' => Auth::id(),
            ...$array,
        ]);

        return $group;
    }

    public function update(Group $group, array $array): Group
    {
        $group->update($array);

        return $group;
    }
}

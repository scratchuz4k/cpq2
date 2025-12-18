<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function export(User $user): bool
    {
        if ($user->can('viewAny export')) {
            return true;
        }

        return false;
    }
}

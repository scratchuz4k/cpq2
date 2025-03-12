<?php

namespace App\Trait;

trait IsActive
{
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

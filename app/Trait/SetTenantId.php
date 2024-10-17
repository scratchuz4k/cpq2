<?php

namespace App\Trait;

use Illuminate\Database\Eloquent\Model;

trait SetTenantId
{
    public static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            // Check if tenant_id is not set and the user is authenticated
            if (auth()->check() && !$model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}

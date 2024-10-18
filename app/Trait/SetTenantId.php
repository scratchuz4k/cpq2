<?php

namespace App\Trait;

use Illuminate\Database\Eloquent\Model;

trait SetTenantId
{
    // Define default hidden attributes in the trait
    protected $defaultHidden = ['tenant_id'];

    /**
     * Get the hidden attributes, merging trait's default and model-specific ones.
     *
     * @return array
     */
    public function getHidden()
    {
        // Check if the model has overridden the $hidden property
        if (isset($this->hidden)) {
            // Merge model's $hidden with the default hidden attributes from the trait
            return array_merge($this->defaultHidden, $this->hidden);
        }

        // If not overridden, return the default hidden attributes
        return $this->defaultHidden;
    }
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

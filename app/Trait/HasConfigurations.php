<?php

namespace App\Trait;

use App\Models\Configuration;

trait HasConfigurations
{
    public function configurations()
    {
        return $this->morphMany(Configuration::class, 'configurable');
    }

    public function syncConfigurations(array $configurations)
    {
        $oldKeys = $this->configurations;

        foreach ($configurations as $key => $value) {
            $item = $this->configurations()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
            $oldKeys = $oldKeys->reject(function ($old) use ($item) {
                return $old->id === $item->id;
            });
        }

        foreach ($oldKeys as $old) {
            $old->delete();
        }
    }

    public function getConfigsAttribute()
    {
        $res = [];

        foreach ($this->configurations as $configuration) {
            $res[$configuration['key']] = $configuration['value'];
        }

        return $res;
    }

    public function getConfiguration(string $key, mixed $defaultValue = null): mixed
    {
        $res = $this->configurations->where('key', $key)->first();

        return $res ? $res->value : ($defaultValue != null ? $defaultValue : null);
    }
}

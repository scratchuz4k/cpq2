<?php

namespace App\Trait;

use App\Models\Activity;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait RegisterActivity
{
    public function activity()
    {
        return $this->morphMany(Activity::class, 'activitable');
    }

    protected static function eventsToBeRecorded()
    {
        $events = collect([
            'created',
            'updated',
            'deleted',
        ]);

        return $events;
    }

    public function getValueToHistoryValue()
    {
        return $this->id;
    }

    public function getValue($model)
    {
        return $model->getValueToHistoryValue();
    }

    public function getDescriptionForEvent(string $eventName)
    {
        return $eventName;
    }

    protected static function bootRegisterActivity()
    {
        static::eventsToBeRecorded()->each(function ($eventName) {
            return static::$eventName(function (Model $model) use ($eventName) {

                if (app()->runningInConsole()) {
                    $user = User::where('email', 'system@cpq.cloudmarket.com')->first();
                    $tenant = Tenant::current();
                } else {
                    $user = auth()->user();
                    $tenant = $user->currentTenant;
                }

                if (! array_key_exists('log_system_is_active', $tenant->configs) || $tenant->configs['log_system_is_active'] == '0') {
                    return;
                }

                $description = $model->getDescriptionForEvent($eventName);

                if (empty($description)) {
                    return;
                }

                $data = [];
                $changes = $model->getDirty();
                foreach ($changes as $field => $newValue) {
                    $originalValue = $model->getOriginal($field);
                    if (isset($model->activity_field) && ! in_array($field, $model->activity_field)) {
                        continue;
                    }
                    $data[] = ['field' => $field, 'from' => $originalValue, 'to' => $newValue];
                }

                Activity::create([
                    'operation' => $description,
                    'activitable_id' => $model->getValue($model),
                    'activitable_type' => $model::class,
                    'data' => $data,
                    'user_id' => $user->id ?? null,
                    'tenant_id' => $tenant->id ?? null,
                ]);
            });
        });
    }
}

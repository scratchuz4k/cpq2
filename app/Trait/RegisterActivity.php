<?php

namespace App\Trait;

use App\Models\Activity;
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
                $description = $model->getDescriptionForEvent($eventName);

                if (empty($description)) {
                    return;
                }

                $data = [];
                $changes = $model->getDirty();
                foreach ($changes as $field => $newValue) {
                    $originalValue = $model->getOriginal($field);
                    if (isset($model->activity_field) &&  !in_array($field, $model->activity_field)) {
                        continue;
                    }
                    $data[] = "Field '$field' changed from '$originalValue' to '$newValue'.";
                }


                Activity::create([
                    'operation' => $description,
                    'activitable_id' => $model->getValue($model),
                    'activitable_type' => $model::class,
                    'data' => $data,
                    'user_id' => auth()->user()->id ?? null,
                    'tenant_id' => auth()->user()->tenant_id ?? null,
                ]);
            });
        });
    }
}

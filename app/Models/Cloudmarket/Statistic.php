<?php

namespace App\Models\Cloudmarket;

use MongoDB\Laravel\Eloquent\Model;

class Statistic extends Model
{
    protected $connection = 'mongodb_cloudmarket';

    protected $collection = 'statistics';

    protected $guarded = [];

    protected $casts = [
        'requestDate' => 'datetime',
        'providers' => 'array',
    ];
}

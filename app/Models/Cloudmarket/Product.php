<?php

namespace App\Models\Cloudmarket;

use Illuminate\Support\Facades\Cache;
use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mongodb_cloudmarket'; // Use the MongoDB connection

    protected $collection = 'products'; // Specify the collection name

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $guarded = [];

    public static function write(string $filename, array $data, bool $wating_task, $finished_at, $created_at, $filters = [], $errors = [])
    {
        $content = [
            'data' => $data,
            'total' => count($data),
            'wating_task' => $wating_task,
            'updated_at' => now(),
            'created_at' => $created_at,
            'finished_at' => $finished_at,
            'filters' => $filters,
            'errors' => $errors,
        ];

        Product::updateOrCreate([
            'key' => $filename,
        ], $content);

        Cache::put($filename, json_encode(['updated_at' => now()]), 86400);
    }
}

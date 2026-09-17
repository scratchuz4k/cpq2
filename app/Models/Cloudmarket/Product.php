<?php

namespace App\Models\Cloudmarket;

use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mongodb_cloudmarket'; // Use the MongoDB connection

    protected $collection = 'products'; // Specify the collection name

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $guarded = [];

    public static function write(string $key, string $filename, array $data, bool $wating_task, $finished_at, $created_at, $filters = [], $errors = [], $expire_at = null, $options = [])
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
            'expire_at' => $expire_at,
            'options' => $options,
        ];

        Product::updateOrCreate([
            'key' => $key,
            'filename' => $filename,
        ], $content);
    }

    public static function addOptions(string $key, string $filename, array $data)
    {
        Product::updateOrCreate([
            'key' => $key,
            'filename' => $filename,
        ], ['options' => $data]);
    }
}

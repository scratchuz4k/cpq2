<?php

namespace App\Models\Cloudmarket;

use MongoDB\Laravel\Eloquent\Model;

class Address extends Model
{
    protected $connection = 'mongodb_cloudmarket'; // Use the MongoDB connection

    protected $collection = 'addresses'; // Specify the collection name

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $guarded = [];
}

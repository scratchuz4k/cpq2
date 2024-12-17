<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolutionType extends Model
{
    use HasFactory;

    private static $solution_types = [
        "3" => 'Connectivity - Ethernet & Fibre',
        "4" => 'Mobile',
        // "1" => 'Connectivity - Ethernet Point to Point',
        // "2" => 'Connectivity - Fibre',
    ];

}

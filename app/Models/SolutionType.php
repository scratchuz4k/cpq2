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

    public static function getSolutionTypeName($id): string
    {
        return self::$solution_types[$id] ?? "Unknown";
    }

    public static function getSolutionMap()
    {
        $solution_map = [];

        foreach (static::$solution_types as $solution_id => $solution_name) {
            $solution_key = (string) Str::of($solution_name)->slug()->replace('-', '_')->upper();
            $solution_map[$solution_key] = $solution_id;
        }

        return $solution_map;
    }
}

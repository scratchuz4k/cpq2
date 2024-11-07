<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaRule extends Model
{
    protected $table = "spa_rules";
    protected $fillable = ["code", "class"];
}

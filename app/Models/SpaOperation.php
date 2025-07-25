<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpaOperation extends Model
{
    protected $table = 'spa_operations';

    protected $fillable = ['code', 'class'];
}

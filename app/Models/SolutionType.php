<?php

namespace App\Models;

use App\Models\Table;
use App\Trait\HasConfigurations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SolutionType extends Model
{
    use HasFactory, HasConfigurations;

    protected $fillable = ['code'];

}

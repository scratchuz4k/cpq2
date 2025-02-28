<?php

namespace App\Models;

use App\Trait\HasConfigurations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SolutionType extends Model
{
    use HasFactory, HasConfigurations;

    protected $fillable = ["code", "name"];

    public function tables(): MorphMany
    {
        return $this->morphMany(Table::class, 'tableable');
    }
}

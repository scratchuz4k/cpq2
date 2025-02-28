<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolutionTypeTenant extends Model
{
    protected $table = 'solution_type_tenant';
    protected $fillable = ['solution_type_id', 'tenant_id'];

    public function solutionType()
    {
        return $this->belongsTo(SolutionType::class, 'solution_type_id');
    }

}


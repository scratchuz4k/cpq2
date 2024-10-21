<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Token extends SanctumPersonalAccessToken
{
    use HasFactory;

    protected $table = 'personal_access_tokens';

}

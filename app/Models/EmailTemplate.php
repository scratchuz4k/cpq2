<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    protected $fillable = [
        'description',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
    ];

    public function tokens(): HasMany
    {
        return $this->hasMany(EmailToken::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(EmailHistory::class);
    }
}

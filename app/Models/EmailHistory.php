<?php

namespace App\Models;

use App\Trait\SetTenantId;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;

class EmailHistory extends Model
{
    
    use  SetTenantId;
    protected $fillable = [
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'user_id',
        'email_template_id',
    ];
    
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function email()
    {
        return $this->belongsTo(EmailTemplate::class);
    }
}

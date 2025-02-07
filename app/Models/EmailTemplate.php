<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    protected static function booted(): void
    {

        static::retrieved(function ($model) {
            //TODO: switch to a account saved in the user model
            if (!Auth::check()) {
                return;
            }

            $email = $model->tenants()->wherePivot('tenant_id', Auth::user()->tenant_id)->first();

            if ($email) {
                $model->setAttribute('to', $email->pivot->to);
                $model->setAttribute('cc', $email->pivot->cc);
                $model->setAttribute('bcc', $email->pivot->bcc);
                $model->setAttribute('subject', $email->pivot->subject);
                $model->setAttribute('body', $email->pivot->body);
            }
        });
    }


    public function tokens(): HasMany
    {
        return $this->hasMany(EmailToken::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(EmailHistory::class);
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'email_templates_tenant', 'email_template_id')->withPivot('to', 'cc', 'bcc', 'subject', 'body');
    }
}

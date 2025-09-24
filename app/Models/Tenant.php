<?php

namespace App\Models;

use App\Trait\HasConfigurations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class Tenant extends \Spatie\Multitenancy\Models\Tenant
{
    use HasApiTokens, HasConfigurations, HasFactory;

    // ...
    public static function boot()
    {
        parent::boot();

        // here assign this team to a global user with global default role
        self::created(function ($model) {
            // temporary: get session team_id for restore at end
            $session_team_id = getPermissionsTeamId();
            // set actual new team_id to package instance
            setPermissionsTeamId($model);
            // get the admin user and assign roles/permissions on new team model
            $mail = env('SUPERUSER_EMAIL', 'super@example.com');
            User::where('email', $mail)->first()->assignRole('Super Admin');
            // restore session team_id to package instance using temporary value stored above
            setPermissionsTeamId($session_team_id);
        });
    }

    protected $fillable = ['name', 'domain', 'database', 'sso_id', 'logo_id'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'sso_id', 'id');
    }

    public function logo(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'logo_id', 'id');
    }

    public function solutionTypes(): BelongsToMany
    {
        return $this
            ->belongsToMany(SolutionType::class, 'solution_type_tenant', 'tenant_id', 'solution_type_id')
            ->withPivot('estimated_time', 'is_active')
            ->withTimestamps();
    }

    public function emails(): BelongsToMany
    {
        return $this->belongsToMany(EmailTemplate::class, 'email_templates_tenant', 'tenant_id');
    }
}

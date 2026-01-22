<?php

namespace App\Models;

use App\Trait\HasConfigurations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;
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
            setPermissionsTeamId($model->id);
            // get the admin user and assign roles/permissions on new team model
            $admin = User::where('email', config('cpq.superuser.email'))->first();
            $system = User::where('email', 'system@cpq.cloudmarket.com')->first();
            $role = Role::withoutGlobalScopes()->where('name', 'Super Admin')->first();

            if (Auth::check()) {
                Auth::user()->update(['tenant_id' => $model->id]);
                $model->makeCurrent();
            }

            $admin->tenants()->attach($model->id);
            $admin->roles()->attach($role->id, ['tenant_id' => $model->id]);

            $system->tenants()->attach($model->id);
            $system->roles()->attach($role->id, ['tenant_id' => $model->id]);

            $role = Role::firstOrCreate(
                [
                    'name' => 'Company Admin',
                    'tenant_id' => $model->id,
                    'guard_name' => 'web',
                ],
                [
                    'guard_name' => 'web',
                ]
            );
            $role->syncPermissions(Permission::all());

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

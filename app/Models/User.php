<?php

namespace App\Models;

use App\Trait\SetTenantId;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SetTenantId;

    public static $default_role = 'sales rep';

    public static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            // Check if tenant_id is not set and the user is authenticated
            if (auth()->check() && ! $model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }

            $user = Auth::user();
            if ($user && $user->group()) {
                try {
                    $temp = [];
                    $temp[$user->group()->id] = ['is_default' => true];
                    $model->groups()->sync($temp);
                } catch (Exception $e) {
                    if (! env('APP_DEBUG')) {
                        Log::error($e->getMessage());
                    }
                }
            }
        });

    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'sso', // Add sso to fillable attributes
        'commission_rate',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'sso' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the children users of this user.
     */
    public function spas()
    {
        return $this->hasMany(Spa::class, 'user_id', 'id');
    }

    /**
     * Get the group of this user.
     */
    public function group()
    {
        return $this->groups()->where('is_default', true)->first();
    }

    /**
     * Get the group of this user.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class);
    }

    public function currentTenant(): HasOne
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Override createToken method from Sanctum
     */
    public function createTokenWithTenant(string $name, int $tenantId, bool $is_active = true, array $abilities = ['*'])
    {
        $plainTextToken = $this->generateTokenString();
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'tenant_id' => $tenantId,
            'is_active' => $is_active,
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    /**
     * Override updateToken method from Sanctum
     */
    public function updateTokenWithTenant(Token $token, bool $is_active)
    {
        $token->update([
            'is_active' => $is_active,
        ]);

        return $token;
    }

    public static function getUserWithRoles(Collection $users)
    {
        return $users->map(function ($user) {
            $roleName = $user->roles->pluck('name')->join(', ');
            $user->description = "{$user->name} ({$roleName})";

            return $user;
        });
    }

    public static function getChildAndOrphansUser(): Builder
    {
        $query = User::query();
        $superUsers = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['Super Admin']))->get();
        $query = $query->whereNotIn('id', $superUsers->pluck('id'));

        $role = Auth::user()->roles()->first();

        if ($role && ! $role->parent_id) {
            return $query;
        }

        $roles = Role::descendants();

        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->whereIn('id', $roles->pluck('id'));
        });

    }
}

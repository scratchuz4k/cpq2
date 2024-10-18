<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Spa;
use App\Trait\SetTenantId;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Scopes\TenantScope;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SetTenantId;

    public static $default_role = 'sales rep';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'parent_id', // Add parent_id to fillable attributes
        'tenant_id',
        'sso', // Add sso to fillable attributes
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
    ];


    /**
     * Get the children users of this user.
     */
    public function spas()
    {
        return $this->hasMany(Spa::class, 'user_id', 'id');
    }

    /**
     * Get the parent user of this user.
     */
    public function parent()
    {
        return $this->hasOne(User::class, 'id', 'parent_id');
    }

    /**
     * Get the children users of this user.
     */
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id', 'id');
    }
    public function currentTenant(): HasOne
    {
        return $this->hasOne(Tenant::class, 'id', 'tenant_id');
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class);
    }

    public static function ancestors($id)
    {
        return static::with([implode('.', array_fill(0, 10, 'parent'))])->where('id', $id)->get();
    }

    public static function descendants($id)
    {
        return static::with([implode('.', array_fill(0, 10, 'children'))])->where('id', $id)->get();
    }
}

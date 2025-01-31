<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Trait\SetTenantId;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SetTenantId;

    public static $default_role = 'sales rep';

    public static function boot()
    {
        parent::boot();
        
        static::creating(function (Model $model) {
            // Check if tenant_id is not set and the user is authenticated
            if (auth()->check() && !$model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });

        // Update parent when a child is created
        static::created(function ($child) {
            if ($child->parent_id) {
                $parent = User::find($child->parent_id);
                if ($parent) {
                    $parent->has_children = true;
                    $parent->save();
                }
            }
        });

        // Update parent when a child is deleted
        static::deleting(function ($child) {
            if ($child->parent_id) {
                $parent = User::find($child->parent_id);
                if ($parent) {
                    $parent->has_children = $parent->children()->exists();
                    $parent->save();
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
        'parent_id', // Add parent_id to fillable attributes
        'tenant_id',
        'sso', // Add sso to fillable attributes
        'commission_rate',
        'has_children',
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

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public static function ancestors()
    {
        $users = collect([]);
        if (is_null(Auth::user())) {
            return $users;
        }
        $userAscendents = collect([Auth::user()]);
        do {
            $current = $userAscendents->pop();
            $users->push($current);
            if (! $current->parent) {
                break;
            }
            $userAscendents->push($current->parent);
        } while (count($userAscendents));

        return $users;
    }

    public static function descendants()
    {
        $users = collect([]);
        if (is_null(Auth::user())) {
            return $users;
        }
        $userDescedents = collect([Auth::user()]);
        do {
            $current = $userDescedents->pop();
            $users->push($current);
            $userDescedents->push(...$current->children);
        } while (count($userDescedents));

        return $users;
    }
}

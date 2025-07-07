<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Trait\SetTenantId;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
            if (auth()->check() && !$model->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }

            $user = Auth::user();
            if ($user && $user->group) {
                try {
                    $model->group_id = $user->group->id;
                } catch (Exception $e) {
                    if (!env('APP_DEBUG')) {
                        Log::error($e->getMessage());
                    }
                }
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

        static::updating(function ($child) {
            // Check if parent_id is being changed
            if ($child->isDirty('parent_id')) {
                $oldParentId = $child->getOriginal('parent_id');
                $newParentId = $child->parent_id;
                // Update old parent
                if ($oldParentId) {
                    $oldParent = User::find($oldParentId);
                    if ($oldParent) {
                        $oldParent->has_children = $oldParent->children()->where('id', '!=', $child->id)->exists();
                        $oldParent->save();
                    }
                }
                // Update new parent
                if ($newParentId) {
                    $newParent = User::find($newParentId);
                    if ($newParent) {
                        $newParent->has_children = true;
                        $newParent->save();
                    }
                }
            }
        });

        // Update parent when a child is deleted
        static::deleting(function ($child) {
            if ($child->parent_id) {
                $parent = User::find($child->parent_id);
                if ($parent) {
                    $parent->has_children = $parent->children()->where('id', '!=', $child->id)->exists();
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
        'group_id',
        'sso', // Add sso to fillable attributes
        'commission_rate',
        'has_children',
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
     * Get the parent user of this user.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
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

    public static function ancestors(?User $user = null)
    {
        $currentUser = is_null($user) ? Auth::user() : $user;
        $users = collect([]);
        if (is_null($currentUser)) {
            return $users;
        }
        $userAscendents = collect([$currentUser]);
        do {
            $current = $userAscendents->pop();
            $users->push($current);
            if (!$current->parent) {
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

    /**
     * Override createToken method from Sanctum
     */
    public function createTokenWithTenant(string $name, int $tenantId, array $abilities = ['*'])
    {
        $plainTextToken = $this->generateTokenString();
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'tenant_id' => $tenantId,
        ]);

        return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }
}

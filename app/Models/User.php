<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Spa;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

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
        return $this->hasMany( Spa::class, 'user_id', 'id');
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

    // public static function ancestors(int $user_id): array
    // {
    //     $ancestor_ids = [];

    //     $user_ids = TenantUser::select('user_id')->get()->pluck('user_id')->toArray();

    //     if ($user_ids) {
    //         $sql = "with recursive cte as (
    //             select id, parent_id, 1 lvl from users
    //             where id in (" . implode(',', $user_ids) . ")
    //             union all
    //             select c.id, t.parent_id, lvl + 1
    //             from cte c
    //             inner join users t on t.id = c.parent_id
    //         )
    //         select id, group_concat(parent_id order by lvl) parents
    //         from cte
    //         where id = " . $user_id . "
    //         group by id";
    //         $ancestor_ids = array_filter(explode(',', DB::select($sql)[0]?->parents ?? ''));
    //         if (!empty($ancestor_ids)) {
    //             $ancestor_ids = array_map(function ($value) {
    //                 return (int) $value;
    //             }, $ancestor_ids);
    //         }
    //     }

    //     return $ancestor_ids ?? [];
    // }


    // public static function descendants(int $user_id): array
    // {
    //     $descendant_ids = [];

    //     $user_ids = TenantUser::select('user_id')->get()->pluck('user_id')->toArray();

    //     if ($user_ids) {
    //         $sql = "with recursive cte as (
    //             select id, parent_id, 1 as level from users
    //             where parent_id = " . $user_id . "
    //             union all
    //             select u.id, u.parent_id, c.level + 1
    //             from users u
    //             inner join cte c on u.parent_id = c.id
    //         )
    //         select id, level from cte";

    //         // Execute the SQL query
    //         $results = DB::select($sql);

    //         // Extract the descendant IDs and their levels from the result
    //         foreach ($results as $result) {
    //             // $descendant_ids[] = ['id' => $result->id, 'level' => $result->level];
    //             $descendant_ids[] = (int) $result->id;
    //         }
    //     }

    //     return $descendant_ids ?? [];
    // }
}

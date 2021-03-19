<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Services\SlugService;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Sluggable;

    const STATUS_ACTIVE = 1;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
            ]
        ];
    }

    public static function create($data)
    {
        $user = new static;
        $data['password'] = Hash::make($data['password']);
        $user->fill($data);
        $user->save();

        return $user;
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function tasks()
    {
        return $this->morphToMany(Task::class, 'taskable');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions');
    }
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    public function hasRole(string $role_slug)
    {
        return $this->roles->contains($role_slug);
    }

    /**
     * @param string[]|string $permission 
     */
    public function hasPermission($permission)
    {
        if ( is_array($permission) ) {
            return $this->hasPermissionFromList($permission);
        }

        return $this->hasPermissionThroughRole($permission);
    }

    protected function hasPermissionFromList(array $permissions) 
    {
        $permissions = Permission::whereIn('slug', $permissions)->get();
        
        foreach ($permissions as $permission) {
            if ( $this->permissions->contains($permission) ) return true;

            if ( $this->hasPermissionThroughRole($permission) ) return true;
        }

        return false;
    }

    protected function hasPermissionThroughRole($permission)
    {
        if ( !$permission instanceof Permission ) {
            $permission = Permission::where('slug', '=', $permission)->get();
        }

        foreach ( $permission->roles as $role ) {
            if ( $this->roles->contains($role) ) return true;
        }

        return false;
    }

}

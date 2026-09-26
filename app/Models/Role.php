<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'display_name',
        'guard_name',
        'description',
    ];

    protected $casts = [
        'display_name' => 'array',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }

    public function givePermissionTo(Permission|string $permission): void
    {
        $permissionModel = is_string($permission)
            ? Permission::where('name', $permission)->firstOrFail()
            : $permission;

        $this->permissions()->syncWithoutDetaching([$permissionModel->id]);
    }

    public function hasPermissionTo(string $permissionName): bool
    {
        return $this->permissions->contains('name', $permissionName);
    }
}

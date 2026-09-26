<?php

namespace App\Models;

use App\Enums\PermissionGroup;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'group',
        'display_name',
        'guard_name',
    ];

    protected $casts = [
        'group' => PermissionGroup::class,
        'display_name' => 'array',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }
}

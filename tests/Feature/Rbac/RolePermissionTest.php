<?php

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

test('super admin has all permissions implicitly', function () {
    $superAdminRole = Role::create([
        'name' => RoleName::SUPER_ADMIN->value,
        'display_name' => ['en' => 'Super Admin'],
        'guard_name' => 'web',
    ]);

    $user = User::create([
        'name' => 'Super Admin',
        'email' => 'super@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);
    $user->assignRole($superAdminRole);

    expect($user->hasRole(RoleName::SUPER_ADMIN))->toBeTrue();
    expect($user->hasPermission('any.random.permission'))->toBeTrue();
});

test('roles enforce granular permissions correctly', function () {
    $adminRole = Role::create([
        'name' => RoleName::ADMIN->value,
        'display_name' => ['en' => 'Admin'],
        'guard_name' => 'web',
    ]);

    $viewUsersPerm = Permission::create([
        'name' => 'users.view',
        'group' => 'users',
        'display_name' => ['en' => 'View Users'],
    ]);

    $adminRole->givePermissionTo($viewUsersPerm);

    $user = User::create([
        'name' => 'Admin User',
        'email' => 'admin_user@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);
    $user->assignRole($adminRole);

    expect($user->hasPermission('users.view'))->toBeTrue();
    expect($user->hasPermission('users.delete'))->toBeFalse();
});

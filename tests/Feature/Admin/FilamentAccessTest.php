<?php

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    Role::firstOrCreate(
        ['name' => RoleName::SUPER_ADMIN->value],
        ['display_name' => ['en' => 'Super Admin'], 'guard_name' => 'web']
    );

    Role::firstOrCreate(
        ['name' => RoleName::ADMIN->value],
        ['display_name' => ['en' => 'Admin'], 'guard_name' => 'web']
    );

    Role::firstOrCreate(
        ['name' => RoleName::SUPPORT->value],
        ['display_name' => ['en' => 'Support'], 'guard_name' => 'web']
    );

    Role::firstOrCreate(
        ['name' => RoleName::CUSTOMER->value],
        ['display_name' => ['en' => 'Customer'], 'guard_name' => 'web']
    );
});

test('customer cannot access filament admin panel', function () {
    $customer = User::create([
        'name' => 'Customer Joe',
        'email' => 'joe@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);
    $customer->assignRole(RoleName::CUSTOMER);

    $response = $this->actingAs($customer)->get('/admin');

    $response->assertStatus(403);
});

test('admin can access filament admin panel', function () {
    $admin = User::create([
        'name' => 'Admin Jane',
        'email' => 'jane@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);
    $admin->assignRole(RoleName::ADMIN);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(302);
});

test('suspended admin cannot access filament admin panel', function () {
    $admin = User::create([
        'name' => 'Suspended Admin',
        'email' => 'bad_admin@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::SUSPENDED,
    ]);
    $admin->assignRole(RoleName::ADMIN);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(403);
});

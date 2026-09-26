<?php

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;

beforeEach(function () {
    Role::create([
        'name' => RoleName::CUSTOMER->value,
        'display_name' => ['en' => 'Customer'],
        'guard_name' => 'web',
    ]);
});

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register and are assigned customer role', function () {
    $response = $this->post('/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+212600112233',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->account_status)->toBe(AccountStatus::ACTIVE);
    expect($user->hasRole(RoleName::CUSTOMER))->toBeTrue();
});

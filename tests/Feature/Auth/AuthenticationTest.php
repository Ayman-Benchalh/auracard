<?php

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::create([
        'name' => 'Alice Test',
        'email' => 'alice@example.com',
        'password' => Hash::make('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);

    $response = $this->post('/login', [
        'email' => 'alice@example.com',
        'password' => 'password123',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');
});

test('users can not authenticate with invalid password', function () {
    $user = User::create([
        'name' => 'Alice Test',
        'email' => 'alice@example.com',
        'password' => Hash::make('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);

    $this->post('/login', [
        'email' => 'alice@example.com',
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('suspended users cannot authenticate', function () {
    $user = User::create([
        'name' => 'Suspended User',
        'email' => 'suspended@example.com',
        'password' => Hash::make('password123'),
        'account_status' => AccountStatus::SUSPENDED,
    ]);

    $response = $this->post('/login', [
        'email' => 'suspended@example.com',
        'password' => 'password123',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::create([
        'name' => 'Alice Test',
        'email' => 'alice@example.com',
        'password' => Hash::make('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

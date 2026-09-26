<?php

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Support\Facades\Password;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    $user = User::create([
        'name' => 'Charlie Test',
        'email' => 'charlie@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);

    $response = $this->post('/forgot-password', [
        'email' => 'charlie@example.com',
    ]);

    $response->assertSessionHas('status');
});

test('reset password screen can be rendered', function () {
    $user = User::create([
        'name' => 'Charlie Test',
        'email' => 'charlie@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);

    $token = Password::createToken($user);

    $response = $this->get('/reset-password/'.$token.'?email=charlie@example.com');

    $response->assertStatus(200);
});

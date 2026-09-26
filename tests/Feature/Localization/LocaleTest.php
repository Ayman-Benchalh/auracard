<?php

use App\Enums\AccountStatus;
use App\Models\User;

test('guest can switch locale and persist in session', function () {
    $response = $this->get('/locale/ar');

    $response->assertSessionHas('locale', 'ar');
});

test('authenticated user locale preference updates profile', function () {
    $user = User::create([
        'name' => 'Locale Tester',
        'email' => 'locale@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
        'locale' => 'fr',
    ]);

    $this->actingAs($user)->get('/locale/ar');

    expect($user->fresh()->locale)->toBe('ar');
});

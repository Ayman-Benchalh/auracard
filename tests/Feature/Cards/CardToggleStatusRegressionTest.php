<?php

use App\Actions\Cards\GenerateNfcCardAction;
use App\Enums\AccountStatus;
use App\Enums\CardStatus;
use App\Models\NfcCard;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::create([
        'name' => 'Card Owner',
        'email' => 'owner_toggle@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
        'email_verified_at' => now(),
    ]);

    $this->otherCustomer = User::create([
        'name' => 'Other Customer',
        'email' => 'other_customer@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
        'email_verified_at' => now(),
    ]);

    $generator = app(GenerateNfcCardAction::class);
    $this->card = $generator->execute(null, 'standard', CardStatus::ACTIVE)->card;
    $this->card->update(['user_id' => $this->owner->id]);
});

test('customer can pause and reactivate their own active card', function () {
    // 1. Pause active card
    $response = $this->actingAs($this->owner)->patch("/cards/{$this->card->id}/toggle-status");
    $response->assertSessionHas('success');
    expect($this->card->fresh()->status)->toBe(CardStatus::SUSPENDED);

    // 2. Reactivate suspended card
    $response2 = $this->actingAs($this->owner)->patch("/cards/{$this->card->id}/toggle-status");
    $response2->assertSessionHas('success');
    expect($this->card->fresh()->status)->toBe(CardStatus::ACTIVE);
});

test('customer cannot toggle status of another customers card', function () {
    $response = $this->actingAs($this->otherCustomer)->patch("/cards/{$this->card->id}/toggle-status");
    $response->assertStatus(403);
    expect($this->card->fresh()->status)->toBe(CardStatus::ACTIVE);
});

test('toggle status never allows modifying arbitrary card statuses', function () {
    $invalidStatuses = [
        CardStatus::LOST,
        CardStatus::REPLACED,
        CardStatus::DISABLED,
        CardStatus::INVENTORY,
        CardStatus::SOLD,
    ];

    foreach ($invalidStatuses as $status) {
        $this->card->update(['status' => $status]);

        $response = $this->actingAs($this->owner)->patch("/cards/{$this->card->id}/toggle-status");
        $response->assertSessionHas('error');

        // Status must remain completely unchanged
        expect($this->card->fresh()->status)->toBe($status);
    }
});

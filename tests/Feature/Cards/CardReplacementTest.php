<?php

use App\Actions\Cards\GenerateNfcCardAction;
use App\Actions\Cards\ReplaceCardAction;
use App\Enums\AccountStatus;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Models\CardEntitlement;
use App\Models\CardTransfer;
use App\Models\NfcCard;
use App\Models\User;

beforeEach(function () {
    $this->generator = app(GenerateNfcCardAction::class);
    $this->replaceAction = app(ReplaceCardAction::class);

    $this->admin = User::create([
        'name' => 'Admin Replacer',
        'email' => 'admin_rep@auracard.ma',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);

    $this->customer = User::create([
        'name' => 'Customer Alice',
        'email' => 'alice@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
        'email_verified_at' => now(),
    ]);

    // Generate Card A and link to Alice with entitlement
    $resultA = $this->generator->execute(null, 'matte_black', CardStatus::ACTIVE);
    $this->cardA = $resultA->card;
    $this->cardA->update([
        'user_id' => $this->customer->id,
        'status' => CardStatus::ACTIVE,
        'claimed_at' => now()->subMonths(1),
        'activated_at' => now()->subMonths(1),
    ]);

    $this->entitlement = CardEntitlement::create([
        'source_nfc_card_id' => $this->cardA->id,
        'current_nfc_card_id' => $this->cardA->id,
        'user_id' => $this->customer->id,
        'initial_credits_amount' => 2,
        'is_initial_credits_granted' => false,
        'status' => CardEntitlementStatus::ACTIVE,
    ]);
});

test('replacing lost Card A with Card B updates current card while preserving source entitlement', function () {
    // Generate fresh Card B (Inventory)
    $resultB = $this->generator->execute(null, 'matte_black', CardStatus::INVENTORY);
    $cardB = $resultB->card;

    // Execute replacement
    $replacedCard = $this->replaceAction->execute(
        oldCard: $this->cardA,
        newCard: $cardB,
        authorizedUser: $this->admin,
        reason: 'Physical card lost by customer'
    );

    $this->cardA->refresh();
    $cardB->refresh();
    $this->entitlement->refresh();

    // 1. Card A is marked replaced
    expect($this->cardA->status)->toBe(CardStatus::REPLACED)
        ->and($this->cardA->replaced_by_card_id)->toBe($cardB->id);

    // 2. Card B is active and owned by customer
    expect($cardB->status)->toBe(CardStatus::ACTIVE)
        ->and($cardB->user_id)->toBe($this->customer->id);

    // 3. Entitlement preserves immutable source card and updates current card
    expect($this->entitlement->source_nfc_card_id)->toBe($this->cardA->id)
        ->and($this->entitlement->current_nfc_card_id)->toBe($cardB->id)
        ->and($this->entitlement->is_initial_credits_granted)->toBeFalse()
        ->and($this->entitlement->initial_credits_amount)->toBe(2);

    // 4. Exactly one entitlement exists across both cards
    expect(CardEntitlement::count())->toBe(1);

    // 5. Transfer history is written
    $transfer = CardTransfer::where('nfc_card_id', $cardB->id)->first();
    expect($transfer)->not->toBeNull()
        ->and($transfer->transfer_type->value)->toBe('replacement')
        ->and($transfer->authorized_by_user_id)->toBe($this->admin->id);
});

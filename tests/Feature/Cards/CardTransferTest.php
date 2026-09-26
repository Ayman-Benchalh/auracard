<?php

use App\Actions\Cards\GenerateNfcCardAction;
use App\Actions\Cards\TransferCardAction;
use App\Enums\AccountStatus;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Models\CardEntitlement;
use App\Models\CardTransfer;
use App\Models\NfcCard;
use App\Models\User;

beforeEach(function () {
    $this->generator = app(GenerateNfcCardAction::class);
    $this->transferAction = app(TransferCardAction::class);

    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin_xfer@auracard.ma',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);

    $this->alice = User::create([
        'name' => 'Alice Original',
        'email' => 'alice_orig@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
        'email_verified_at' => now(),
    ]);

    $this->bob = User::create([
        'name' => 'Bob Recipient',
        'email' => 'bob_recip@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
        'email_verified_at' => now(),
    ]);

    $result = $this->generator->execute(null, 'matte_black', CardStatus::ACTIVE);
    $this->card = $result->card;
    $this->card->update([
        'user_id' => $this->alice->id,
        'status' => CardStatus::ACTIVE,
        'claimed_at' => now()->subWeeks(2),
        'activated_at' => now()->subWeeks(2),
    ]);

    $this->entitlement = CardEntitlement::create([
        'source_nfc_card_id' => $this->card->id,
        'current_nfc_card_id' => $this->card->id,
        'user_id' => $this->alice->id,
        'initial_credits_amount' => 2,
        'is_initial_credits_granted' => false,
        'status' => CardEntitlementStatus::ACTIVE,
    ]);
});

test('admin can transfer card ownership and entitlement preserves zero initial credit grant', function () {
    $this->transferAction->execute(
        card: $this->card,
        toUser: $this->bob,
        authorizedAdmin: $this->admin,
        reason: 'Customer requested reassignment'
    );

    $this->card->refresh();
    $this->entitlement->refresh();

    expect($this->card->user_id)->toBe($this->bob->id);
    expect($this->entitlement->user_id)->toBe($this->bob->id);
    expect($this->entitlement->is_initial_credits_granted)->toBeFalse();
    expect(CardEntitlement::count())->toBe(1);

    $history = CardTransfer::where('nfc_card_id', $this->card->id)->first();
    expect($history)->not->toBeNull()
        ->and($history->from_user_id)->toBe($this->alice->id)
        ->and($history->to_user_id)->toBe($this->bob->id)
        ->and($history->authorized_by_user_id)->toBe($this->admin->id);
});

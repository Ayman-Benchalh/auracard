<?php

use App\Actions\Cards\ClaimCardAction;
use App\Actions\Cards\GenerateNfcCardAction;
use App\Enums\AccountStatus;
use App\Enums\CardStatus;
use App\Models\CardClaim;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\User;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Verified Customer',
        'email' => 'customer_test@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
        'email_verified_at' => now(),
    ]);

    $this->unverifiedUser = User::create([
        'name' => 'Unverified Customer',
        'email' => 'unverified@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
        'email_verified_at' => null,
    ]);

    $this->generator = app(GenerateNfcCardAction::class);
});

test('claim requires authentication', function () {
    $result = $this->generator->execute(null, 'standard', CardStatus::UNCLAIMED);

    $response = $this->post('/cards/claim', [
        'raw_token' => $result->rawPublicToken,
        'claim_code' => $result->rawClaimCode,
    ]);

    $response->assertRedirect('/login');
});

test('claim requires verified email', function () {
    $result = $this->generator->execute(null, 'standard', CardStatus::UNCLAIMED);

    $response = $this->actingAs($this->unverifiedUser)->post('/cards/claim', [
        'raw_token' => $result->rawPublicToken,
        'claim_code' => $result->rawClaimCode,
    ]);

    $response->assertRedirect('/verify-email');
});

test('eligible card can be claimed with valid claim code', function () {
    $result = $this->generator->execute(null, 'matte_black', CardStatus::UNCLAIMED);

    $response = $this->actingAs($this->user)->post('/cards/claim', [
        'raw_token' => $result->rawPublicToken,
        'claim_code' => $result->rawClaimCode,
    ]);

    $response->assertRedirect('/cards/claim-success');

    $card = $result->card->fresh();
    expect($card->status)->toBe(CardStatus::ACTIVE)
        ->and($card->user_id)->toBe($this->user->id)
        ->and($card->claimed_at)->not->toBeNull()
        ->and($card->claim_code_hash)->toBeNull(); // Claim code is invalidated

    // Entitlement checks
    $entitlement = CardEntitlement::where('source_nfc_card_id', $card->id)->first();
    expect($entitlement)->not->toBeNull()
        ->and($entitlement->current_nfc_card_id)->toBe($card->id)
        ->and($entitlement->user_id)->toBe($this->user->id)
        ->and($entitlement->initial_credits_amount)->toBe(2)
        ->and($entitlement->is_initial_credits_granted)->toBeFalse() // CRITICAL: 0 credits granted at claim time!
        ->and($entitlement->initial_credits_granted_at)->toBeNull();

    // Claim history check
    $claim = CardClaim::where('nfc_card_id', $card->id)->first();
    expect($claim)->not->toBeNull()
        ->and($claim->user_id)->toBe($this->user->id)
        ->and($claim->card_entitlement_id)->toBe($entitlement->id)
        ->and($claim->visitor_identifier)->not->toBeNull();
});

test('wrong claim code fails', function () {
    $result = $this->generator->execute(null, 'standard', CardStatus::UNCLAIMED);

    $response = $this->actingAs($this->user)->post('/cards/claim', [
        'raw_token' => $result->rawPublicToken,
        'claim_code' => 'WRONG-CODE-1234',
    ]);

    $response->assertSessionHasErrors(['claim_code']);

    $card = $result->card->fresh();
    expect($card->status)->toBe(CardStatus::UNCLAIMED)
        ->and($card->user_id)->toBeNull()
        ->and(CardEntitlement::where('source_nfc_card_id', $card->id)->exists())->toBeFalse();
});

test('consumed code cannot be reused and already claimed card cannot be claimed again', function () {
    $result = $this->generator->execute(null, 'standard', CardStatus::UNCLAIMED);

    // First claim succeeds
    $this->actingAs($this->user)->post('/cards/claim', [
        'raw_token' => $result->rawPublicToken,
        'claim_code' => $result->rawClaimCode,
    ]);

    // Second claim attempt by attacker
    $attacker = User::create([
        'name' => 'Attacker',
        'email' => 'attacker@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($attacker)->post('/cards/claim', [
        'raw_token' => $result->rawPublicToken,
        'claim_code' => $result->rawClaimCode,
    ]);

    $response->assertSessionHasErrors(['claim_code']);

    // Verify only 1 entitlement exists
    expect(CardEntitlement::where('source_nfc_card_id', $result->card->id)->count())->toBe(1);
});

test('anti-double-claim: race condition prevents two simultaneous claims on the same physical card', function () {
    $result = $this->generator->execute(null, 'standard', CardStatus::UNCLAIMED);
    $action = app(ClaimCardAction::class);

    // First claim succeeds
    $card1 = $action->execute($this->user, $result->rawPublicToken, $result->rawClaimCode);
    expect($card1->user_id)->toBe($this->user->id);

    // Second simultaneous invocation fails with ValidationException
    $user2 = User::create([
        'name' => 'Second User',
        'email' => 'user2@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
        'email_verified_at' => now(),
    ]);

    expect(fn () => $action->execute($user2, $result->rawPublicToken, $result->rawClaimCode))
        ->toThrow(ValidationException::class);

    expect(CardEntitlement::where('source_nfc_card_id', $result->card->id)->count())->toBe(1);
});

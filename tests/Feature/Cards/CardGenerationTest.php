<?php

use App\Actions\Cards\GenerateCardBatchAction;
use App\Actions\Cards\GenerateNfcCardAction;
use App\Enums\AccountStatus;
use App\Enums\CardStatus;
use App\Models\NfcCard;
use App\Models\NfcCardBatch;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin_gen@auracard.ma',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
    ]);
});

test('can generate a single NFC card with secure hashes and no raw secrets stored in DB', function () {
    $action = app(GenerateNfcCardAction::class);
    $result = $action->execute(null, 'standard', CardStatus::INVENTORY);

    $card = $result->card;

    expect($card)->not->toBeNull()
        ->and($card->status)->toBe(CardStatus::INVENTORY)
        ->and($card->serial_number)->toStartWith('AC-')
        ->and(strlen($card->public_token_hash))->toBe(64)
        ->and($card->public_token_hash)->toBe(hash('sha256', $result->rawPublicToken))
        ->and($card->user_id)->toBeNull();

    // Verify claim code is properly hashed and plain code is NOT stored
    expect($card->claim_code_hash)->not->toBe($result->rawClaimCode)
        ->and(Hash::check($result->rawClaimCode, $card->claim_code_hash))->toBeTrue();

    // Verify raw public token does NOT exist anywhere in database row
    $dbRow = NfcCard::find($card->id);
    expect($dbRow->public_token_hash)->not->toBe($result->rawPublicToken)
        ->and($result->nfcUrl)->toContain($result->rawPublicToken);
});

test('card batch can generate cards with unique serials and unique token hashes', function () {
    $batchAction = app(GenerateCardBatchAction::class);

    $results = $batchAction->execute(
        batchCode: 'AURA-TEST-BATCH-001',
        quantity: 10,
        cardModel: 'matte_black',
        cardColor: 'black',
        material: 'pvc',
        notes: 'Test batch creation',
        creator: $this->admin
    );

    expect($results)->toHaveCount(10);

    $batch = NfcCardBatch::where('batch_code', 'AURA-TEST-BATCH-001')->first();
    expect($batch)->not->toBeNull()
        ->and($batch->quantity)->toBe(10)
        ->and($batch->cards)->toHaveCount(10);

    // Verify all serials and public token hashes are strictly unique
    $serials = $results->map(fn ($r) => $r->card->serial_number);
    $tokenHashes = $results->map(fn ($r) => $r->card->public_token_hash);

    expect($serials->unique())->toHaveCount(10)
        ->and($tokenHashes->unique())->toHaveCount(10);
});

test('card batch service generates batch with correct defaults and relations', function () {
    $batchService = app(\App\Services\CardBatchService::class);

    $results = $batchService->generateBatch(
        batchNumber: 'BATCH-SERVICE-TEST-001',
        quantity: 5,
        cardType: 'standard',
        initialFreeCredits: 3,
        creator: $this->admin
    );

    expect($results)->toHaveCount(5);

    $batch = NfcCardBatch::where('batch_code', 'BATCH-SERVICE-TEST-001')->first();
    expect($batch)->not->toBeNull()
        ->and($batch->quantity)->toBe(5)
        ->and($batch->cards)->toHaveCount(5);

    foreach ($results as $result) {
        expect($result->card->batch_id)->toBe($batch->id)
            ->and($result->card->status)->toBe(CardStatus::INVENTORY)
            ->and($result->rawPublicToken)->not->toBeEmpty()
            ->and($result->rawClaimCode)->not->toBeEmpty();
    }
});


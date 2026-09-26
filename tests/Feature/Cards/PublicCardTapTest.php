<?php

use App\Actions\Cards\GenerateNfcCardAction;
use App\Enums\AccountStatus;
use App\Enums\CardStatus;
use App\Models\NfcCard;
use App\Models\User;

beforeEach(function () {
    $this->cardAction = app(GenerateNfcCardAction::class);

    $this->user = User::create([
        'name' => 'Card Owner',
        'email' => 'owner@example.com',
        'password' => bcrypt('password123'),
        'account_status' => AccountStatus::ACTIVE,
        'email_verified_at' => now(),
    ]);
});

test('valid raw token resolves correct unclaimed card', function () {
    $result = $this->cardAction->execute(null, 'standard', CardStatus::UNCLAIMED);

    $response = $this->get("/c/{$result->rawPublicToken}");
    $response->assertStatus(200);
});

test('invalid token returns safe 404 response without leaking information', function () {
    $response = $this->get('/c/invalidtokenstringthatdoesnotexist1234567890');
    $response->assertStatus(404);
});

test('short or malformed token returns 404', function () {
    $response = $this->get('/c/short-token');
    $response->assertStatus(404);
});

test('suspended card follows suspended behavior and does not leak active profile', function () {
    $result = $this->cardAction->execute(null, 'standard', CardStatus::ACTIVE);
    $card = $result->card;
    $card->update([
        'user_id' => $this->user->id,
        'status' => CardStatus::SUSPENDED,
    ]);

    $response = $this->get("/c/{$result->rawPublicToken}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Public/CardTap')
        ->where('card.status', 'suspended')
    );
});

test('lost card follows lost behavior and deactivates active routing', function () {
    $result = $this->cardAction->execute(null, 'standard', CardStatus::ACTIVE);
    $card = $result->card;
    $card->update([
        'user_id' => $this->user->id,
        'status' => CardStatus::LOST,
    ]);

    $response = $this->get("/c/{$result->rawPublicToken}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Public/CardTap')
        ->where('card.status', 'lost')
    );
});

test('replaced card follows replaced behavior and does not act like active card', function () {
    $result = $this->cardAction->execute(null, 'standard', CardStatus::ACTIVE);
    $card = $result->card;
    $card->update([
        'user_id' => $this->user->id,
        'status' => CardStatus::REPLACED,
    ]);

    $response = $this->get("/c/{$result->rawPublicToken}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Public/CardTap')
        ->where('card.status', 'replaced')
    );
});

test('disabled card follows disabled behavior', function () {
    $result = $this->cardAction->execute(null, 'standard', CardStatus::DISABLED);

    $response = $this->get("/c/{$result->rawPublicToken}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Public/CardTap')
        ->where('card.status', 'disabled')
    );
});

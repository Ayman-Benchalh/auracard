<?php

use App\Actions\Cards\GenerateNfcCardAction;
use App\Enums\AccountStatus;
use App\Enums\CardStatus;
use App\Enums\RoleName;
use App\Models\NfcCard;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->superAdmin = User::where('email', 'superadmin@auracard.ma')->first();
    $this->admin = User::where('email', 'admin@auracard.ma')->first();
    $this->support = User::where('email', 'support@auracard.ma')->first();
    $this->customer = User::where('email', 'customer@auracard.ma')->first();

    $generator = app(GenerateNfcCardAction::class);
    $this->testCard = $generator->execute(null, 'standard', CardStatus::INVENTORY)->card;
});

test('customer cannot access filament nfc card admin routes', function () {
    $response = $this->actingAs($this->customer)->get('/admin/nfc-cards');
    $response->assertStatus(403);

    $batchResponse = $this->actingAs($this->customer)->get('/admin/nfc-card-batches');
    $batchResponse->assertStatus(403);
});

test('support agent can view cards but cannot perform card replacement', function () {
    $response = $this->actingAs($this->support)->get('/admin/nfc-cards');
    $response->assertStatus(200);

    expect($this->support->hasPermission('cards.replace'))->toBeFalse();
    expect($this->support->hasPermission('cards.transfer'))->toBeFalse();
});

test('admin can access filament nfc card resources', function () {
    $response = $this->actingAs($this->admin)->get('/admin/nfc-cards');
    $response->assertStatus(200);

    $batchResponse = $this->actingAs($this->admin)->get('/admin/nfc-card-batches');
    $batchResponse->assertStatus(200);
});

test('super admin has all card permissions', function () {
    expect($this->superAdmin->hasPermission('cards.create'))->toBeTrue();
    expect($this->superAdmin->hasPermission('cards.replace'))->toBeTrue();
    expect($this->superAdmin->hasPermission('cards.transfer'))->toBeTrue();
    expect($this->superAdmin->hasPermission('cards.reset_claim'))->toBeTrue();
    expect($this->superAdmin->hasPermission('cards.rotate_token'))->toBeTrue();
    expect($this->superAdmin->hasPermission('card_batches.create'))->toBeTrue();
});

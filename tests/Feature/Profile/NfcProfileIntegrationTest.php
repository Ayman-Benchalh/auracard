<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Actions\Profile\PublishProfileVersionAction;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NfcProfileIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected NfcCard $card;
    protected CardEntitlement $entitlement;
    protected string $rawToken = 'auranfcbridgetokentest1234567890abcdef';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::factory()->create(['email_verified_at' => now()]);

        $this->card = NfcCard::create([
            'batch_id' => null,
            'user_id' => $this->user->id,
            'serial_number' => 'AURA-NFC-0001',
            'public_token_hash' => NfcCard::hashPublicToken($this->rawToken),
            'claim_code_hash' => null,
            'card_type' => 'matte_black',
            'status' => CardStatus::ACTIVE,
            'activated_at' => now(),
        ]);

        $this->entitlement = CardEntitlement::create([
            'source_nfc_card_id' => $this->card->id,
            'current_nfc_card_id' => $this->card->id,
            'user_id' => $this->user->id,
            'initial_credits_amount' => 2,
            'is_initial_credits_granted' => false,
            'status' => CardEntitlementStatus::ACTIVE,
        ]);
    }

    public function test_active_card_with_published_profile_redirects_to_canonical_public_profile(): void
    {
        $createAction = app(CreateProfileForEntitlementAction::class);
        $profile = $createAction->execute($this->user, $this->entitlement, [
            'display_name' => 'Me Omar Tazi',
            'slug' => 'omar-tazi',
        ]);

        $publishAction = app(PublishProfileVersionAction::class);
        $publishAction->execute($this->user, $profile);

        // Tap physical card NFC route
        $response = $this->get("/c/{$this->rawToken}");

        // Must return 302 redirect to canonical public profile URL
        $response->assertStatus(302);
        $response->assertRedirect('/p/omar-tazi');
    }

    public function test_active_card_with_draft_profile_does_not_redirect_and_shows_safe_tap_landing(): void
    {
        $createAction = app(CreateProfileForEntitlementAction::class);
        $createAction->execute($this->user, $this->entitlement, [
            'display_name' => 'Draft User',
            'slug' => 'draft-user',
        ]);

        // Tap physical card NFC route
        $response = $this->get("/c/{$this->rawToken}");

        // Must show CardTap component without redirecting to draft
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/CardTap')
            ->where('card.status', 'active')
            ->where('card.has_profile', true)
            ->where('card.profile_status', 'draft')
        );
    }
}

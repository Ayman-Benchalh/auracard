<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Actions\Cards\ClaimCardAction;
use App\Enums\CardStatus;
use App\Enums\ProfileStatus;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\ProfessionType;
use App\Models\Profile;
use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected NfcCard $card;
    protected string $rawPublicToken;
    protected string $claimCode;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->rawPublicToken = 'aura_flow_public_token_test_1234567890';
        $this->claimCode = 'CLAIM-FLOW-9999';

        $this->card = NfcCard::create([
            'batch_id' => null,
            'user_id' => null,
            'serial_number' => 'AURA-FLOW-0001',
            'public_token_hash' => NfcCard::hashPublicToken($this->rawPublicToken),
            'claim_code_hash' => Hash::make($this->claimCode),
            'card_type' => 'matte_black',
            'status' => CardStatus::UNCLAIMED,
        ]);
    }

    public function test_complete_onboarding_wizard_from_claim_to_first_publish(): void
    {
        // 1. Claim Card
        $claimAction = app(ClaimCardAction::class);
        $card = $claimAction->execute($this->user, $this->rawPublicToken, $this->claimCode);

        $this->assertEquals($this->user->id, $card->user_id);
        $this->assertEquals(CardStatus::ACTIVE, $card->status);

        $entitlement = CardEntitlement::where('user_id', $this->user->id)->firstOrFail();
        $this->assertFalse($entitlement->is_initial_credits_granted);
        $this->assertEquals(0, $this->user->edit_credit_balance);

        // 2. Load Onboarding Wizard
        $response = $this->actingAs($this->user)->get('/profile/create');
        $response->assertOk();

        // 3. Submit 9-Step Onboarding with publish_now = true
        $profession = ProfessionType::first();
        $template = Template::first();
        $theme = Theme::first();

        $postResponse = $this->actingAs($this->user)->post('/profile', [
            'card_entitlement_id' => $entitlement->id,
            'profession_type_id' => $profession?->id,
            'template_id' => $template?->id,
            'theme_id' => $theme?->id,
            'display_name' => 'Ayman Tech',
            'headline' => 'Chief Technology Officer',
            'company' => 'Aura Innovations',
            'job_title' => 'CTO',
            'bio' => 'Building advanced contactless experiences.',
            'location' => 'Casablanca, Morocco',
            'publish_now' => true,
        ]);

        $postResponse->assertSessionHasNoErrors();

        // 4. Verify Profile Created & Published
        $profile = Profile::where('user_id', $this->user->id)->firstOrFail();
        $this->assertEquals('Ayman Tech', $profile->display_name);
        $this->assertEquals(ProfileStatus::Published, $profile->status);
        $this->assertEquals(1, $profile->current_version);
        $this->assertNotNull($profile->published_at);

        // 5. Verify Credit Invariant: +2 free credits granted, exactly 0 cost
        $entitlement->refresh();
        $this->assertTrue($entitlement->is_initial_credits_granted);
        $this->assertNotNull($entitlement->initial_credits_granted_at);
        $this->assertEquals(2, $this->user->fresh()->edit_credit_balance);

        // 6. Verify Public Profile Snapshot immediately available
        $publicResponse = $this->get("/p/{$profile->slug}");
        $publicResponse->assertOk();
    }
}

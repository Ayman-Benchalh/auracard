<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Actions\Profile\PublishProfileVersionAction;
use App\Actions\Profile\SaveProfileBlockAction;
use App\Actions\Profile\SuspendProfileAdminAction;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Enums\ProfileBlockType;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Profile $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::factory()->create(['email_verified_at' => now()]);

        $card = NfcCard::create([
            'batch_id' => null,
            'user_id' => $this->user->id,
            'serial_number' => 'AURA-VIEW-0001',
            'public_token_hash' => NfcCard::hashPublicToken('aura_view_public_token_valid_hash_32chars'),
            'claim_code_hash' => null,
            'card_type' => 'matte_black',
            'status' => CardStatus::ACTIVE,
            'activated_at' => now(),
        ]);

        $entitlement = CardEntitlement::create([
            'source_nfc_card_id' => $card->id,
            'current_nfc_card_id' => $card->id,
            'user_id' => $this->user->id,
            'initial_credits_amount' => 2,
            'is_initial_credits_granted' => false,
            'status' => CardEntitlementStatus::ACTIVE,
        ]);

        $createAction = app(CreateProfileForEntitlementAction::class);
        $this->profile = $createAction->execute($this->user, $entitlement, [
            'display_name' => 'Me Sofia Alami',
            'headline' => 'Notary Public & Legal Counselor',
            'bio' => 'Serving institutional and private clients.',
            'company' => 'Etude Notariale Alami',
            'location' => 'Casablanca, Morocco',
        ]);

        $saveBlock = app(SaveProfileBlockAction::class);
        $saveBlock->execute($this->user, $this->profile, [
            'type' => ProfileBlockType::CONTACT_ACTIONS->value,
            'title' => 'Direct Channels',
            'content' => [
                'items' => [
                    ['type' => 'phone', 'label' => 'Office', 'value' => '+212 522 11 22 33', 'is_primary' => true],
                    ['type' => 'email', 'label' => 'Direct', 'value' => 'sofia@notaire-alami.ma'],
                ],
            ],
        ]);
    }

    public function test_draft_profile_returns_404_publicly(): void
    {
        $response = $this->get("/p/{$this->profile->slug}");
        $response->assertStatus(404);
    }

    public function test_published_profile_returns_200_ok_with_snapshot_props(): void
    {
        $publishAction = app(PublishProfileVersionAction::class);
        $publishAction->execute($this->user, $this->profile);

        $response = $this->get("/p/{$this->profile->slug}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Public/ProfileView')
            ->where('profile.display_name', 'Me Sofia Alami')
            ->where('profile.headline', 'Notary Public & Legal Counselor')
            ->has('profile.blocks')
            ->has('vcard_url')
            ->has('qr_url')
        );
    }

    public function test_suspended_profile_returns_404_publicly(): void
    {
        $publishAction = app(PublishProfileVersionAction::class);
        $publishAction->execute($this->user, $this->profile);

        $admin = User::factory()->create();
        $suspendAction = app(SuspendProfileAdminAction::class);
        $suspendAction->execute($admin, $this->profile, true, 'Violation of terms');

        $response = $this->get("/p/{$this->profile->slug}");
        $response->assertStatus(404);
    }

    public function test_vcard_download_returns_valid_rfc6350_stream(): void
    {
        $publishAction = app(PublishProfileVersionAction::class);
        $publishAction->execute($this->user, $this->profile);

        $response = $this->get("/p/{$this->profile->slug}/vcard");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/vcard; charset=utf-8');
        $this->assertStringContainsString("attachment; filename=\"{$this->profile->slug}.vcf\"", (string) $response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        $this->assertStringStartsWith("BEGIN:VCARD\r\nVERSION:4.0\r\n", $content);
        $this->assertStringEndsWith("END:VCARD\r\n", $content);
        $this->assertStringContainsString('FN:Me Sofia Alami', $content);
        $this->assertStringContainsString('TITLE:Notary Public & Legal Counselor', $content);
    }

    public function test_qr_code_returns_svg_image(): void
    {
        $publishAction = app(PublishProfileVersionAction::class);
        $publishAction->execute($this->user, $this->profile);

        $response = $this->get("/p/{$this->profile->slug}/qr");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
    }
}

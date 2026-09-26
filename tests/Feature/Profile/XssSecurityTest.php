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
use App\Models\ProfileBlock;
use App\Models\User;
use App\Services\Profile\BlockValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class XssSecurityTest extends TestCase
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
            'serial_number' => 'AURA-XSS-0001',
            'public_token_hash' => NfcCard::hashPublicToken('aura_xss_token_test_1234567890abcdef'),
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
            'display_name' => 'XSS Audit Profile',
        ]);
    }

    public function test_rejection_of_javascript_url_scheme_in_blocks(): void
    {
        $response = $this->actingAs($this->user)->post("/profile/{$this->profile->id}/blocks", [
            'type' => 'social_links',
            'title' => 'Malicious Javascript Link',
            'content' => [
                'items' => [
                    ['platform' => 'twitter', 'url' => 'javascript:alert(1)'],
                ],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('profile_blocks', [
            'title' => 'Malicious Javascript Link',
        ]);
    }

    public function test_rejection_of_data_uri_scheme_in_blocks(): void
    {
        $response = $this->actingAs($this->user)->post("/profile/{$this->profile->id}/blocks", [
            'type' => 'social_links',
            'title' => 'Data URI Attack',
            'content' => [
                'items' => [
                    ['platform' => 'custom', 'url' => 'data:text/html,<script>alert(1)</script>'],
                ],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('profile_blocks', [
            'title' => 'Data URI Attack',
        ]);
    }

    public function test_rejection_of_dangerous_scheme_in_portfolio_or_projects(): void
    {
        $response = $this->actingAs($this->user)->post("/profile/{$this->profile->id}/blocks", [
            'type' => 'projects',
            'title' => 'Exploit Project',
            'content' => [
                'items' => [
                    ['title' => 'Attack', 'url' => 'vbscript:msgbox(1)'],
                ],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('profile_blocks', [
            'title' => 'Exploit Project',
        ]);
    }

    public function test_stripping_of_script_tag_in_custom_html_block(): void
    {
        $response = $this->actingAs($this->user)->post("/profile/{$this->profile->id}/blocks", [
            'type' => 'custom_cta',
            'title' => 'Script Injection CTA',
            'content' => [
                'html' => '<p>Safe Text</p><script>alert(1)</script>',
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $block = ProfileBlock::where('profile_id', $this->profile->id)
            ->where('type', \App\Enums\ProfileBlockType::CUSTOM_CTA)
            ->first();

        $this->assertNotNull($block);
        $this->assertStringNotContainsString('<script>', $block->content['html']);
        $this->assertStringNotContainsString('alert(1)', $block->content['html']);
        $this->assertStringContainsString('Safe Text', $block->content['html']);
    }

    public function test_stripping_of_inline_event_handlers_in_html_block(): void
    {
        $response = $this->actingAs($this->user)->post("/profile/{$this->profile->id}/blocks", [
            'type' => 'custom_cta',
            'title' => 'Img Onerror CTA',
            'content' => [
                'html' => '<img src=x onerror=alert(1)><span>Contact Me</span>',
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $block = ProfileBlock::where('profile_id', $this->profile->id)
            ->where('type', \App\Enums\ProfileBlockType::CUSTOM_CTA)
            ->first();

        $this->assertNotNull($block);
        $this->assertStringNotContainsString('onerror', $block->content['html']);
        $this->assertStringNotContainsString('alert(1)', $block->content['html']);
        $this->assertStringContainsString('<img src=x>', $block->content['html']);
    }

    public function test_defanging_of_javascript_and_data_schemes_in_custom_html(): void
    {
        $service = app(BlockValidationService::class);
        $sanitized = $service->sanitizeHtml('<a href="javascript:alert(1)">Click</a><a href="data:text/html,exploit">Link</a>');

        $this->assertStringNotContainsString('javascript:', $sanitized);
        $this->assertStringNotContainsString('data:', $sanitized);
        $this->assertStringContainsString('unsafe:', $sanitized);
    }

    public function test_public_profile_cannot_execute_stored_xss(): void
    {
        // Update draft with payload in text fields
        $this->actingAs($this->user)->put("/profile/{$this->profile->id}/draft", [
            'display_name' => '<script>alert(1)</script>',
            'headline' => '<img src=x onerror=alert(1)>',
            'bio' => 'Safe bio with <script>console.log("xss")</script>',
        ]);

        // Also add a custom CTA block with attempted XSS
        $this->actingAs($this->user)->post("/profile/{$this->profile->id}/blocks", [
            'type' => 'custom_cta',
            'title' => 'CTA with XSS attempt',
            'content' => [
                'html' => '<p>Hello</p><script>alert("stored")</script><img src="x" onerror="alert(2)">',
            ],
        ]);

        // Publish profile
        $publishAction = app(PublishProfileVersionAction::class);
        $publishAction->execute($this->user, $this->profile->fresh());

        // Fetch public profile view
        $response = $this->get("/p/{$this->profile->slug}");

        $response->assertOk();
        // The raw HTTP response must not contain unescaped executable script tags for the payload
        $content = $response->getContent();
        $this->assertStringNotContainsString('<script>alert("stored")</script>', $content);
        $this->assertStringNotContainsString('onerror="alert(2)"', $content);
    }
}

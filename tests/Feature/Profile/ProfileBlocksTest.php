<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Enums\ProfileBlockType;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\Profile;
use App\Models\ProfileBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileBlocksTest extends TestCase
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
            'serial_number' => 'AURA-BLOCKS-0001',
            'public_token_hash' => NfcCard::hashPublicToken('aura_blocks_token_test_1234567890abcdef'),
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
            'display_name' => 'Blocks Test Profile',
        ]);
    }

    public function test_customer_can_add_modular_block_to_draft(): void
    {
        $response = $this->actingAs($this->user)->post("/profile/{$this->profile->id}/blocks", [
            'type' => 'services',
            'title' => 'Custom Services Block',
            'is_visible' => true,
            'content' => [
                'items' => [
                    ['name' => 'Consulting', 'price' => '$200', 'description' => 'Strategy session'],
                ],
            ],
            'config' => [
                'layout' => 'compact',
            ],
        ]);

        $response->assertSessionHasNoErrors();

        $block = ProfileBlock::where('profile_id', $this->profile->id)->first();
        $this->assertNotNull($block);
        $this->assertEquals(ProfileBlockType::SERVICES, $block->type);
        $this->assertEquals('Custom Services Block', $block->title);
    }

    public function test_dangerous_url_schemes_are_rejected(): void
    {
        $response = $this->actingAs($this->user)->post("/profile/{$this->profile->id}/blocks", [
            'type' => 'social_links',
            'title' => 'Malicious Link',
            'content' => [
                'items' => [
                    ['platform' => 'xss', 'url' => 'javascript:alert(document.cookie)'],
                ],
            ],
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('profile_blocks', [
            'title' => 'Malicious Link',
        ]);
    }

    public function test_blocks_can_be_reordered(): void
    {
        $block1 = ProfileBlock::create([
            'profile_id' => $this->profile->id,
            'type' => ProfileBlockType::SERVICES,
            'sort_order' => 0,
            'is_enabled' => true,
            'settings' => ['title' => 'Block 1'],
            'content' => ['items' => [['name' => 'S1']]],
        ]);

        $block2 = ProfileBlock::create([
            'profile_id' => $this->profile->id,
            'type' => ProfileBlockType::PORTFOLIO,
            'sort_order' => 1,
            'is_enabled' => true,
            'settings' => ['title' => 'Block 2'],
            'content' => ['items' => [['title' => 'P1', 'image_url' => 'https://example.com/img.jpg']]],
        ]);

        // Reverse order
        $response = $this->actingAs($this->user)->post("/profile/{$this->profile->id}/blocks/reorder", [
            'order' => [$block2->id, $block1->id],
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertEquals(0, $block2->fresh()->sort_order);
        $this->assertEquals(1, $block1->fresh()->sort_order);
    }

    public function test_blocks_can_be_deleted(): void
    {
        $block = ProfileBlock::create([
            'profile_id' => $this->profile->id,
            'type' => ProfileBlockType::BIO,
            'sort_order' => 0,
            'is_enabled' => true,
            'settings' => ['title' => 'Temporary Bio'],
            'content' => ['headline' => 'H', 'bio' => 'B'],
        ]);

        $response = $this->actingAs($this->user)->delete("/profile/{$this->profile->id}/blocks/{$block->id}");
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('profile_blocks', [
            'id' => $block->id,
        ]);
    }
}

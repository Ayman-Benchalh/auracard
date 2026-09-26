<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Actions\Profile\PublishProfileVersionAction;
use App\Actions\Profile\UpdateProfileDraftAction;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Enums\ProfileStatus;
use App\Models\CardEntitlement;
use App\Models\EditCreditTransaction;
use App\Models\NfcCard;
use App\Models\Profile;
use App\Models\ProfileVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProfileDraftPublishTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected CardEntitlement $entitlement;
    protected Profile $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $card = NfcCard::create([
            'batch_id' => null,
            'user_id' => $this->user->id,
            'serial_number' => 'AURA-PUB-0001',
            'public_token_hash' => NfcCard::hashPublicToken('aura_pub_public_token_valid_hash_32chars'),
            'claim_code_hash' => null,
            'card_type' => 'matte_black',
            'status' => CardStatus::ACTIVE,
            'activated_at' => now(),
        ]);

        $this->entitlement = CardEntitlement::create([
            'source_nfc_card_id' => $card->id,
            'current_nfc_card_id' => $card->id,
            'user_id' => $this->user->id,
            'initial_credits_amount' => 2,
            'is_initial_credits_granted' => false,
            'status' => CardEntitlementStatus::ACTIVE,
        ]);

        $createAction = app(CreateProfileForEntitlementAction::class);
        $this->profile = $createAction->execute($this->user, $this->entitlement, [
            'display_name' => 'Dr. Karim Bennani',
            'headline' => 'Cardiologist Specialist',
            'bio' => 'Clinic based in Rabat.',
        ]);
    }

    public function test_first_publication_consumes_0_credits_and_awards_plus_2_free_credits(): void
    {
        $this->assertEquals(0, $this->user->edit_credit_balance);
        $this->assertFalse($this->entitlement->is_initial_credits_granted);

        $publishAction = app(PublishProfileVersionAction::class);
        $result = $publishAction->execute($this->user, $this->profile, 'Initial launch');

        $this->assertEquals('published', $result['status']);
        $this->assertEquals(0, $result['credits_deducted']);
        $this->assertEquals(2, $result['credits_awarded']);

        // Check entitlement updated
        $this->entitlement->refresh();
        $this->assertTrue($this->entitlement->is_initial_credits_granted);
        $this->assertNotNull($this->entitlement->initial_credits_granted_at);

        // Check user ledger balance is now exactly 2
        $this->user->refresh();
        $this->assertEquals(2, $this->user->edit_credit_balance);

        // Check profile is published with version 1
        $this->profile->refresh();
        $this->assertEquals(ProfileStatus::PUBLISHED, $this->profile->status);
        $this->assertEquals(1, $this->profile->version_counter);
        $this->assertNotNull($this->profile->published_at);
        $this->assertNotNull($this->profile->first_published_at);
    }

    public function test_double_publish_with_unchanged_content_is_idempotent(): void
    {
        $publishAction = app(PublishProfileVersionAction::class);

        // First publish
        $publishAction->execute($this->user, $this->profile);
        $this->assertEquals(2, $this->user->fresh()->edit_credit_balance);

        // Immediate second publish without changes
        $secondResult = $publishAction->execute($this->user, $this->profile);

        $this->assertEquals('unchanged', $secondResult['status']);
        $this->assertEquals(0, $secondResult['credits_deducted']);
        $this->assertEquals(0, $secondResult['credits_awarded']);

        // Balance remains exactly 2 (never +4)
        $this->assertEquals(2, $this->user->fresh()->edit_credit_balance);
        $this->assertEquals(1, ProfileVersion::where('profile_id', $this->profile->id)->count());
    }

    public function test_subsequent_publish_with_changes_deducts_one_credit(): void
    {
        $publishAction = app(PublishProfileVersionAction::class);
        $updateDraft = app(UpdateProfileDraftAction::class);

        // First publish (+2 credits awarded)
        $publishAction->execute($this->user, $this->profile);
        $this->assertEquals(2, $this->user->fresh()->edit_credit_balance);

        // Modify draft headline
        $updateDraft->execute($this->user, $this->profile, [
            'headline' => 'Chief of Cardiology & Interventional Heart Specialist',
        ]);

        // Second publish with changed content
        $result = $publishAction->execute($this->user, $this->profile, 'Updated professional title');

        $this->assertEquals('published', $result['status']);
        $this->assertEquals(1, $result['credits_deducted']);
        $this->assertEquals(0, $result['credits_awarded']);

        // Balance is now 2 - 1 = 1
        $this->assertEquals(1, $this->user->fresh()->edit_credit_balance);
        $this->assertEquals(2, $this->profile->fresh()->version_counter);
    }

    public function test_publication_blocked_when_balance_is_zero_on_subsequent_edits(): void
    {
        $publishAction = app(PublishProfileVersionAction::class);
        $updateDraft = app(UpdateProfileDraftAction::class);

        // First publish
        $publishAction->execute($this->user, $this->profile);

        // Manually exhaust credits by adjusting transactions
        EditCreditTransaction::create([
            'user_id' => $this->user->id,
            'card_entitlement_id' => $this->entitlement->id,
            'amount' => -2,
            'balance_after' => 0,
            'reason' => \App\Enums\EditCreditReason::ADMIN_CORRECTION,
            'created_at' => now(),
        ]);

        $this->assertEquals(0, $this->user->fresh()->edit_credit_balance);

        // Modify draft
        $updateDraft->execute($this->user, $this->profile, [
            'bio' => 'New updated bio content',
        ]);

        $this->expectException(ValidationException::class);

        // Attempt publication with 0 balance
        $publishAction->execute($this->user, $this->profile);
    }

    public function test_draft_edits_never_leak_to_published_version(): void
    {
        $publishAction = app(PublishProfileVersionAction::class);
        $updateDraft = app(UpdateProfileDraftAction::class);

        // 1. Initial publish
        $publishAction->execute($this->user, $this->profile);

        $initialVersion = $this->profile->fresh()->publishedVersion;
        $this->assertEquals('Cardiologist Specialist', $initialVersion->snapshot['headline']);

        // 2. Edit draft
        $updateDraft->execute($this->user, $this->profile, [
            'headline' => 'DRAFT TEMPORARY HEADLINE',
        ]);

        // 3. Verify public published version remains unchanged
        $this->profile->refresh();
        $currentPublished = $this->profile->publishedVersion;
        $this->assertEquals('Cardiologist Specialist', $currentPublished->snapshot['headline']);
        $this->assertEquals('DRAFT TEMPORARY HEADLINE', $this->profile->headline);
    }
}

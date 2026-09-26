<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Enums\ProfileStatus;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\ProfessionType;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProfileCreationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected CardEntitlement $entitlement;
    protected ProfessionType $profession;

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
            'serial_number' => 'AURA-TEST-0001',
            'public_token_hash' => NfcCard::hashPublicToken('aura_test_public_token_valid_hash_32chars'),
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

        $this->profession = ProfessionType::where('key', 'lawyer')->firstOrFail();
    }

    public function test_customer_can_create_draft_profile_bound_to_active_card_entitlement(): void
    {
        $response = $this->actingAs($this->user)->post('/profile', [
            'card_entitlement_id' => $this->entitlement->id,
            'display_name' => 'Me Ayman Lawyer',
            'headline' => 'Commercial Litigator',
            'bio' => 'Advising corporations across North Africa.',
            'company' => 'Ayman & Co Law',
            'location' => 'Casablanca, Morocco',
            'profession_type_id' => $this->profession->id,
        ]);

        $profile = Profile::where('user_id', $this->user->id)->first();

        $this->assertNotNull($profile);
        $this->assertEquals('Me Ayman Lawyer', $profile->display_name);
        $this->assertEquals(ProfileStatus::DRAFT, $profile->status);
        $this->assertEquals($this->entitlement->id, $profile->card_entitlement_id);

        $response->assertRedirect("/profile/{$profile->id}/builder");

        // Verify entitlement is linked
        $this->entitlement->refresh();
        $this->assertEquals($profile->id, $this->entitlement->profile_id);

        // Verify initial blocks were seeded from profession preset
        $this->assertGreaterThan(0, $profile->blocks()->count());
    }

    public function test_one_card_entitlement_cannot_have_multiple_profiles(): void
    {
        $action = app(CreateProfileForEntitlementAction::class);
        $action->execute($this->user, $this->entitlement, [
            'display_name' => 'First Profile',
        ]);

        $this->expectException(ValidationException::class);

        // Try creating second profile for same entitlement
        $action->execute($this->user, $this->entitlement, [
            'display_name' => 'Second Profile',
        ]);
    }

    public function test_user_cannot_bind_another_users_card_entitlement(): void
    {
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($otherUser)->post('/profile', [
            'card_entitlement_id' => $this->entitlement->id,
            'display_name' => 'Tampered Profile',
        ]);

        $response->assertSessionHasErrors('entitlement');
        $this->assertDatabaseMissing('profiles', [
            'display_name' => 'Tampered Profile',
        ]);
    }

    public function test_slug_generator_avoids_reserved_keywords(): void
    {
        $action = app(CreateProfileForEntitlementAction::class);

        $profile = $action->execute($this->user, $this->entitlement, [
            'display_name' => 'Admin User',
            'slug' => 'admin',
        ]);

        // "admin" is reserved so it should append suffix
        $this->assertNotEquals('admin', $profile->slug);
        $this->assertStringStartsWith('admin-', $profile->slug);
    }
}

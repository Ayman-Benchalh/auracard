<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Enums\RoleName;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $otherCustomer;
    protected User $supportUser;
    protected User $adminUser;
    protected Profile $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->owner = User::factory()->create(['email_verified_at' => now()]);
        $this->otherCustomer = User::factory()->create(['email_verified_at' => now()]);

        $supportRole = Role::where('name', RoleName::SUPPORT->value)->first();
        $this->supportUser = User::factory()->create(['email_verified_at' => now()]);
        if ($supportRole) {
            $this->supportUser->roles()->attach($supportRole->id);
        }

        $adminRole = Role::where('name', RoleName::ADMIN->value)->first();
        $this->adminUser = User::factory()->create(['email_verified_at' => now()]);
        if ($adminRole) {
            $this->adminUser->roles()->attach($adminRole->id);
        }

        $card = NfcCard::create([
            'batch_id' => null,
            'user_id' => $this->owner->id,
            'serial_number' => 'AURA-AUTH-0001',
            'public_token_hash' => NfcCard::hashPublicToken('aura_auth_token_test_1234567890abcdef'),
            'claim_code_hash' => null,
            'card_type' => 'matte_black',
            'status' => CardStatus::ACTIVE,
            'activated_at' => now(),
        ]);

        $entitlement = CardEntitlement::create([
            'source_nfc_card_id' => $card->id,
            'current_nfc_card_id' => $card->id,
            'user_id' => $this->owner->id,
            'initial_credits_amount' => 2,
            'is_initial_credits_granted' => false,
            'status' => CardEntitlementStatus::ACTIVE,
        ]);

        $createAction = app(CreateProfileForEntitlementAction::class);
        $this->profile = $createAction->execute($this->owner, $entitlement, [
            'display_name' => 'Owner Profile',
        ]);
    }

    public function test_owner_can_edit_own_draft(): void
    {
        $response = $this->actingAs($this->owner)->put("/profile/{$this->profile->id}/draft", [
            'display_name' => 'Updated By Owner',
            'headline' => 'Founder & CEO',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('Updated By Owner', $this->profile->fresh()->display_name);
    }

    public function test_owner_can_preview_own_draft(): void
    {
        $response = $this->actingAs($this->owner)->get("/profile/{$this->profile->id}/preview");
        $response->assertOk();
    }

    public function test_owner_can_publish_own_profile(): void
    {
        $response = $this->actingAs($this->owner)->post("/profile/{$this->profile->id}/publish");
        $response->assertSessionHasNoErrors();
        $this->assertEquals(1, $this->profile->fresh()->current_version);
    }

    public function test_another_customer_cannot_edit_profile(): void
    {
        $response = $this->actingAs($this->otherCustomer)->put("/profile/{$this->profile->id}/draft", [
            'display_name' => 'Hijacked By Other',
        ]);

        $response->assertForbidden();
        $this->assertEquals('Owner Profile', $this->profile->fresh()->display_name);
    }

    public function test_another_customer_cannot_preview_draft(): void
    {
        $response = $this->actingAs($this->otherCustomer)->get("/profile/{$this->profile->id}/preview");
        $response->assertForbidden();
    }

    public function test_another_customer_cannot_upload_media_to_profile(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg', 400, 400);

        $response = $this->actingAs($this->otherCustomer)->post("/profile/{$this->profile->id}/media", [
            'file' => $file,
            'collection' => 'avatar',
        ]);

        $response->assertForbidden();
    }

    public function test_support_cannot_mutate_profile_draft(): void
    {
        $response = $this->actingAs($this->supportUser)->put("/profile/{$this->profile->id}/draft", [
            'display_name' => 'Support Mutation Attempt',
        ]);

        $response->assertForbidden();
        $this->assertEquals('Owner Profile', $this->profile->fresh()->display_name);
    }

    public function test_admin_permissions_follow_rbac(): void
    {
        // Admin with profiles.view can preview for inspection
        $this->assertTrue($this->adminUser->hasPermission('profiles.view'));

        $previewResponse = $this->actingAs($this->adminUser)->get("/profile/{$this->profile->id}/preview");
        $previewResponse->assertOk();

        // But admin cannot mutate customer profile draft directly via customer builder endpoint
        $mutateResponse = $this->actingAs($this->adminUser)->put("/profile/{$this->profile->id}/draft", [
            'display_name' => 'Admin Direct Mutation',
        ]);
        $mutateResponse->assertForbidden();
    }
}

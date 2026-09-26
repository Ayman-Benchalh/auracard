<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Actions\Commercial\ActivatePackagePurchaseAction;
use App\Actions\Commercial\CreateCommercialOrderAction;
use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Actions\Profile\PublishProfileVersionAction;
use App\Actions\Profile\UpdateProfileDraftAction;
use App\Enums\RoleName;
use App\Models\CardDesign;
use App\Models\CommercialPackage;
use App\Models\Order;
use App\Models\PackagePurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CommercialPackageLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->customer = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->customer->assignRole(RoleName::CUSTOMER);

        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->admin->assignRole(RoleName::ADMIN);

        $this->superAdmin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->superAdmin->assignRole(RoleName::SUPER_ADMIN);
    }

    public function test_solo_package_order_and_activation_allocates_one_card_and_three_credits(): void
    {
        $soloPackage = CommercialPackage::where('key', 'solo')->firstOrFail();
        $design = CardDesign::where('key', 'matte_black')->firstOrFail();

        $createOrderAction = app(CreateCommercialOrderAction::class);
        $order = $createOrderAction->execute($this->customer, [
            'package_id' => $soloPackage->id,
            'card_selections' => [$design->key],
        ]);

        $this->assertEquals('pending', $order->status);
        $this->assertEquals(150.00, (float) $order->total_amount);
        $this->assertCount(1, $order->card_selections);

        // Approve order
        $activateAction = app(ActivatePackagePurchaseAction::class);
        $purchase = $activateAction->execute($this->admin, $order);

        $this->assertEquals('active', $purchase->status);
        $this->assertCount(1, $purchase->cardAllocations);
        $this->assertCount(1, $purchase->profileAllocations);

        // Verify entitlement has 3 initial credits configured
        $allocation = $purchase->cardAllocations->first();
        $entitlement = $allocation->entitlement;
        $this->assertNotNull($entitlement);
        $this->assertEquals(3, $entitlement->initial_credits_amount);
        $this->assertFalse($entitlement->is_initial_credits_granted);

        // First publication is free and grants exactly 3 credits
        $createProfile = app(CreateProfileForEntitlementAction::class);
        $profile = $createProfile->execute($this->customer, $entitlement, [
            'display_name' => 'Solo Consultant',
        ]);

        $publishAction = app(PublishProfileVersionAction::class);
        $result = $publishAction->execute($this->customer, $profile);

        $this->assertEquals('published', $result['status']);
        $this->assertEquals(0, $result['credits_deducted']);
        $this->assertEquals(3, $result['credits_awarded']);
        $this->assertEquals(3, $this->customer->fresh()->edit_credit_balance);
        $this->assertEquals(3, $entitlement->fresh()->edit_credit_balance);
    }

    public function test_trio_package_allocates_three_cards_and_five_isolated_credits_each(): void
    {
        $trioPackage = CommercialPackage::where('key', 'trio')->firstOrFail();
        $designs = CardDesign::take(3)->pluck('key')->all();

        $createOrderAction = app(CreateCommercialOrderAction::class);
        $order = $createOrderAction->execute($this->customer, [
            'package_id' => $trioPackage->id,
            'card_selections' => $designs,
        ]);

        $this->assertEquals(249.00, (float) $order->total_amount);
        $this->assertCount(3, $order->card_selections);

        // Activate
        $activateAction = app(ActivatePackagePurchaseAction::class);
        $purchase = $activateAction->execute($this->admin, $order);

        $this->assertCount(3, $purchase->cardAllocations);
        $this->assertCount(3, $purchase->profileAllocations);

        $entitlementA = $purchase->cardAllocations[0]->entitlement;
        $entitlementB = $purchase->cardAllocations[1]->entitlement;
        $entitlementC = $purchase->cardAllocations[2]->entitlement;

        $this->assertEquals(5, $entitlementA->initial_credits_amount);
        $this->assertEquals(5, $entitlementB->initial_credits_amount);
        $this->assertEquals(5, $entitlementC->initial_credits_amount);

        // Create profile for Card A and publish
        $createProfile = app(CreateProfileForEntitlementAction::class);
        $publishAction = app(PublishProfileVersionAction::class);
        $updateDraft = app(UpdateProfileDraftAction::class);

        $profileA = $createProfile->execute($this->customer, $entitlementA, ['display_name' => 'Profile A']);
        $publishAction->execute($this->customer, $profileA);

        // Card A has 5 credits
        $this->assertEquals(5, $entitlementA->fresh()->edit_credit_balance);

        // Create profile for Card B and publish
        $profileB = $createProfile->execute($this->customer, $entitlementB, ['display_name' => 'Profile B']);
        $publishAction->execute($this->customer, $profileB);

        // Card B has 5 credits
        $this->assertEquals(5, $entitlementB->fresh()->edit_credit_balance);

        // Publish an update with changes on Card A
        $updateDraft->execute($this->customer, $profileA, ['headline' => 'Changed Headline']);
        $result = $publishAction->execute($this->customer, $profileA, 'Updated Headline');

        $this->assertEquals(1, $result['credits_deducted']);
        // Card A now has 4 credits
        $this->assertEquals(4, $entitlementA->fresh()->edit_credit_balance);
        // CRITICAL INVARIANT: Card B credits MUST NOT have been consumed (remains 5)
        $this->assertEquals(5, $entitlementB->fresh()->edit_credit_balance);
    }

    public function test_business_5_package_allocates_five_cards_with_ten_credits_each(): void
    {
        $bizPackage = CommercialPackage::where('key', 'business_5')->firstOrFail();
        $designKeys = ['matte_black', 'metal_gold', 'bamboo_wood', 'aura_gradient', 'matte_black'];

        $createOrderAction = app(CreateCommercialOrderAction::class);
        $order = $createOrderAction->execute($this->customer, [
            'package_id' => $bizPackage->id,
            'card_selections' => $designKeys,
        ]);

        $this->assertEquals(399.00, (float) $order->total_amount);

        $activateAction = app(ActivatePackagePurchaseAction::class);
        $purchase = $activateAction->execute($this->admin, $order);

        $this->assertCount(5, $purchase->cardAllocations);
        $this->assertCount(5, $purchase->profileAllocations);

        foreach ($purchase->cardAllocations as $allocation) {
            $this->assertEquals(10, $allocation->entitlement->initial_credits_amount);
        }
    }

    public function test_profile_only_package_creates_customer_owned_card_and_four_credits(): void
    {
        $profileOnlyPackage = CommercialPackage::where('key', 'profile_only')->firstOrFail();

        $createOrderAction = app(CreateCommercialOrderAction::class);
        $order = $createOrderAction->execute($this->customer, [
            'package_id' => $profileOnlyPackage->id,
            'external_card_data' => [
                'tag_type' => 'Custom NTAG215',
                'notes' => 'Existing blank NFC card',
            ],
        ]);

        $this->assertEquals(100.00, (float) $order->total_amount);

        $activateAction = app(ActivatePackagePurchaseAction::class);
        $purchase = $activateAction->execute($this->admin, $order);

        $this->assertCount(1, $purchase->cardAllocations);
        $cardAllocation = $purchase->cardAllocations->first();
        $nfcCard = $cardAllocation->card;

        // Validates hardware_origin is customer_owned
        $this->assertEquals('customer_owned', $nfcCard->hardware_origin);
        $this->assertNotEmpty($nfcCard->public_token_hash);

        // Check 4 credits configured
        $this->assertEquals(4, $cardAllocation->entitlement->initial_credits_amount);
    }

    public function test_double_approval_of_order_is_idempotent(): void
    {
        $soloPackage = CommercialPackage::where('key', 'solo')->firstOrFail();
        $design = CardDesign::where('key', 'matte_black')->firstOrFail();

        $createOrderAction = app(CreateCommercialOrderAction::class);
        $order = $createOrderAction->execute($this->customer, [
            'package_id' => $soloPackage->id,
            'card_selections' => [$design->key],
        ]);

        $activateAction = app(ActivatePackagePurchaseAction::class);
        $purchase1 = $activateAction->execute($this->admin, $order);
        $purchase2 = $activateAction->execute($this->admin, $order);

        $this->assertEquals($purchase1->id, $purchase2->id);
        $this->assertEquals(1, PackagePurchase::where('order_id', $order->id)->count());
    }

    public function test_server_side_validation_rejects_incorrect_card_count_for_trio(): void
    {
        $trioPackage = CommercialPackage::where('key', 'trio')->firstOrFail();

        $createOrderAction = app(CreateCommercialOrderAction::class);

        $this->expectException(ValidationException::class);

        // Passing only 2 cards instead of required 3
        $createOrderAction->execute($this->customer, [
            'package_id' => $trioPackage->id,
            'card_selections' => ['matte_black', 'metal_gold'],
        ]);
    }

    public function test_commercial_packages_have_exact_required_specifications_and_profile_only_price_is_one_hundred_mad(): void
    {
        $profileOnly = CommercialPackage::where('key', 'profile_only')->firstOrFail();
        $this->assertEquals(100.00, (float) $profileOnly->price);
        $this->assertEquals(12, $profileOnly->duration_months);
        $this->assertEquals(0, $profileOnly->physical_cards_included);
        $this->assertEquals(1, $profileOnly->profiles_included);
        $this->assertEquals(4, $profileOnly->edit_credits_per_profile);
        $this->assertTrue($profileOnly->requires_customer_owned_card);

        $solo = CommercialPackage::where('key', 'solo')->firstOrFail();
        $this->assertEquals(150.00, (float) $solo->price);
        $this->assertEquals(12, $solo->duration_months);
        $this->assertEquals(1, $solo->physical_cards_included);
        $this->assertEquals(1, $solo->profiles_included);
        $this->assertEquals(3, $solo->edit_credits_per_profile);

        $trio = CommercialPackage::where('key', 'trio')->firstOrFail();
        $this->assertEquals(249.00, (float) $trio->price);
        $this->assertEquals(12, $trio->duration_months);
        $this->assertEquals(3, $trio->physical_cards_included);
        $this->assertEquals(3, $trio->profiles_included);
        $this->assertEquals(5, $trio->edit_credits_per_profile);
        $this->assertTrue((bool) $trio->is_featured);

        $biz5 = CommercialPackage::where('key', 'business_5')->firstOrFail();
        $this->assertEquals(399.00, (float) $biz5->price);
        $this->assertEquals(12, $biz5->duration_months);
        $this->assertEquals(5, $biz5->physical_cards_included);
        $this->assertEquals(5, $biz5->profiles_included);
        $this->assertEquals(10, $biz5->edit_credits_per_profile);
    }
}

<?php

namespace Database\Seeders;

use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Models\CardClaim;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\NfcCardBatch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NfcCardDemoSeeder extends Seeder
{
    /**
     * Run the NFC Card Demo Seeder for development/testing environments.
     */
    public function run(): void
    {
        $customer = User::where('email', 'customer@auracard.ma')->first();
        $admin = User::where('email', 'superadmin@auracard.ma')->first();

        // 1. Create Demo Batch
        $batch = NfcCardBatch::firstOrCreate(
            ['batch_code' => 'AURA-DEMO-001'],
            [
                'card_model' => 'standard_pvc',
                'card_color' => 'matte_black',
                'material' => 'recycled_pvc',
                'quantity' => 4,
                'notes' => 'Development baseline demo batch',
                'created_by_user_id' => $admin?->id,
            ]
        );

        // 2. Card 1: Inventory Card (Unclaimed, claim code demo: AURA-DEMO-INV1)
        $card1RawToken = 'demo_token_inventory_00000000000000000000000000000001';
        NfcCard::firstOrCreate(
            ['serial_number' => 'AC-DEMO-0001'],
            [
                'batch_id' => $batch->id,
                'public_token_hash' => hash('sha256', $card1RawToken),
                'claim_code_hash' => Hash::make('AURA-DEMO-INV1'),
                'status' => CardStatus::INVENTORY,
                'card_type' => 'matte_black',
            ]
        );

        // 3. Card 2: Sold / Unclaimed Card (claim code demo: AURA-DEMO-SLD2)
        $card2RawToken = 'demo_token_sold_000000000000000000000000000000000002';
        NfcCard::firstOrCreate(
            ['serial_number' => 'AC-DEMO-0002'],
            [
                'batch_id' => $batch->id,
                'public_token_hash' => hash('sha256', $card2RawToken),
                'claim_code_hash' => Hash::make('AURA-DEMO-SLD2'),
                'status' => CardStatus::SOLD,
                'card_type' => 'matte_black',
                'sold_at' => now()->subDays(2),
            ]
        );

        // 4. Card 3: Claimed Active Card linked to sample customer
        $card3RawToken = 'demo_token_active_000000000000000000000000000000000003';
        $card3 = NfcCard::firstOrCreate(
            ['serial_number' => 'AC-DEMO-0003'],
            [
                'batch_id' => $batch->id,
                'public_token_hash' => hash('sha256', $card3RawToken),
                'claim_code_hash' => null, // Claim code invalidated after claim
                'status' => CardStatus::ACTIVE,
                'card_type' => 'matte_black',
                'user_id' => $customer?->id,
                'sold_at' => now()->subDays(5),
                'claimed_at' => now()->subDays(4),
                'activated_at' => now()->subDays(4),
            ]
        );

        if ($customer) {
            $entitlement = CardEntitlement::firstOrCreate(
                ['source_nfc_card_id' => $card3->id],
                [
                    'current_nfc_card_id' => $card3->id,
                    'user_id' => $customer->id,
                    'initial_credits_amount' => 2,
                    'is_initial_credits_granted' => false, // Initial credits NOT granted until first publish
                    'initial_credits_granted_at' => null,
                    'status' => CardEntitlementStatus::ACTIVE,
                ]
            );

            CardClaim::firstOrCreate(
                [
                    'nfc_card_id' => $card3->id,
                    'user_id' => $customer->id,
                ],
                [
                    'card_entitlement_id' => $entitlement->id,
                    'visitor_identifier' => 'demo-visitor-identifier-salt',
                    'user_agent' => 'Mozilla/5.0 Demo Browser',
                    'claimed_at' => now()->subDays(4),
                ]
            );
        }

        // 5. Card 4: Suspended Card linked to sample customer
        $card4RawToken = 'demo_token_suspended_00000000000000000000000000000004';
        NfcCard::firstOrCreate(
            ['serial_number' => 'AC-DEMO-0004'],
            [
                'batch_id' => $batch->id,
                'public_token_hash' => hash('sha256', $card4RawToken),
                'claim_code_hash' => null,
                'status' => CardStatus::SUSPENDED,
                'card_type' => 'metal_gold',
                'user_id' => $customer?->id,
                'claimed_at' => now()->subDays(10),
                'activated_at' => now()->subDays(10),
                'suspended_at' => now()->subDay(),
            ]
        );
    }
}

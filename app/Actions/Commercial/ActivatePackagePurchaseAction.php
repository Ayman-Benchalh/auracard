<?php

declare(strict_types=1);

namespace App\Actions\Commercial;

use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Enums\PaymentRequestStatus;
use App\Models\CardDesign;
use App\Models\CardEntitlement;
use App\Models\CommercialPackage;
use App\Models\NfcCard;
use App\Models\Order;
use App\Models\PackageCardAllocation;
use App\Models\PackageProfileAllocation;
use App\Models\PackagePurchase;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ActivatePackagePurchaseAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Atomically approve an order, activate 12-month commercial entitlement,
     * and allocate isolated cards/entitlements with package-specific credit quotas.
     * Prevents double-approval via database transaction and row locking.
     *
     * @param User $admin
     * @param Order $order
     * @return PackagePurchase
     * @throws ValidationException
     */
    public function execute(User $admin, Order $order): PackagePurchase
    {
        return DB::transaction(function () use ($admin, $order) {
            $lockedOrder = Order::where('id', $order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Idempotency check: already approved
            if ($lockedOrder->status === 'approved') {
                $existing = PackagePurchase::where('order_id', $lockedOrder->id)->first();
                if ($existing) {
                    return $existing;
                }
            }

            // Customer cannot approve own order unless Super Admin
            if ($lockedOrder->user_id === $admin->id && ! $admin->hasRole(\App\Enums\RoleName::SUPER_ADMIN)) {
                throw ValidationException::withMessages([
                    'approval' => [__('You cannot approve your own order.')],
                ]);
            }

            $customer = $lockedOrder->user;
            $packageSnapshot = $lockedOrder->package_snapshot;
            $package = CommercialPackage::find($lockedOrder->commercial_package_id);

            $durationMonths = (int) ($packageSnapshot['duration_months'] ?? ($package?->duration_months ?? 12));
            $cardsCount = (int) ($packageSnapshot['physical_cards_included'] ?? ($package?->physical_cards_included ?? 1));
            $profilesCount = (int) ($packageSnapshot['profiles_included'] ?? ($package?->profiles_included ?? 1));
            $creditsPerProfile = (int) ($packageSnapshot['edit_credits_per_profile'] ?? ($package?->edit_credits_per_profile ?? 3));
            $isCustomerOwned = (bool) ($package?->requires_customer_owned_card ?? ($cardsCount === 0));

            $startsAt = now();
            $endsAt = $startsAt->copy()->addMonths($durationMonths);

            // 1. Create PackagePurchase instance
            $purchase = PackagePurchase::create([
                'order_id' => $lockedOrder->id,
                'user_id' => $customer->id,
                'commercial_package_id' => $lockedOrder->commercial_package_id,
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            // 2. Allocate Cards & Entitlements
            if ($isCustomerOwned) {
                // Package 4: Profile Only (Customer-owned NFC card)
                // Register logical AURACARD NFC card record with hardware_origin = 'customer_owned'
                $rawToken = NfcCard::generateRawPublicToken();
                $tokenHash = NfcCard::hashPublicToken($rawToken);

                $nfcCard = NfcCard::create([
                    'serial_number' => NfcCard::generateSerialNumber(),
                    'public_token_hash' => $tokenHash,
                    'status' => CardStatus::ACTIVE,
                    'hardware_origin' => 'customer_owned',
                    'card_type' => 'customer_owned_nfc',
                    'user_id' => $customer->id,
                    'activated_at' => now(),
                ]);

                // Allocate 1 CardEntitlement with initial_credits_amount = 4
                $entitlement = CardEntitlement::create([
                    'source_nfc_card_id' => $nfcCard->id,
                    'current_nfc_card_id' => $nfcCard->id,
                    'user_id' => $customer->id,
                    'initial_credits_amount' => $creditsPerProfile,
                    'is_initial_credits_granted' => false,
                    'status' => CardEntitlementStatus::ACTIVE,
                ]);

                PackageCardAllocation::create([
                    'package_purchase_id' => $purchase->id,
                    'card_design_id' => null,
                    'nfc_card_id' => $nfcCard->id,
                    'card_entitlement_id' => $entitlement->id,
                    'status' => 'active',
                ]);

                PackageProfileAllocation::create([
                    'package_purchase_id' => $purchase->id,
                    'card_entitlement_id' => $entitlement->id,
                    'profile_id' => null,
                    'edit_credits_allocated' => $creditsPerProfile,
                    'status' => 'unassigned',
                ]);
            } else {
                // Hardware Packages: Solo (1), Trio (3), Business 5 (5)
                $selections = $lockedOrder->card_selections ?? [];

                for ($i = 0; $i < $cardsCount; $i++) {
                    $selectedDesignData = $selections[$i] ?? null;
                    $designId = $selectedDesignData['design_id'] ?? null;
                    $cardType = $selectedDesignData['key'] ?? 'matte_black';

                    $rawToken = NfcCard::generateRawPublicToken();
                    $tokenHash = NfcCard::hashPublicToken($rawToken);

                    $nfcCard = NfcCard::create([
                        'serial_number' => NfcCard::generateSerialNumber(),
                        'public_token_hash' => $tokenHash,
                        'status' => CardStatus::ACTIVE,
                        'hardware_origin' => 'auracard_inventory',
                        'card_type' => $cardType,
                        'user_id' => $customer->id,
                        'activated_at' => now(),
                    ]);

                    // Allocate independent CardEntitlement with exact credits quota
                    $entitlement = CardEntitlement::create([
                        'source_nfc_card_id' => $nfcCard->id,
                        'current_nfc_card_id' => $nfcCard->id,
                        'user_id' => $customer->id,
                        'initial_credits_amount' => $creditsPerProfile,
                        'is_initial_credits_granted' => false,
                        'status' => CardEntitlementStatus::ACTIVE,
                    ]);

                    PackageCardAllocation::create([
                        'package_purchase_id' => $purchase->id,
                        'card_design_id' => $designId,
                        'nfc_card_id' => $nfcCard->id,
                        'card_entitlement_id' => $entitlement->id,
                        'status' => 'active',
                    ]);

                    PackageProfileAllocation::create([
                        'package_purchase_id' => $purchase->id,
                        'card_entitlement_id' => $entitlement->id,
                        'profile_id' => null,
                        'edit_credits_allocated' => $creditsPerProfile,
                        'status' => 'unassigned',
                    ]);
                }
            }

            // 3. Update Order and linked PaymentRequest
            $lockedOrder->status = 'approved';
            $lockedOrder->save();

            PaymentRequest::where('order_id', $lockedOrder->id)
                ->whereIn('status', [PaymentRequestStatus::PENDING->value, PaymentRequestStatus::UNDER_REVIEW->value])
                ->update([
                    'status' => PaymentRequestStatus::APPROVED->value,
                    'processed_by' => $admin->id,
                    'processed_at' => now(),
                ]);

            // 4. Financial Audit Log
            $this->auditLogService->log(
                'package_purchase.activated',
                $lockedOrder,
                ['status' => 'under_review'],
                [
                    'status' => 'approved',
                    'package_purchase_id' => $purchase->id,
                    'package_key' => $packageSnapshot['key'] ?? 'unknown',
                    'cards_allocated' => $cardsCount,
                    'profiles_allocated' => $profilesCount,
                    'credits_per_profile' => $creditsPerProfile,
                ],
                "Approved and activated Package Purchase {$purchase->id} for Order #{$lockedOrder->order_number}",
                $admin
            );

            return $purchase;
        });
    }
}

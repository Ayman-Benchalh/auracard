<?php

namespace App\Actions\Cards;

use App\Enums\CardStatus;
use App\Models\CardClaim;
use App\Models\NfcCard;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\CardEntitlementService;
use App\Services\PrivacyIdentifierService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ClaimCardAction
{
    public function __construct(
        protected CardEntitlementService $entitlementService,
        protected PrivacyIdentifierService $privacyService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Atomically claim a physical NFC card using its public token and claim code.
     * Enforces double-claim protection, locks rows, and creates hardware-bound entitlement.
     *
     * @throws ValidationException
     */
    public function execute(
        User $user,
        string $rawPublicToken,
        string $claimCode,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): NfcCard {
        $tokenHash = NfcCard::hashPublicToken(trim($rawPublicToken));
        $normalizedClaimCode = strtoupper(trim($claimCode));

        return DB::transaction(function () use (
            $user,
            $tokenHash,
            $normalizedClaimCode,
            $ipAddress,
            $userAgent
        ) {
            // 1. Locate and lock the card row
            $card = NfcCard::where('public_token_hash', $tokenHash)
                ->lockForUpdate()
                ->first();

            if (! $card) {
                throw ValidationException::withMessages([
                    'claim_code' => __('Invalid card token or claim details.'),
                ]);
            }

            // 2. Verify claimable status
            if (! in_array($card->status, [CardStatus::INVENTORY, CardStatus::SOLD, CardStatus::UNCLAIMED], true)) {
                throw ValidationException::withMessages([
                    'claim_code' => __('This card cannot be claimed. It is either already active or not available.'),
                ]);
            }

            // 3. Confirm card is not assigned to another user
            if ($card->user_id !== null && $card->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'claim_code' => __('This card is already linked to another account.'),
                ]);
            }

            // 4. Verify claim code hash
            if (empty($card->claim_code_hash) || ! Hash::check($normalizedClaimCode, $card->claim_code_hash)) {
                throw ValidationException::withMessages([
                    'claim_code' => __('The activation claim code is invalid or has already been used.'),
                ]);
            }

            // 5. Create the hardware-bound entitlement (ensuring 0 credits granted at claim time)
            $entitlement = $this->entitlementService->createForCard($card, $user);

            // 6. Link card to user and activate
            $beforePayload = [
                'status' => $card->status->value,
                'user_id' => $card->user_id,
                'claimed_at' => $card->claimed_at?->toISOString(),
            ];

            $card->update([
                'user_id' => $user->id,
                'status' => CardStatus::ACTIVE,
                'claimed_at' => now(),
                'activated_at' => now(),
                'claim_code_hash' => null, // Permanently invalidate one-time claim code
            ]);

            // 7. Write CardClaim history record
            $visitorId = $this->privacyService->generateIdentifier($ipAddress);

            CardClaim::create([
                'nfc_card_id' => $card->id,
                'user_id' => $user->id,
                'card_entitlement_id' => $entitlement->id,
                'visitor_identifier' => $visitorId,
                'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
                'claimed_at' => now(),
            ]);

            // 8. Write Audit Log
            $this->auditLogService->log(
                action: 'card.claim',
                target: $card,
                before: $beforePayload,
                after: [
                    'status' => CardStatus::ACTIVE->value,
                    'user_id' => $user->id,
                    'claimed_at' => now()->toISOString(),
                    'entitlement_id' => $entitlement->id,
                ],
                reason: 'Customer successfully claimed physical NFC card',
                actor: $user
            );

            return $card->fresh(['user', 'currentEntitlement', 'sourceEntitlement']);
        });
    }
}

<?php

namespace App\Actions\Cards;

use App\Enums\CardStatus;
use App\Enums\CardTransferType;
use App\Models\CardEntitlement;
use App\Models\CardTransfer;
use App\Models\NfcCard;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\CardEntitlementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReplaceCardAction
{
    public function __construct(
        protected CardEntitlementService $entitlementService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Atomically replace an old physical card (e.g. lost/damaged) with a fresh replacement card.
     * Preserves original source entitlement and grants zero new credits.
     *
     * @throws ValidationException
     */
    public function execute(
        NfcCard $oldCard,
        NfcCard $newCard,
        User $authorizedUser,
        string $reason = 'Hardware card replacement'
    ): NfcCard {
        if ($oldCard->id === $newCard->id) {
            throw ValidationException::withMessages([
                'replacement' => __('Cannot replace a card with itself.'),
            ]);
        }

        if (! $newCard->status->isClaimable()) {
            throw ValidationException::withMessages([
                'replacement' => __('Replacement card must be an unassigned inventory card.'),
            ]);
        }

        if ($newCard->user_id !== null) {
            throw ValidationException::withMessages([
                'replacement' => __('Replacement card is already assigned to a user.'),
            ]);
        }

        return DB::transaction(function () use ($oldCard, $newCard, $authorizedUser, $reason) {
            // Locate current entitlement for the old card
            $entitlement = CardEntitlement::where('current_nfc_card_id', $oldCard->id)->first();

            if (! $entitlement) {
                throw ValidationException::withMessages([
                    'replacement' => __('Original card does not have an active hardware entitlement.'),
                ]);
            }

            $ownerId = $oldCard->user_id;

            // 1. Mark Old Card as Replaced
            $oldCard->update([
                'status' => CardStatus::REPLACED,
                'replaced_by_card_id' => $newCard->id,
            ]);

            // 2. Activate New Card and assign to same owner
            $newCard->update([
                'user_id' => $ownerId,
                'status' => CardStatus::ACTIVE,
                'claimed_at' => now(),
                'activated_at' => now(),
                'claim_code_hash' => null, // Invalidate claim code on replacement
            ]);

            // 3. Update Entitlement: source_nfc_card_id remains immutable, current_nfc_card_id becomes new card
            $this->entitlementService->reassignCurrentCard($entitlement, $newCard);

            // 4. Create CardTransfer history
            CardTransfer::create([
                'nfc_card_id' => $newCard->id,
                'card_entitlement_id' => $entitlement->id,
                'from_user_id' => $ownerId,
                'to_user_id' => $ownerId,
                'authorized_by_user_id' => $authorizedUser->id,
                'reason' => $reason,
                'transfer_type' => CardTransferType::REPLACEMENT,
                'transferred_at' => now(),
            ]);

            // 5. Audit Log
            $this->auditLogService->log(
                action: 'card.replace',
                target: $oldCard,
                before: ['status' => $oldCard->status->value, 'card_id' => $oldCard->id],
                after: [
                    'status' => CardStatus::REPLACED->value,
                    'replaced_by_card_id' => $newCard->id,
                    'entitlement_id' => $entitlement->id,
                ],
                reason: $reason,
                actor: $authorizedUser
            );

            return $newCard->fresh(['user', 'currentEntitlement']);
        });
    }
}

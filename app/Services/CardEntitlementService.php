<?php

namespace App\Services;

use App\Enums\CardEntitlementStatus;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CardEntitlementService
{
    /**
     * Create the hardware-bound entitlement for a claimed physical NFC card.
     * Enforces that ONE eligible physical purchase = ONE free entitlement.
     * CRITICAL RULE: initial credits are NOT granted here (is_initial_credits_granted = false).
     *
     * @throws ValidationException
     */
    public function createForCard(NfcCard $card, User $user): CardEntitlement
    {
        // Enforce anti-fraud invariant: Source physical card cannot have an existing active entitlement
        $existing = CardEntitlement::where('source_nfc_card_id', $card->id)
            ->where('status', CardEntitlementStatus::ACTIVE)
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'card' => __('An active hardware entitlement already exists for this physical card.'),
            ]);
        }

        return CardEntitlement::create([
            'source_nfc_card_id' => $card->id,
            'current_nfc_card_id' => $card->id,
            'user_id' => $user->id,
            'profile_id' => null,
            'initial_credits_amount' => 2,
            'is_initial_credits_granted' => false,
            'initial_credits_granted_at' => null,
            'status' => CardEntitlementStatus::ACTIVE,
        ]);
    }

    /**
     * Reassign the current active card for an entitlement during replacement.
     * The source_nfc_card_id remains strictly immutable.
     */
    public function reassignCurrentCard(CardEntitlement $entitlement, NfcCard $replacementCard): CardEntitlement
    {
        $entitlement->update([
            'current_nfc_card_id' => $replacementCard->id,
        ]);

        return $entitlement->fresh();
    }
}

<?php

namespace App\Services;

use App\Enums\CardStatus;
use App\Models\CardClaim;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CardClaimService
{
    /**
     * Claim a physical NFC card for a customer with anti-fraud protection
     *
     * @throws ValidationException
     */
    public function claim(
        User $user,
        string $claimToken,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): NfcCard {
        $normalizedToken = trim(strtoupper($claimToken));

        $card = NfcCard::where('claim_token', $normalizedToken)->first();

        if (! $card) {
            throw ValidationException::withMessages([
                'claim_token' => __('Invalid or unrecognized claim token. Please check the code on your physical card packaging.'),
            ]);
        }

        // Anti-Fraud check: Card must not be retired or replaced
        if (in_array($card->status, [CardStatus::REPLACED, CardStatus::RETIRED], true)) {
            throw ValidationException::withMessages([
                'claim_token' => __('This card has been decommissioned or replaced. Please contact support.'),
            ]);
        }

        // Anti-Fraud check: Card must not already be claimed by a user
        if ($card->isClaimed()) {
            if ($card->assigned_user_id === $user->id) {
                throw ValidationException::withMessages([
                    'claim_token' => __('You have already claimed this card.'),
                ]);
            }

            throw ValidationException::withMessages([
                'claim_token' => __('This card is already linked to another account. The current owner must initiate an ownership transfer.'),
            ]);
        }

        return DB::transaction(function () use ($card, $user, $normalizedToken, $ipAddress, $userAgent) {
            // 1. Process Anti-Fraud Hardware Entitlement
            $entitlement = $card->entitlement;
            if (! $entitlement) {
                $entitlement = CardEntitlement::create([
                    'nfc_card_id' => $card->id,
                    'initial_free_credits' => (int) config('app.included_modification_credits', 2),
                    'credits_granted' => 0,
                    'is_claimed' => false,
                ]);
            }

            // Tie initial benefits exclusively to the first claim of this physical unit
            if (! $entitlement->is_claimed) {
                $entitlement->update([
                    'is_claimed' => true,
                    'first_claimed_by_user_id' => $user->id,
                    'first_claimed_at' => now(),
                    'credits_granted' => $entitlement->initial_free_credits,
                ]);
            }

            // 2. Assign card to user & activate
            $card->update([
                'assigned_user_id' => $user->id,
                'status' => CardStatus::ACTIVE,
                'claimed_at' => now(),
            ]);

            // 3. Create Audit Trail Record
            CardClaim::create([
                'nfc_card_id' => $card->id,
                'user_id' => $user->id,
                'claim_token_used' => $normalizedToken,
                'status' => 'successful',
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'claimed_at' => now(),
            ]);

            return $card->fresh(['entitlement', 'assignedUser']);
        });
    }
}

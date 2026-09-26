<?php

namespace App\Services;

use App\Enums\CardStatus;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CardBatchService
{
    /**
     * Generate a batch of physical NFC cards with unique claim tokens and entitlements
     */
    public function generateBatch(
        string $batchNumber,
        int $quantity,
        string $cardType = 'standard',
        int $initialFreeCredits = 2
    ): Collection {
        return DB::transaction(function () use ($batchNumber, $quantity, $cardType, $initialFreeCredits) {
            $createdCards = collect();

            for ($i = 0; $i < $quantity; $i++) {
                // Ensure unique UID
                do {
                    $cardUid = NfcCard::generateCardUid();
                } while (NfcCard::where('card_uid', $cardUid)->exists());

                // Ensure unique claim token
                do {
                    $claimToken = NfcCard::generateClaimToken();
                } while (NfcCard::where('claim_token', $claimToken)->exists());

                $card = NfcCard::create([
                    'card_uid' => $cardUid,
                    'claim_token' => $claimToken,
                    'status' => CardStatus::INACTIVE,
                    'card_type' => $cardType,
                    'batch_number' => $batchNumber,
                    'assigned_user_id' => null,
                    'claimed_at' => null,
                ]);

                CardEntitlement::create([
                    'nfc_card_id' => $card->id,
                    'initial_free_credits' => $initialFreeCredits,
                    'credits_granted' => 0,
                    'is_claimed' => false,
                ]);

                $createdCards->push($card);
            }

            return $createdCards;
        });
    }
}

<?php

namespace App\Services;

use App\Enums\CardStatus;
use App\Enums\CardTransferStatus;
use App\Models\CardClaim;
use App\Models\CardTransfer;
use App\Models\NfcCard;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CardTransferService
{
    /**
     * Initiate ownership transfer of an NFC card
     *
     * @throws ValidationException
     */
    public function initiateTransfer(
        NfcCard $card,
        User $fromUser,
        string $toEmail,
        int $validHours = 48
    ): CardTransfer {
        if ($card->assigned_user_id !== $fromUser->id) {
            throw ValidationException::withMessages([
                'card' => __('You do not own this card.'),
            ]);
        }

        if (strtolower($fromUser->email) === strtolower(trim($toEmail))) {
            throw ValidationException::withMessages([
                'to_email' => __('You cannot transfer a card to yourself.'),
            ]);
        }

        // Check if there is an existing pending transfer for this card
        $existingPending = CardTransfer::where('nfc_card_id', $card->id)
            ->where('status', CardTransferStatus::PENDING->value)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingPending) {
            throw ValidationException::withMessages([
                'to_email' => __('A pending transfer already exists for this card. Cancel it first to create a new one.'),
            ]);
        }

        return CardTransfer::create([
            'nfc_card_id' => $card->id,
            'from_user_id' => $fromUser->id,
            'to_email' => strtolower(trim($toEmail)),
            'transfer_token' => CardTransfer::generateTransferToken(),
            'status' => CardTransferStatus::PENDING,
            'expires_at' => now()->addHours($validHours),
        ]);
    }

    /**
     * Cancel an active pending transfer
     *
     * @throws ValidationException
     */
    public function cancelTransfer(CardTransfer $transfer, User $user): bool
    {
        if ($transfer->from_user_id !== $user->id) {
            throw ValidationException::withMessages([
                'transfer' => __('Unauthorized to cancel this transfer.'),
            ]);
        }

        if ($transfer->status !== CardTransferStatus::PENDING) {
            throw ValidationException::withMessages([
                'transfer' => __('Transfer is no longer pending.'),
            ]);
        }

        return $transfer->update([
            'status' => CardTransferStatus::CANCELLED,
        ]);
    }

    /**
     * Accept a card transfer
     *
     * @throws ValidationException
     */
    public function acceptTransfer(string $transferToken, User $toUser): NfcCard
    {
        $transfer = CardTransfer::where('transfer_token', trim($transferToken))->first();

        if (! $transfer || $transfer->status !== CardTransferStatus::PENDING) {
            throw ValidationException::withMessages([
                'transfer_token' => __('Invalid or expired transfer token.'),
            ]);
        }

        if ($transfer->isExpired()) {
            $transfer->update(['status' => CardTransferStatus::EXPIRED]);
            throw ValidationException::withMessages([
                'transfer_token' => __('This transfer link has expired.'),
            ]);
        }

        if (strtolower($toUser->email) !== strtolower($transfer->to_email)) {
            throw ValidationException::withMessages([
                'transfer_token' => __('This transfer was designated for a different email address.'),
            ]);
        }

        return DB::transaction(function () use ($transfer, $toUser) {
            $card = $transfer->card;

            // 1. Mark transfer completed
            $transfer->update([
                'status' => CardTransferStatus::COMPLETED,
                'to_user_id' => $toUser->id,
                'completed_at' => now(),
            ]);

            // 2. Re-assign card to new user
            $card->update([
                'assigned_user_id' => $toUser->id,
                'status' => CardStatus::ACTIVE,
            ]);

            // 3. Log claim audit trail
            CardClaim::create([
                'nfc_card_id' => $card->id,
                'user_id' => $toUser->id,
                'claim_token_used' => $transfer->transfer_token,
                'status' => 'transferred',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'claimed_at' => now(),
            ]);

            return $card->fresh(['entitlement', 'assignedUser']);
        });
    }
}

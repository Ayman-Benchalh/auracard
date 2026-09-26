<?php

namespace App\Actions\Cards;

use App\Enums\CardTransferType;
use App\Models\CardEntitlement;
use App\Models\CardTransfer;
use App\Models\NfcCard;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferCardAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Administratively transfer card ownership from one user to another.
     * Preserves physical entitlement integrity and records transfer history.
     *
     * @throws ValidationException
     */
    public function execute(
        NfcCard $card,
        User $toUser,
        User $authorizedAdmin,
        string $reason
    ): NfcCard {
        if ($card->user_id === $toUser->id) {
            throw ValidationException::withMessages([
                'user' => __('The recipient is already the owner of this card.'),
            ]);
        }

        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => __('A valid administrative transfer reason is mandatory.'),
            ]);
        }

        return DB::transaction(function () use ($card, $toUser, $authorizedAdmin, $reason) {
            $fromUserId = $card->user_id;

            // Locate entitlement
            $entitlement = CardEntitlement::where('current_nfc_card_id', $card->id)->first();

            // 1. Update Card User
            $card->update([
                'user_id' => $toUser->id,
            ]);

            // 2. Update Entitlement Owner if present
            if ($entitlement) {
                $entitlement->update([
                    'user_id' => $toUser->id,
                ]);
            }

            // 3. Record Transfer History
            CardTransfer::create([
                'nfc_card_id' => $card->id,
                'card_entitlement_id' => $entitlement?->id,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUser->id,
                'authorized_by_user_id' => $authorizedAdmin->id,
                'reason' => $reason,
                'transfer_type' => CardTransferType::OWNERSHIP_TRANSFER,
                'transferred_at' => now(),
            ]);

            // 4. Audit Log
            $this->auditLogService->log(
                action: 'card.transfer',
                target: $card,
                before: ['user_id' => $fromUserId],
                after: ['user_id' => $toUser->id, 'entitlement_id' => $entitlement?->id],
                reason: $reason,
                actor: $authorizedAdmin
            );

            return $card->fresh(['user', 'currentEntitlement']);
        });
    }
}

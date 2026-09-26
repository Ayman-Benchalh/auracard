<?php

namespace App\Actions\Cards;

use App\Enums\CardStatus;
use App\Models\NfcCard;
use App\Models\User;
use App\Services\AuditLogService;

class MarkCardLostAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Mark a physical card as lost.
     * Prevents public active resolution while preserving history and entitlement for future replacement.
     */
    public function execute(
        NfcCard $card,
        ?string $reason = 'Reported lost by owner',
        ?User $actor = null
    ): NfcCard {
        $oldStatus = $card->status;

        $card->update([
            'status' => CardStatus::LOST,
            'suspended_at' => now(),
        ]);

        $this->auditLogService->log(
            action: 'card.mark_lost',
            target: $card,
            before: ['status' => $oldStatus->value],
            after: ['status' => CardStatus::LOST->value],
            reason: $reason,
            actor: $actor
        );

        return $card->fresh();
    }
}

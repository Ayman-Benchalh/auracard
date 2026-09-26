<?php

namespace App\Actions\Cards;

use App\Enums\CardStatus;
use App\Models\NfcCard;
use App\Models\User;
use App\Services\AuditLogService;

class SuspendCardAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Suspend or reactivate an active NFC card.
     */
    public function execute(
        NfcCard $card,
        bool $suspend,
        ?string $reason = null,
        ?User $actor = null
    ): NfcCard {
        if ($suspend && $card->status !== CardStatus::ACTIVE) {
            throw new \DomainException("Only active cards can be suspended. Current status: {$card->status->value}");
        }

        if (! $suspend && $card->status !== CardStatus::SUSPENDED) {
            throw new \DomainException("Only suspended cards can be reactivated. Current status: {$card->status->value}");
        }

        $oldStatus = $card->status;
        $newStatus = $suspend ? CardStatus::SUSPENDED : CardStatus::ACTIVE;

        $card->update([
            'status' => $newStatus,
            'suspended_at' => $suspend ? now() : null,
        ]);

        $this->auditLogService->log(
            action: $suspend ? 'card.suspend' : 'card.reactivate',
            target: $card,
            before: ['status' => $oldStatus->value],
            after: ['status' => $newStatus->value],
            reason: $reason ?? ($suspend ? 'Card suspended' : 'Card reactivated'),
            actor: $actor
        );

        return $card->fresh();
    }
}

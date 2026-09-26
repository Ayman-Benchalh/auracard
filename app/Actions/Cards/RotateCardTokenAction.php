<?php

namespace App\Actions\Cards;

use App\Models\NfcCard;
use App\Models\User;
use App\Services\AuditLogService;

class RotateCardTokenAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Rotate the public NFC token of a card.
     * Returns the new raw public token strictly in-memory for physical NFC reprogramming.
     *
     * @return array{card: NfcCard, raw_public_token: string, nfc_url: string}
     */
    public function execute(
        NfcCard $card,
        ?string $reason = 'Token rotated for security',
        ?User $actor = null
    ): array {
        $rawPublicToken = NfcCard::generateRawPublicToken();
        $publicTokenHash = NfcCard::hashPublicToken($rawPublicToken);

        $card->update([
            'public_token_hash' => $publicTokenHash,
        ]);

        $this->auditLogService->log(
            action: 'card.rotate_token',
            target: $card,
            before: null,
            after: ['action' => 'token_hash_updated'],
            reason: $reason,
            actor: $actor
        );

        return [
            'card' => $card->fresh(),
            'raw_public_token' => $rawPublicToken,
            'nfc_url' => url("/c/{$rawPublicToken}"),
        ];
    }
}

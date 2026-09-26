<?php

namespace App\Actions\Cards;

use App\Models\NfcCard;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetClaimCodeAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Reset the claim code for an UNCLAIMED card.
     * Generates a new code, hashes it, and reveals the new raw code only once.
     *
     * @return array{card: NfcCard, raw_claim_code: string}
     * @throws ValidationException
     */
    public function execute(
        NfcCard $card,
        ?string $reason = 'Claim code reset by admin',
        ?User $actor = null
    ): array {
        if ($card->user_id !== null || ! $card->status->isClaimable()) {
            throw ValidationException::withMessages([
                'card' => __('Claim codes can only be reset for unclaimed inventory cards.'),
            ]);
        }

        $rawClaimCode = NfcCard::generatePlainClaimCode();
        $claimCodeHash = Hash::make($rawClaimCode);

        $card->update([
            'claim_code_hash' => $claimCodeHash,
        ]);

        $this->auditLogService->log(
            action: 'card.reset_claim_code',
            target: $card,
            before: ['status' => $card->status->value],
            after: ['action' => 'claim_code_hash_updated'],
            reason: $reason,
            actor: $actor
        );

        return [
            'card' => $card->fresh(),
            'raw_claim_code' => $rawClaimCode,
        ];
    }
}

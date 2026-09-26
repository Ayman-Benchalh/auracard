<?php

namespace App\Actions\Cards;

use App\DTOs\GeneratedCardResult;
use App\Enums\CardStatus;
use App\Models\NfcCard;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Hash;

class GenerateNfcCardAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Generate a new physical NFC Card record.
     * Computes SHA-256 for public token and Hash::make for claim code.
     * Raw public token and raw claim code are NEVER stored in database.
     */
    public function execute(
        ?string $batchId = null,
        ?string $cardType = 'standard',
        CardStatus $status = CardStatus::INVENTORY
    ): GeneratedCardResult {
        $rawPublicToken = NfcCard::generateRawPublicToken();
        $publicTokenHash = NfcCard::hashPublicToken($rawPublicToken);

        $rawClaimCode = NfcCard::generatePlainClaimCode();
        $claimCodeHash = Hash::make($rawClaimCode);

        $serialNumber = NfcCard::generateSerialNumber();

        $card = NfcCard::create([
            'batch_id' => $batchId,
            'serial_number' => $serialNumber,
            'public_token_hash' => $publicTokenHash,
            'claim_code_hash' => $claimCodeHash,
            'status' => $status,
            'card_type' => $cardType,
            'user_id' => null,
            'profile_id' => null,
        ]);

        $nfcUrl = url("/c/{$rawPublicToken}");

        $this->auditLogService->log(
            action: 'card.generate',
            target: $card,
            before: null,
            after: [
                'serial_number' => $serialNumber,
                'status' => $status->value,
                'card_type' => $cardType,
            ],
            reason: 'Generated new NFC card inventory item'
        );

        return new GeneratedCardResult(
            card: $card,
            rawPublicToken: $rawPublicToken,
            nfcUrl: $nfcUrl,
            rawClaimCode: $rawClaimCode
        );
    }
}

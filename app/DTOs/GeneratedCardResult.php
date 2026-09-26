<?php

namespace App\DTOs;

use App\Models\NfcCard;

class GeneratedCardResult
{
    public function __construct(
        public readonly NfcCard $card,
        public readonly string $rawPublicToken,
        public readonly string $nfcUrl,
        public readonly string $rawClaimCode
    ) {}

    public function toArray(): array
    {
        return [
            'card_id' => $this->card->id,
            'serial_number' => $this->card->serial_number,
            'status' => $this->card->status->value,
            'nfc_url' => $this->nfcUrl,
            'raw_claim_code' => $this->rawClaimCode,
        ];
    }
}

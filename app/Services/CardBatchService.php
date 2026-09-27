<?php

namespace App\Services;

use App\Actions\Cards\GenerateCardBatchAction;
use App\Models\User;
use Illuminate\Support\Collection;

class CardBatchService
{
    public function __construct(
        protected GenerateCardBatchAction $generateCardBatchAction
    ) {}

    /**
     * Generate a batch of physical NFC cards with unique claim tokens and entitlements.
     *
     * @return Collection<int, \App\DTOs\GeneratedCardResult>
     */
    public function generateBatch(
        string $batchNumber,
        int $quantity,
        string $cardType = 'standard',
        int $initialFreeCredits = 2,
        ?User $creator = null
    ): Collection {
        return $this->generateCardBatchAction->execute(
            batchCode: $batchNumber,
            quantity: $quantity,
            cardModel: $cardType,
            cardColor: 'black',
            material: 'pvc',
            notes: "Batch minted with initial free credits: {$initialFreeCredits}",
            creator: $creator ?? auth()->user()
        );
    }
}


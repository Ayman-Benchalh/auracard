<?php

namespace App\Actions\Cards;

use App\Enums\CardStatus;
use App\Models\NfcCardBatch;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenerateCardBatchAction
{
    public function __construct(
        protected GenerateNfcCardAction $generateCardAction,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Generate an NFC card batch and its associated physical cards.
     *
     * @return Collection<int, \App\DTOs\GeneratedCardResult>
     * @throws ValidationException
     */
    public function execute(
        string $batchCode,
        int $quantity,
        ?string $cardModel = 'standard',
        ?string $cardColor = 'black',
        ?string $material = 'pvc',
        ?string $notes = null,
        ?User $creator = null
    ): Collection {
        if ($quantity < 1 || $quantity > 500) {
            throw ValidationException::withMessages([
                'quantity' => __('Batch quantity must be between 1 and 500.'),
            ]);
        }

        if (NfcCardBatch::where('batch_code', $batchCode)->exists()) {
            throw ValidationException::withMessages([
                'batch_code' => __('A batch with this code already exists.'),
            ]);
        }

        return DB::transaction(function () use (
            $batchCode,
            $quantity,
            $cardModel,
            $cardColor,
            $material,
            $notes,
            $creator
        ) {
            $batch = NfcCardBatch::create([
                'batch_code' => $batchCode,
                'card_model' => $cardModel,
                'card_color' => $cardColor,
                'material' => $material,
                'quantity' => $quantity,
                'notes' => $notes,
                'created_by_user_id' => $creator?->id ?? auth()->id(),
            ]);

            $results = collect();

            for ($i = 0; $i < $quantity; $i++) {
                $result = $this->generateCardAction->execute(
                    batchId: $batch->id,
                    cardType: $cardModel,
                    status: CardStatus::INVENTORY
                );

                $results->push($result);
            }

            $this->auditLogService->log(
                action: 'batch.generate',
                target: $batch,
                before: null,
                after: [
                    'batch_code' => $batchCode,
                    'quantity' => $quantity,
                    'card_model' => $cardModel,
                ],
                reason: "Minted batch of {$quantity} NFC cards",
                actor: $creator
            );

            return $results;
        });
    }
}

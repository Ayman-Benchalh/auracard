<?php

namespace App\Filament\Resources\NfcCardBatchResource\Pages;

use App\Actions\Cards\GenerateCardBatchAction;
use App\Filament\Resources\NfcCardBatchResource;
use App\Models\NfcCardBatch;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateNfcCardBatch extends CreateRecord
{
    protected static string $resource = NfcCardBatchResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $batchAction = app(GenerateCardBatchAction::class);

        $results = $batchAction->execute(
            batchCode: $data['batch_code'],
            quantity: (int) $data['quantity'],
            cardModel: $data['card_model'] ?? 'standard',
            cardColor: $data['card_color'] ?? 'black',
            material: $data['material'] ?? 'pvc',
            notes: $data['notes'] ?? null,
            creator: auth()->user()
        );

        Notification::make()
            ->title('Batch and physical cards generated successfully')
            ->body("Minted {$results->count()} cards under batch {$data['batch_code']}.")
            ->success()
            ->send();

        return NfcCardBatch::where('batch_code', $data['batch_code'])->firstOrFail();
    }
}

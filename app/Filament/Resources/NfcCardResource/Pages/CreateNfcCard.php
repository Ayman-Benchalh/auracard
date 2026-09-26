<?php

namespace App\Filament\Resources\NfcCardResource\Pages;

use App\Actions\Cards\GenerateNfcCardAction;
use App\Enums\CardStatus;
use App\Filament\Resources\NfcCardResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateNfcCard extends CreateRecord
{
    protected static string $resource = NfcCardResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $generateAction = app(GenerateNfcCardAction::class);

        $result = $generateAction->execute(
            batchId: $data['batch_id'] ?? null,
            cardType: $data['card_type'] ?? 'standard',
            status: CardStatus::from($data['status'] ?? 'inventory')
        );

        Notification::make()
            ->title('Physical NFC Card Generated')
            ->body("Serial: {$result->card->serial_number}\nProgramming URL: {$result->nfcUrl}\nClaim Code: {$result->rawClaimCode}")
            ->persistent()
            ->success()
            ->send();

        return $result->card;
    }
}

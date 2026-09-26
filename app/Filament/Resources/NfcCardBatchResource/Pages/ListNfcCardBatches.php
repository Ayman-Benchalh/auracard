<?php

namespace App\Filament\Resources\NfcCardBatchResource\Pages;

use App\Filament\Resources\NfcCardBatchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNfcCardBatches extends ListRecords
{
    protected static string $resource = NfcCardBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Mint New Batch'),
        ];
    }
}

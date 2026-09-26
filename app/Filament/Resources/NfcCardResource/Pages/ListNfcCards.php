<?php

namespace App\Filament\Resources\NfcCardResource\Pages;

use App\Filament\Resources\NfcCardResource;
use App\Services\CardBatchService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListNfcCards extends ListRecords
{
    protected static string $resource = NfcCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_batch')
                ->label('Mint Card Batch')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->visible(fn () => auth()->user()?->hasPermission('cards.create_batch'))
                ->form([
                    Forms\Components\TextInput::make('batch_number')
                        ->label('Batch Identifier')
                        ->default('BATCH-' . date('Ymd-His'))
                        ->required(),
                    Forms\Components\TextInput::make('quantity')
                        ->label('Number of Cards to Mint')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(500)
                        ->default(10)
                        ->required(),
                    Forms\Components\Select::make('card_type')
                        ->label('Hardware Card Type')
                        ->options([
                            'standard' => 'Standard PVC',
                            'matte_black' => 'Matte Black',
                            'metal_gold' => 'Metal Gold',
                            'bamboo_wood' => 'Eco Bamboo Wood',
                        ])
                        ->default('standard')
                        ->required(),
                    Forms\Components\TextInput::make('initial_free_credits')
                        ->label('Included Modification Credits')
                        ->numeric()
                        ->default(2)
                        ->required(),
                ])
                ->action(function (array $data, CardBatchService $batchService) {
                    $cards = $batchService->generateBatch(
                        batchNumber: $data['batch_number'],
                        quantity: (int) $data['quantity'],
                        cardType: $data['card_type'],
                        initialFreeCredits: (int) $data['initial_free_credits']
                    );

                    Notification::make()
                        ->title('Batch minted successfully!')
                        ->body("Created {$cards->count()} NFC cards under batch {$data['batch_number']}.")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()->label('Add Single Card'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NfcCardBatchResource\Pages;
use App\Models\NfcCardBatch;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NfcCardBatchResource extends Resource
{
    protected static ?string $model = NfcCardBatch::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-archive-box';

    protected static string | \UnitEnum | null $navigationGroup = 'NFC Hardware & Inventory';

    protected static ?string $navigationLabel = 'Card Batches';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('card_batches.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('card_batches.create') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('batch_code')
                    ->label('Batch Identifier Code')
                    ->default(fn () => 'AURA-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(2))))
                    ->required()
                    ->maxLength(64)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('quantity')
                    ->label('Initial Card Quantity to Mint')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(500)
                    ->default(10)
                    ->required(),

                Forms\Components\Select::make('card_model')
                    ->label('Hardware Card Model')
                    ->options([
                        'standard_pvc' => 'Standard PVC',
                        'matte_black' => 'Matte Black',
                        'metal_gold' => 'Metal Gold',
                        'bamboo_wood' => 'Eco Bamboo Wood',
                    ])
                    ->default('matte_black')
                    ->required(),

                Forms\Components\TextInput::make('card_color')
                    ->label('Color Finish')
                    ->default('black')
                    ->maxLength(32),

                Forms\Components\TextInput::make('material')
                    ->label('Physical Material')
                    ->default('pvc')
                    ->maxLength(64),

                Forms\Components\Textarea::make('notes')
                    ->label('Batch Production Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('batch_code')
                    ->label('Batch Code')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('card_model')
                    ->label('Model')
                    ->badge(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cards_count')
                    ->label('Generated Cards')
                    ->counts('cards'),

                Tables\Columns\TextColumn::make('createdByUser.name')
                    ->label('Created By')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNfcCardBatches::route('/'),
            'create' => Pages\CreateNfcCardBatch::route('/create'),
            'view' => Pages\ViewNfcCardBatch::route('/{record}'),
        ];
    }
}

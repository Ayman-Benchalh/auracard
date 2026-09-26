<?php

namespace App\Filament\Resources;

use App\Actions\Cards\MarkCardLostAction;
use App\Actions\Cards\ReplaceCardAction;
use App\Actions\Cards\ResetClaimCodeAction;
use App\Actions\Cards\RotateCardTokenAction;
use App\Actions\Cards\SuspendCardAction;
use App\Actions\Cards\TransferCardAction;
use App\Enums\CardStatus;
use App\Filament\Resources\NfcCardResource\Pages;
use App\Models\NfcCard;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class NfcCardResource extends Resource
{
    protected static ?string $model = NfcCard::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';

    protected static string | \UnitEnum | null $navigationGroup = 'NFC Hardware & Inventory';

    protected static ?string $navigationLabel = 'NFC Cards';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('cards.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermission('cards.create') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('serial_number')
                    ->label('Card Serial Number')
                    ->disabled()
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        CardStatus::INVENTORY->value => 'Inventory',
                        CardStatus::RESERVED->value => 'Reserved',
                        CardStatus::SOLD->value => 'Sold',
                        CardStatus::UNCLAIMED->value => 'Unclaimed',
                        CardStatus::CLAIMED->value => 'Claimed',
                        CardStatus::ACTIVE->value => 'Active',
                        CardStatus::SUSPENDED->value => 'Suspended',
                        CardStatus::LOST->value => 'Lost',
                        CardStatus::REPLACED->value => 'Replaced',
                        CardStatus::DISABLED->value => 'Disabled',
                    ])
                    ->required(),

                Forms\Components\Select::make('batch_id')
                    ->label('Production Batch')
                    ->relationship('batch', 'batch_code')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('user_id')
                    ->label('Assigned Customer Owner')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('card_type')
                    ->label('Card Model / Finish'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->fontFamily('mono')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('batch.batch_code')
                    ->label('Batch')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray' => CardStatus::INVENTORY->value,
                        'info' => CardStatus::RESERVED->value,
                        'warning' => CardStatus::SOLD->value,
                        'gray' => CardStatus::UNCLAIMED->value,
                        'info' => CardStatus::CLAIMED->value,
                        'success' => CardStatus::ACTIVE->value,
                        'warning' => CardStatus::SUSPENDED->value,
                        'danger' => CardStatus::LOST->value,
                        'danger' => CardStatus::REPLACED->value,
                        'danger' => CardStatus::DISABLED->value,
                    ]),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer Owner')
                    ->searchable()
                    ->default('Unassigned'),

                Tables\Columns\TextColumn::make('card_type')
                    ->label('Model')
                    ->badge(),

                Tables\Columns\TextColumn::make('sold_at')
                    ->label('Sold At')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('claimed_at')
                    ->label('Claimed At')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        CardStatus::INVENTORY->value => 'Inventory',
                        CardStatus::RESERVED->value => 'Reserved',
                        CardStatus::SOLD->value => 'Sold',
                        CardStatus::UNCLAIMED->value => 'Unclaimed',
                        CardStatus::ACTIVE->value => 'Active',
                        CardStatus::SUSPENDED->value => 'Suspended',
                        CardStatus::LOST->value => 'Lost',
                        CardStatus::REPLACED->value => 'Replaced',
                        CardStatus::DISABLED->value => 'Disabled',
                    ]),

                Tables\Filters\TernaryFilter::make('claimed')
                    ->label('Claimed State')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('user_id'),
                        false: fn ($query) => $query->whereNull('user_id'),
                    ),
            ])
            ->actions([
                // 1. Mark Sold Action
                Action::make('mark_sold')
                    ->label('Mark Sold')
                    ->icon('heroicon-o-shopping-bag')
                    ->color('warning')
                    ->visible(fn (NfcCard $record) => in_array($record->status, [CardStatus::INVENTORY, CardStatus::RESERVED], true) && auth()->user()?->hasPermission('cards.update'))
                    ->requiresConfirmation()
                    ->action(function (NfcCard $record) {
                        $record->update([
                            'status' => CardStatus::SOLD,
                            'sold_at' => now(),
                        ]);
                        Notification::make()->title('Card marked as sold')->success()->send();
                    }),

                // 2. Suspend / Reactivate Action
                Action::make('toggle_suspend')
                    ->label(fn (NfcCard $record) => $record->status === CardStatus::SUSPENDED ? 'Reactivate' : 'Suspend')
                    ->icon(fn (NfcCard $record) => $record->status === CardStatus::SUSPENDED ? 'heroicon-o-play' : 'heroicon-o-pause')
                    ->color('warning')
                    ->visible(fn (NfcCard $record) => in_array($record->status, [CardStatus::ACTIVE, CardStatus::SUSPENDED], true) && auth()->user()?->hasPermission('cards.suspend'))
                    ->requiresConfirmation()
                    ->action(function (NfcCard $record, SuspendCardAction $action) {
                        $suspend = $record->status !== CardStatus::SUSPENDED;
                        $action->execute($record, $suspend, 'Admin toggle status', auth()->user());
                        Notification::make()
                            ->title($suspend ? 'Card suspended' : 'Card reactivated')
                            ->success()
                            ->send();
                    }),

                // 3. Mark Lost Action
                Action::make('mark_lost')
                    ->label('Mark Lost')
                    ->icon('heroicon-o-shield-exclamation')
                    ->color('danger')
                    ->visible(fn (NfcCard $record) => ! in_array($record->status, [CardStatus::LOST, CardStatus::REPLACED, CardStatus::DISABLED], true) && auth()->user()?->hasPermission('cards.update'))
                    ->requiresConfirmation()
                    ->action(function (NfcCard $record, MarkCardLostAction $action) {
                        $action->execute($record, 'Admin marked as lost', auth()->user());
                        Notification::make()->title('Card marked as lost')->danger()->send();
                    }),

                // 4. Replace Card Action
                Action::make('replace_card')
                    ->label('Replace Card')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('danger')
                    ->visible(fn (NfcCard $record) => $record->user_id !== null && auth()->user()?->hasPermission('cards.replace'))
                    ->form([
                        Forms\Components\Select::make('replacement_card_id')
                            ->label('Select Replacement Inventory Card')
                            ->options(function () {
                                return NfcCard::whereIn('status', [CardStatus::INVENTORY, CardStatus::SOLD, CardStatus::UNCLAIMED])
                                    ->whereNull('user_id')
                                    ->pluck('serial_number', 'id');
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Replacement Reason')
                            ->default('Original card lost/damaged')
                            ->required(),
                    ])
                    ->action(function (NfcCard $record, array $data, ReplaceCardAction $action) {
                        $newCard = NfcCard::findOrFail($data['replacement_card_id']);
                        $action->execute($record, $newCard, auth()->user(), $data['reason']);
                        Notification::make()
                            ->title('Card replaced successfully')
                            ->body("Old card {$record->serial_number} replaced with {$newCard->serial_number}. Entitlement preserved.")
                            ->success()
                            ->send();
                    }),

                // 5. Transfer Ownership Action
                Action::make('transfer_card')
                    ->label('Transfer Owner')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->visible(fn (NfcCard $record) => $record->user_id !== null && auth()->user()?->hasPermission('cards.transfer'))
                    ->form([
                        Forms\Components\Select::make('to_user_id')
                            ->label('New Customer Owner')
                            ->options(User::pluck('email', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason for Transfer')
                            ->required(),
                    ])
                    ->action(function (NfcCard $record, array $data, TransferCardAction $action) {
                        $toUser = User::findOrFail($data['to_user_id']);
                        $action->execute($record, $toUser, auth()->user(), $data['reason']);
                        Notification::make()
                            ->title('Ownership transferred successfully')
                            ->body("Transferred card {$record->serial_number} to {$toUser->email}.")
                            ->success()
                            ->send();
                    }),

                // 6. Reset Claim Code Action (Unclaimed only)
                Action::make('reset_claim_code')
                    ->label('Reset Claim Code')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn (NfcCard $record) => $record->user_id === null && $record->status->isClaimable() && auth()->user()?->hasPermission('cards.reset_claim'))
                    ->requiresConfirmation()
                    ->action(function (NfcCard $record, ResetClaimCodeAction $action) {
                        $result = $action->execute($record, 'Admin reset code', auth()->user());
                        Notification::make()
                            ->title('New Claim Code Generated')
                            ->body("One-time code: {$result['raw_claim_code']}. Copy and package with physical card.")
                            ->warning()
                            ->persistent()
                            ->send();
                    }),

                // 7. Rotate Public Token
                Action::make('rotate_token')
                    ->label('Rotate Token')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn () => auth()->user()?->hasPermission('cards.rotate_token'))
                    ->requiresConfirmation()
                    ->action(function (NfcCard $record, RotateCardTokenAction $action) {
                        $result = $action->execute($record, 'Admin rotated token', auth()->user());
                        Notification::make()
                            ->title('New NFC Programming URL Generated')
                            ->body("URL: {$result['nfc_url']}. Write this URL to physical NFC chip.")
                            ->warning()
                            ->persistent()
                            ->send();
                    }),

                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNfcCards::route('/'),
            'create' => Pages\CreateNfcCard::route('/create'),
            'edit' => Pages\EditNfcCard::route('/{record}/edit'),
        ];
    }
}

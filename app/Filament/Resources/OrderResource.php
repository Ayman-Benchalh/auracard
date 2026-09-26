<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\Commercial\ActivatePackagePurchaseAction;
use App\Enums\RoleName;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string | \UnitEnum | null $navigationGroup = 'Commercial & Finance';

    protected static ?string $navigationLabel = 'Commercial Orders';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->hasRole(RoleName::SUPER_ADMIN) || $user?->hasRole(RoleName::ADMIN);
    }

    public static function canCreate(): bool
    {
        return false; // Customer submitted via checkout
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('order_number')
                    ->label('Order Number')
                    ->disabled(),

                Forms\Components\TextInput::make('user.name')
                    ->label('Customer Name')
                    ->disabled(),

                Forms\Components\TextInput::make('user.email')
                    ->label('Customer Email')
                    ->disabled(),

                Forms\Components\TextInput::make('package.name.fr')
                    ->label('Package')
                    ->disabled(),

                Forms\Components\TextInput::make('total_amount')
                    ->label('Total Amount')
                    ->suffix('MAD')
                    ->disabled(),

                Forms\Components\TextInput::make('status')
                    ->label('Order Status')
                    ->disabled(),

                Forms\Components\Textarea::make('customer_notes')
                    ->label('Customer Notes')
                    ->disabled()
                    ->rows(2),

                Forms\Components\DateTimePicker::make('created_at')
                    ->label('Placed At')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('package.name.fr')
                    ->label('Package')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Amount')
                    ->money('MAD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'fulfilled' => 'primary',
                        'under_review' => 'warning',
                        'pending' => 'gray',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),

                Action::make('approveOrder')
                    ->label('Approve & Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => in_array($record->status, ['pending', 'under_review'], true))
                    ->action(function (Order $record): void {
                        $activateAction = app(ActivatePackagePurchaseAction::class);
                        $activateAction->execute(auth()->user(), $record);

                        Notification::make()
                            ->title('Order Approved & Activated')
                            ->body("Package purchase activated with card & profile entitlements.")
                            ->success()
                            ->send();
                    }),

                Action::make('markFulfilled')
                    ->label('Fulfill')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->status === 'approved')
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'fulfilled']);

                        Notification::make()
                            ->title('Order Fulfilled')
                            ->body("Order #{$record->order_number} marked as fulfilled.")
                            ->info()
                            ->send();
                    }),

                Action::make('cancelOrder')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => ! in_array($record->status, ['fulfilled', 'cancelled'], true))
                    ->action(function (Order $record): void {
                        $record->update(['status' => 'cancelled']);

                        Notification::make()
                            ->title('Order Cancelled')
                            ->body("Order #{$record->order_number} marked as cancelled.")
                            ->danger()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RoleName;
use App\Enums\SubscriptionStatus;
use App\Filament\Resources\SubscriptionResource\Pages;
use App\Models\Subscription;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string | \UnitEnum | null $navigationGroup = 'Commercial & Finance';

    protected static ?string $navigationLabel = 'Subscriptions';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->hasPermission('subscriptions.view') || $user?->hasRole(RoleName::SUPER_ADMIN);
    }

    public static function canCreate(): bool
    {
        return false; // Created via payment approval workflow
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('user.name')->label('Customer')->disabled(),
                Forms\Components\TextInput::make('user.email')->label('Email')->disabled(),
                Forms\Components\TextInput::make('plan.name')->label('Plan')->disabled(),
                Forms\Components\TextInput::make('price_amount')->disabled(),
                Forms\Components\TextInput::make('price_currency')->disabled(),
                Forms\Components\TextInput::make('billing_period')->disabled(),
                Forms\Components\TextInput::make('status')->disabled(),
                Forms\Components\DateTimePicker::make('starts_at')->disabled(),
                Forms\Components\DateTimePicker::make('ends_at')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_amount')
                    ->label('Price')
                    ->money('MAD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (SubscriptionStatus $state) => match ($state) {
                        SubscriptionStatus::ACTIVE => 'success',
                        SubscriptionStatus::EXPIRED => 'gray',
                        SubscriptionStatus::CANCELLED => 'danger',
                        SubscriptionStatus::PENDING => 'warning',
                        SubscriptionStatus::REJECTED => 'danger',
                    })
                    ->formatStateUsing(fn (SubscriptionStatus $state) => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        SubscriptionStatus::ACTIVE->value => 'Active',
                        SubscriptionStatus::EXPIRED->value => 'Expired',
                        SubscriptionStatus::PENDING->value => 'Pending Payment',
                        SubscriptionStatus::CANCELLED->value => 'Cancelled',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'view' => Pages\ViewSubscription::route('/{record}'),
        ];
    }
}

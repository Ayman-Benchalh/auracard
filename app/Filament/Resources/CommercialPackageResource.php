<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RoleName;
use App\Filament\Resources\CommercialPackageResource\Pages;
use App\Models\CommercialPackage;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CommercialPackageResource extends Resource
{
    protected static ?string $model = CommercialPackage::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    protected static string | \UnitEnum | null $navigationGroup = 'Commercial & Finance';

    protected static ?string $navigationLabel = 'Commercial Packages';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->hasRole(RoleName::SUPER_ADMIN) || $user?->hasRole(RoleName::ADMIN);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('key')
                    ->label('Package Identifier')
                    ->required()
                    ->disabled()
                    ->helperText('Package key: solo, trio, business_5, profile_only'),

                Forms\Components\TextInput::make('name.en')
                    ->label('Package Name (EN)')
                    ->required(),
                Forms\Components\TextInput::make('name.fr')
                    ->label('Package Name (FR)')
                    ->required(),
                Forms\Components\TextInput::make('name.ar')
                    ->label('Package Name (AR)')
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->required()
                    ->suffix('MAD')
                    ->helperText('Solo: 150 MAD, Trio: 249 MAD, Business 5: 399 MAD, Profile Only: 100 MAD'),

                Forms\Components\TextInput::make('currency')
                    ->default('MAD')
                    ->required(),

                Forms\Components\TextInput::make('duration_months')
                    ->label('Entitlement Duration (Months)')
                    ->numeric()
                    ->default(12)
                    ->required(),

                Forms\Components\TextInput::make('physical_cards_included')
                    ->label('Physical NFC Cards Included')
                    ->numeric()
                    ->required()
                    ->helperText('0 for Profile Only, 1 for Solo, 3 for Trio, 5 for Business 5'),

                Forms\Components\TextInput::make('profiles_included')
                    ->label('Profiles Included')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('edit_credits_per_profile')
                    ->label('Modification Credits (per profile)')
                    ->numeric()
                    ->required()
                    ->helperText('Solo: 3, Trio: 5, Business 5: 10, Profile Only: 4'),

                Forms\Components\Toggle::make('allows_customer_owned_card')
                    ->label('Allows Customer-Owned Card'),

                Forms\Components\Toggle::make('requires_customer_owned_card')
                    ->label('Requires Customer-Owned Card (Profile Only)'),

                Forms\Components\Toggle::make('is_featured')
                    ->label('Featured Package (Recommended badge on Landing & Pricing)')
                    ->helperText('Super Admin can toggle which package is highlighted to customers'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active for Purchases')
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),

                Forms\Components\Textarea::make('description.en')
                    ->label('Description (EN)')
                    ->rows(2),
                Forms\Components\Textarea::make('description.fr')
                    ->label('Description (FR)')
                    ->rows(2),
                Forms\Components\Textarea::make('description.ar')
                    ->label('Description (AR)')
                    ->rows(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name.fr')
                    ->label('Package Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('MAD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('physical_cards_included')
                    ->label('Cards')
                    ->sortable(),

                Tables\Columns\TextColumn::make('profiles_included')
                    ->label('Profiles')
                    ->sortable(),

                Tables\Columns\TextColumn::make('edit_credits_per_profile')
                    ->label('Credits / Profile')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->actions([
                Action::make('setFeatured')
                    ->label('Set Featured')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (CommercialPackage $record): bool => ! $record->is_featured)
                    ->action(function (CommercialPackage $record): void {
                        CommercialPackage::where('id', '!=', $record->id)->update(['is_featured' => false]);
                        $record->update(['is_featured' => true]);

                        Notification::make()
                            ->title('Featured Package Updated')
                            ->body("{$record->key} is now set as the featured package.")
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommercialPackages::route('/'),
            'edit' => Pages\EditCommercialPackage::route('/{record}/edit'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RoleName;
use App\Filament\Resources\PaymentSettingResource\Pages;
use App\Models\PaymentSetting;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentSettingResource extends Resource
{
    protected static ?string $model = PaymentSetting::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';

    protected static string | \UnitEnum | null $navigationGroup = 'Commercial & Finance';

    protected static ?string $navigationLabel = 'Payment Methods & Banking';

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
                    ->label('Method Key / Identifier')
                    ->required()
                    ->maxLength(64)
                    ->helperText('Unique technical key (e.g. manual_bank_transfer, cash_deposit, wafacash)'),

                Forms\Components\TextInput::make('title.en')
                    ->label('Title (English)')
                    ->required(),
                Forms\Components\TextInput::make('title.fr')
                    ->label('Title (French)')
                    ->required(),
                Forms\Components\TextInput::make('title.ar')
                    ->label('Title (Arabic)')
                    ->required(),

                Forms\Components\TextInput::make('bank_name')
                    ->label('Bank / Payment Provider')
                    ->nullable()
                    ->helperText('e.g. Partner Bank, Cash Network Agency'),

                Forms\Components\TextInput::make('account_holder')
                    ->label('Account Holder / Beneficiary Name')
                    ->nullable()
                    ->helperText('Legal entity name shown to customers for payment'),

                Forms\Components\TextInput::make('rib')
                    ->label('RIB / Account / Reference Code')
                    ->nullable()
                    ->helperText('Bank account RIB or agency payment code. Never commit real credentials.'),

                Forms\Components\TextInput::make('swift_bic')
                    ->label('SWIFT / BIC (Optional)')
                    ->nullable(),

                Forms\Components\Textarea::make('instructions.en')
                    ->label('Instructions (English)')
                    ->rows(3)
                    ->nullable(),
                Forms\Components\Textarea::make('instructions.fr')
                    ->label('Instructions (French)')
                    ->rows(3)
                    ->nullable(),
                Forms\Components\Textarea::make('instructions.ar')
                    ->label('Instructions (Arabic)')
                    ->rows(3)
                    ->nullable(),

                Forms\Components\Toggle::make('is_enabled')
                    ->label('Enabled for Checkout')
                    ->default(true),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Display Order')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title.fr')
                    ->label('Title (FR)')
                    ->searchable(),

                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Provider / Bank')
                    ->searchable(),

                Tables\Columns\TextColumn::make('account_holder')
                    ->label('Account Holder')
                    ->searchable(),

                Tables\Columns\TextColumn::make('rib')
                    ->label('RIB / Account')
                    ->fontFamily('mono')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_enabled')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentSettings::route('/'),
            'create' => Pages\CreatePaymentSetting::route('/create'),
            'edit' => Pages\EditPaymentSetting::route('/{record}/edit'),
        ];
    }
}

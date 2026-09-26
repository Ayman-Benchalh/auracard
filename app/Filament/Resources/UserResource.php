<?php

namespace App\Filament\Resources;

use App\Enums\AccountStatus;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static string | \UnitEnum | null $navigationGroup = 'Users & Security';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\Select::make('account_status')
                    ->options([
                        AccountStatus::ACTIVE->value => 'Active',
                        AccountStatus::SUSPENDED->value => 'Suspended',
                        AccountStatus::DISABLED->value => 'Disabled',
                    ])
                    ->required(),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\BadgeColumn::make('account_status')
                    ->colors([
                        'success' => AccountStatus::ACTIVE->value,
                        'warning' => AccountStatus::SUSPENDED->value,
                        'danger' => AccountStatus::DISABLED->value,
                    ]),
                Tables\Columns\TextColumn::make('roles.name')->badge(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('account_status')
                    ->options([
                        AccountStatus::ACTIVE->value => 'Active',
                        AccountStatus::SUSPENDED->value => 'Suspended',
                        AccountStatus::DISABLED->value => 'Disabled',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

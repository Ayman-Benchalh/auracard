<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RoleName;
use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static string | \UnitEnum | null $navigationGroup = 'Commercial & Finance';

    protected static ?string $navigationLabel = 'Commercial Plans';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->hasPermission('plans.manage') || $user?->hasRole(RoleName::SUPER_ADMIN);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('key')->required()->disabled(),
                Forms\Components\TextInput::make('price')->numeric()->required()->suffix('MAD'),
                Forms\Components\TextInput::make('currency')->default('MAD')->required(),
                Forms\Components\Select::make('billing_period')
                    ->options([
                        'yearly' => 'Yearly',
                        'monthly' => 'Monthly',
                        'lifetime' => 'Lifetime',
                    ])
                    ->required(),
                Forms\Components\Toggle::make('is_active'),
                Forms\Components\Toggle::make('is_recommended'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Textarea::make('description')->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('key')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('MAD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_period')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_recommended')
                    ->boolean(),

                Tables\Columns\TextColumn::make('sort_order')
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
            'index' => Pages\ListPlans::route('/'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}

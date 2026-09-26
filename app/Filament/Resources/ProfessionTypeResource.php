<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProfessionTypeResource\Pages;
use App\Models\ProfessionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProfessionTypeResource extends Resource
{
    protected static ?string $model = ProfessionType::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-briefcase';

    protected static string | \UnitEnum | null $navigationGroup = 'Digital Profiles';

    protected static ?string $navigationLabel = 'Profession Presets';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('profiles.view') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('name.en')
                    ->label('Name (English)')
                    ->required(),

                Forms\Components\TextInput::make('name.fr')
                    ->label('Name (French)')
                    ->required(),

                Forms\Components\TextInput::make('name.ar')
                    ->label('Name (Arabic)')
                    ->required(),

                Forms\Components\TextInput::make('category')
                    ->maxLength(100),

                Forms\Components\TextInput::make('icon')
                    ->label('Icon Identifier')
                    ->maxLength(100),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),

                Forms\Components\Textarea::make('default_blocks')
                    ->label('Default Blocks (JSON)')
                    ->rows(6)
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? json_decode($state, true) : $state),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name.en')
                    ->label('Name (EN)')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name.fr')
                    ->label('Name (FR)')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name.ar')
                    ->label('Name (AR)'),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfessionTypes::route('/'),
            'create' => Pages\CreateProfessionType::route('/create'),
            'edit' => Pages\EditProfessionType::route('/{record}/edit'),
        ];
    }
}

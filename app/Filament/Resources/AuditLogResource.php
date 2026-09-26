<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings & Audit';

    protected static ?string $navigationLabel = 'Audit Logs';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('audit_logs.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Audit logs are immutable system-generated logs
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('action')->disabled(),
                Forms\Components\TextInput::make('target_type')->disabled(),
                Forms\Components\TextInput::make('target_id')->disabled(),
                Forms\Components\TextInput::make('reason')->disabled(),
                Forms\Components\TextInput::make('ip_address')->disabled(),
                Forms\Components\DateTimePicker::make('created_at')->disabled(),
                Forms\Components\Textarea::make('before_payload')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                    ->rows(5)
                    ->disabled(),
                Forms\Components\Textarea::make('after_payload')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state)
                    ->rows(5)
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('action')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('actor.name')
                    ->label('Actor')
                    ->searchable()
                    ->default('System'),

                Tables\Columns\TextColumn::make('target_type')
                    ->label('Target Type')
                    ->formatStateUsing(fn ($state) => class_basename($state)),

                Tables\Columns\TextColumn::make('target_id')
                    ->label('Target ID')
                    ->fontFamily('mono')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reason')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}

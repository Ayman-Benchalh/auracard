<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\RoleName;
use App\Filament\Resources\MarketingAssetResource\Pages;
use App\Jobs\GenerateMarketingAssetJob;
use App\Models\MarketingAsset;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MarketingAssetResource extends Resource
{
    protected static ?string $model = MarketingAsset::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-sparkles';

    protected static string | \UnitEnum | null $navigationGroup = 'Marketing & Assets';

    protected static ?string $navigationLabel = 'AI Marketing Studio';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->hasRole(RoleName::SUPER_ADMIN) || $user?->hasRole(RoleName::ADMIN);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('title')
                    ->label('Asset Title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('Campaign / Placement Type')
                    ->options([
                        'hero' => 'Landing Page Hero Showcase',
                        'package' => 'Package Commercial Visual',
                        'profession' => 'Profession Identity Spotlight',
                        'social' => 'Social Media & Ads',
                        'banner' => 'Promo Banner',
                    ])
                    ->required()
                    ->default('hero'),

                Forms\Components\Select::make('provider')
                    ->label('AI Generation Provider')
                    ->options([
                        'mock' => 'Built-in Luxury Vector Engine (Offline/Deterministic)',
                        'openai' => 'OpenAI DALL-E 3',
                        'stability' => 'Stability AI SDXL',
                    ])
                    ->default(config('marketing.driver', 'mock'))
                    ->required(),

                Forms\Components\Textarea::make('prompt')
                    ->label('Creative Prompt')
                    ->helperText('Describe the marketing visual, materials, lighting, and aura aesthetics.')
                    ->rows(4)
                    ->required(),

                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->disabled()
                    ->default('draft'),

                Forms\Components\TextInput::make('file_path')
                    ->label('Asset URL / Path')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_path')
                    ->label('Preview')
                    ->circular(false)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover w-16 h-16 shadow']),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('provider')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'ready' => 'info',
                        'generating', 'queued' => 'warning',
                        'rejected', 'failed' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),

                Action::make('generateAsset')
                    ->label('Generate')
                    ->icon('heroicon-o-bolt')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (MarketingAsset $record): bool => in_array($record->status, ['draft', 'failed', 'ready', 'rejected'], true))
                    ->action(function (MarketingAsset $record): void {
                        $record->update(['status' => 'queued']);
                        GenerateMarketingAssetJob::dispatch($record);

                        Notification::make()
                            ->title('Generation Dispatched')
                            ->body("Queued background generation for '{$record->title}'.")
                            ->info()
                            ->send();
                    }),

                Action::make('approveAsset')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (MarketingAsset $record): bool => $record->status === 'ready')
                    ->action(function (MarketingAsset $record): void {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Asset Approved')
                            ->body('This marketing asset is now approved for landing and promotional usage.')
                            ->success()
                            ->send();
                    }),

                Action::make('rejectAsset')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (MarketingAsset $record): bool => $record->status === 'ready')
                    ->action(function (MarketingAsset $record): void {
                        $record->update(['status' => 'rejected']);

                        Notification::make()
                            ->title('Asset Rejected')
                            ->body('Asset has been marked as rejected.')
                            ->danger()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingAssets::route('/'),
            'create' => Pages\CreateMarketingAsset::route('/create'),
            'view' => Pages\ViewMarketingAsset::route('/{record}'),
        ];
    }
}

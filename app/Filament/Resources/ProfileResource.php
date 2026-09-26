<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\Profile\SuspendProfileAdminAction;
use App\Enums\ProfileStatus;
use App\Filament\Resources\ProfileResource\Pages;
use App\Models\Profile;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ProfileResource extends Resource
{
    protected static ?string $model = Profile::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';

    protected static string | \UnitEnum | null $navigationGroup = 'Digital Profiles';

    protected static ?string $navigationLabel = 'User Profiles';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('profiles.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Customer-driven creation bound to physical cards
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('display_name')
                    ->disabled(),

                Forms\Components\TextInput::make('slug')
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->options([
                        ProfileStatus::Draft->value => 'Draft',
                        ProfileStatus::Published->value => 'Published',
                        ProfileStatus::Suspended->value => 'Suspended',
                        ProfileStatus::Archived->value => 'Archived',
                    ])
                    ->disabled(),

                Forms\Components\TextInput::make('user.name')
                    ->label('Owner Name')
                    ->disabled(),

                Forms\Components\TextInput::make('user.email')
                    ->label('Owner Email')
                    ->disabled(),

                Forms\Components\TextInput::make('headline')
                    ->disabled(),

                Forms\Components\Textarea::make('bio')
                    ->disabled(),

                Forms\Components\TextInput::make('version_counter')
                    ->label('Version Count')
                    ->disabled(),

                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Last Published At')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->badge()
                    ->color('gray')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Owner')
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (ProfileStatus $state) => $state->color())
                    ->formatStateUsing(fn (ProfileStatus $state) => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('version_counter')
                    ->label('Versions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cardEntitlement.currentCard.serial_number')
                    ->label('Card Serial')
                    ->searchable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        ProfileStatus::Draft->value => 'Draft',
                        ProfileStatus::Published->value => 'Published',
                        ProfileStatus::Suspended->value => 'Suspended',
                        ProfileStatus::Archived->value => 'Archived',
                    ]),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('danger')
                    ->visible(fn (Profile $record) => $record->status !== ProfileStatus::Suspended && (auth()->user()?->hasPermission('profiles.suspend') ?? false))
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\TextInput::make('reason')
                            ->label('Suspension Reason')
                            ->required(),
                    ])
                    ->action(function (Profile $record, array $data, SuspendProfileAdminAction $action) {
                        $action->execute(auth()->user(), $record, true, $data['reason']);
                        Notification::make()
                            ->title('Profile Suspended')
                            ->body("Profile {$record->slug} has been suspended.")
                            ->danger()
                            ->send();
                    }),

                Action::make('unsuspend')
                    ->label('Unsuspend')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->visible(fn (Profile $record) => $record->status === ProfileStatus::Suspended && (auth()->user()?->hasPermission('profiles.suspend') ?? false))
                    ->requiresConfirmation()
                    ->action(function (Profile $record, SuspendProfileAdminAction $action) {
                        $action->execute(auth()->user(), $record, false);
                        Notification::make()
                            ->title('Profile Unsuspended')
                            ->body("Profile {$record->slug} has been restored.")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProfiles::route('/'),
            'view' => Pages\ViewProfile::route('/{record}'),
        ];
    }
}

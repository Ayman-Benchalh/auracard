<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Actions\Commercial\ApprovePaymentRequestAction;
use App\Actions\Commercial\RejectPaymentRequestAction;
use App\Enums\PaymentRequestStatus;
use App\Enums\RoleName;
use App\Filament\Resources\PaymentRequestResource\Pages;
use App\Models\PaymentRequest;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentRequestResource extends Resource
{
    protected static ?string $model = PaymentRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static string | \UnitEnum | null $navigationGroup = 'Commercial & Finance';

    protected static ?string $navigationLabel = 'Payment Requests';

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        return $user?->hasPermission('payments.review') || $user?->hasRole(RoleName::SUPER_ADMIN);
    }

    public static function canCreate(): bool
    {
        return false; // Customer-submitted via checkout
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('reference_number')->disabled(),
                Forms\Components\TextInput::make('user.name')->label('Customer Name')->disabled(),
                Forms\Components\TextInput::make('user.email')->label('Customer Email')->disabled(),
                Forms\Components\TextInput::make('plan.name')->label('Plan')->disabled(),
                Forms\Components\TextInput::make('amount')->disabled(),
                Forms\Components\TextInput::make('currency')->disabled(),
                Forms\Components\TextInput::make('payment_method')->disabled(),
                Forms\Components\TextInput::make('status')->disabled(),
                Forms\Components\Textarea::make('customer_notes')->disabled(),
                Forms\Components\Textarea::make('rejection_reason')->disabled(),
                Forms\Components\DateTimePicker::make('created_at')->disabled(),
                Forms\Components\DateTimePicker::make('processed_at')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount (MAD)')
                    ->money('MAD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (PaymentRequestStatus $state) => match ($state) {
                        PaymentRequestStatus::APPROVED => 'success',
                        PaymentRequestStatus::PENDING => 'warning',
                        PaymentRequestStatus::REJECTED => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (PaymentRequestStatus $state) => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        PaymentRequestStatus::PENDING->value => 'Pending Review',
                        PaymentRequestStatus::APPROVED->value => 'Approved',
                        PaymentRequestStatus::REJECTED->value => 'Rejected',
                    ]),
            ])
            ->actions([
                ViewAction::make(),

                Action::make('download_proof')
                    ->label('Proof')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn (PaymentRequest $record) => route('payment-requests.proof', $record))
                    ->openUrlInNewTab(),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (PaymentRequest $record) => $record->status === PaymentRequestStatus::PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Approve Payment & Activate Plan')
                    ->modalDescription('Approving this payment request will immediately activate the user subscription, update credits, and notify the customer.')
                    ->action(function (PaymentRequest $record, ApprovePaymentRequestAction $action) {
                        $admin = auth()->user();
                        $action->execute($admin, $record);

                        Notification::make()
                            ->title('Payment Approved')
                            ->body("Payment request #{$record->reference_number} approved and subscription activated.")
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PaymentRequest $record) => $record->status === PaymentRequestStatus::PENDING)
                    ->requiresConfirmation()
                    ->modalHeading('Reject Payment Request')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason for Rejection (Visible to Customer)')
                            ->required()
                            ->placeholder('e.g. Unreadable receipt image, amount does not match plan price, transaction not found...'),
                    ])
                    ->action(function (PaymentRequest $record, array $data, RejectPaymentRequestAction $action) {
                        $admin = auth()->user();
                        $action->execute($admin, $record, $data['rejection_reason']);

                        Notification::make()
                            ->title('Payment Rejected')
                            ->body("Payment request #{$record->reference_number} was rejected.")
                            ->warning()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentRequests::route('/'),
            'view' => Pages\ViewPaymentRequest::route('/{record}'),
        ];
    }
}

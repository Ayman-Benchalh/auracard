<?php

namespace App\Models;

use App\Enums\CardEntitlementStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardEntitlement extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'card_entitlements';

    protected $fillable = [
        'source_nfc_card_id',
        'current_nfc_card_id',
        'user_id',
        'profile_id',
        'initial_credits_amount',
        'is_initial_credits_granted',
        'initial_credits_granted_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'initial_credits_amount' => 'integer',
            'is_initial_credits_granted' => 'boolean',
            'initial_credits_granted_at' => 'datetime',
            'status' => CardEntitlementStatus::class,
        ];
    }

    public function sourceCard(): BelongsTo
    {
        return $this->belongsTo(NfcCard::class, 'source_nfc_card_id');
    }

    public function currentCard(): BelongsTo
    {
        return $this->belongsTo(NfcCard::class, 'current_nfc_card_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(CardClaim::class, 'card_entitlement_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(CardTransfer::class, 'card_entitlement_id');
    }

    public function editCreditTransactions(): HasMany
    {
        return $this->hasMany(EditCreditTransaction::class, 'card_entitlement_id');
    }

    public function getEditCreditBalanceAttribute(): int
    {
        return (int) $this->editCreditTransactions()->sum('amount');
    }

    public function isActive(): bool
    {
        return $this->status === CardEntitlementStatus::ACTIVE;
    }
}

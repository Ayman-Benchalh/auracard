<?php

namespace App\Models;

use App\Enums\CardTransferType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardTransfer extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'card_transfers';

    protected $fillable = [
        'nfc_card_id',
        'card_entitlement_id',
        'from_user_id',
        'to_user_id',
        'authorized_by_user_id',
        'reason',
        'transfer_type',
        'transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'transfer_type' => CardTransferType::class,
            'transferred_at' => 'datetime',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(NfcCard::class, 'nfc_card_id');
    }

    public function entitlement(): BelongsTo
    {
        return $this->belongsTo(CardEntitlement::class, 'card_entitlement_id');
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function authorizedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by_user_id');
    }
}

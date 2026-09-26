<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardClaim extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'card_claims';

    protected $fillable = [
        'nfc_card_id',
        'user_id',
        'card_entitlement_id',
        'visitor_identifier',
        'user_agent',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
        ];
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(NfcCard::class, 'nfc_card_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function entitlement(): BelongsTo
    {
        return $this->belongsTo(CardEntitlement::class, 'card_entitlement_id');
    }
}

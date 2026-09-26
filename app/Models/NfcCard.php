<?php

namespace App\Models;

use App\Enums\CardStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class NfcCard extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'nfc_cards';

    protected $fillable = [
        'batch_id',
        'serial_number',
        'public_token_hash',
        'claim_code_hash',
        'status',
        'hardware_origin',
        'card_type',
        'user_id',
        'profile_id',
        'sold_at',
        'claimed_at',
        'activated_at',
        'suspended_at',
        'disabled_at',
        'replaced_by_card_id',
    ];

    protected $hidden = [
        'public_token_hash',
        'claim_code_hash',
    ];

    protected function casts(): array
    {
        return [
            'status' => CardStatus::class,
            'sold_at' => 'datetime',
            'claimed_at' => 'datetime',
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(NfcCardBatch::class, 'batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replacedByCard(): BelongsTo
    {
        return $this->belongsTo(NfcCard::class, 'replaced_by_card_id');
    }

    public function sourceEntitlement(): HasOne
    {
        return $this->hasOne(CardEntitlement::class, 'source_nfc_card_id');
    }

    public function currentEntitlement(): HasOne
    {
        return $this->hasOne(CardEntitlement::class, 'current_nfc_card_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(CardClaim::class, 'nfc_card_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(CardTransfer::class, 'nfc_card_id');
    }

    public function isClaimable(): bool
    {
        return $this->status->isClaimable() && ! empty($this->claim_code_hash);
    }

    public function isActive(): bool
    {
        return $this->status === CardStatus::ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === CardStatus::SUSPENDED;
    }

    public function isLost(): bool
    {
        return $this->status === CardStatus::LOST;
    }

    public function isReplaced(): bool
    {
        return $this->status === CardStatus::REPLACED;
    }

    public function isDisabled(): bool
    {
        return $this->status === CardStatus::DISABLED;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CardStatus::ACTIVE->value);
    }

    public function scopeUnclaimed(Builder $query): Builder
    {
        return $query->whereIn('status', [
            CardStatus::INVENTORY->value,
            CardStatus::SOLD->value,
            CardStatus::UNCLAIMED->value,
        ])->whereNull('user_id');
    }

    /**
     * Generate cryptographically secure URL-safe raw public token (NOT stored in database)
     */
    public static function generateRawPublicToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Hash raw public token with SHA-256 for database lookup
     */
    public static function hashPublicToken(string $rawToken): string
    {
        return hash('sha256', trim($rawToken));
    }

    /**
     * Generate high-entropy one-time claim code (e.g. AURA-XXXX-XXXX)
     */
    public static function generatePlainClaimCode(): string
    {
        $part1 = strtoupper(Str::random(4));
        $part2 = strtoupper(Str::random(4));
        return "AURA-{$part1}-{$part2}";
    }

    /**
     * Generate collision-resistant serial number
     */
    public static function generateSerialNumber(): string
    {
        $prefix = 'AC-';
        $maxTries = 10;

        for ($i = 0; $i < $maxTries; $i++) {
            $randomDigits = str_pad((string) random_int(1, 99999999), 8, '0', STR_PAD_LEFT);
            $serial = $prefix . $randomDigits;

            if (! self::where('serial_number', $serial)->exists()) {
                return $serial;
            }
        }

        return $prefix . strtoupper(Str::random(8));
    }
}

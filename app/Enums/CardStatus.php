<?php

namespace App\Enums;

enum CardStatus: string
{
    case INVENTORY = 'inventory';
    case RESERVED = 'reserved';
    case SOLD = 'sold';
    case UNCLAIMED = 'unclaimed';
    case CLAIMED = 'claimed';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case LOST = 'lost';
    case REPLACED = 'replaced';
    case DISABLED = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::INVENTORY => 'Inventory',
            self::RESERVED => 'Reserved',
            self::SOLD => 'Sold',
            self::UNCLAIMED => 'Unclaimed',
            self::CLAIMED => 'Claimed',
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::LOST => 'Lost',
            self::REPLACED => 'Replaced',
            self::DISABLED => 'Disabled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::INVENTORY => 'gray',
            self::RESERVED => 'info',
            self::SOLD => 'warning',
            self::UNCLAIMED => 'gray',
            self::CLAIMED => 'info',
            self::ACTIVE => 'success',
            self::SUSPENDED => 'warning',
            self::LOST => 'danger',
            self::REPLACED => 'danger',
            self::DISABLED => 'danger',
        };
    }

    /**
     * Determine if a card can be claimed by a customer
     */
    public function isClaimable(): bool
    {
        return in_array($this, [self::INVENTORY, self::SOLD, self::UNCLAIMED], true);
    }

    /**
     * Determine if a card is currently resolving public profile routing
     */
    public function isPubliclyResolvable(): bool
    {
        return in_array($this, [self::ACTIVE, self::CLAIMED], true);
    }
}

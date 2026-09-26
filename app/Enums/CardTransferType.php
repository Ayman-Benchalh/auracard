<?php

namespace App\Enums;

enum CardTransferType: string
{
    case OWNERSHIP_TRANSFER = 'ownership_transfer';
    case REPLACEMENT = 'replacement';
    case ADMIN_CORRECTION = 'admin_correction';

    public function label(): string
    {
        return match ($this) {
            self::OWNERSHIP_TRANSFER => 'Ownership Transfer',
            self::REPLACEMENT => 'Hardware Replacement',
            self::ADMIN_CORRECTION => 'Administrative Correction',
        };
    }
}

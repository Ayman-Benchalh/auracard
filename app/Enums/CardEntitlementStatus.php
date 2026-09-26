<?php

namespace App\Enums;

enum CardEntitlementStatus: string
{
    case ACTIVE = 'active';
    case TRANSFERRED = 'transferred';
    case REVOKED = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::TRANSFERRED => 'Transferred',
            self::REVOKED => 'Revoked',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::TRANSFERRED => 'warning',
            self::REVOKED => 'danger',
        };
    }
}

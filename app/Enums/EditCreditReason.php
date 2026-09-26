<?php

namespace App\Enums;

enum EditCreditReason: string
{
    case INITIAL_GRANT = 'initial_grant';
    case PROFILE_PUBLISH = 'profile_publish';
    case ADMIN_GRANT = 'admin_grant';
    case ADMIN_CORRECTION = 'admin_correction';
    case PROMOTION = 'promotion';

    public const InitialGrant = self::INITIAL_GRANT;
    public const ProfilePublish = self::PROFILE_PUBLISH;
    public const AdminGrant = self::ADMIN_GRANT;
    public const AdminCorrection = self::ADMIN_CORRECTION;
    public const Promotion = self::PROMOTION;

    public function label(): string
    {
        return match ($this) {
            self::INITIAL_GRANT => 'Physical Card Initial Entitlement (+2 Free Credits)',
            self::PROFILE_PUBLISH => 'Profile Publication (-1 Credit)',
            self::ADMIN_GRANT => 'Administrative Credit Allocation',
            self::ADMIN_CORRECTION => 'Administrative Ledger Correction',
            self::PROMOTION => 'Promotional Credit Bonus',
        };
    }
}

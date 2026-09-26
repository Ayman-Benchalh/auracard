<?php

namespace App\Enums;

enum RoleName: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case SUPPORT = 'support';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::ADMIN => 'Administrator',
            self::SUPPORT => 'Customer Support',
            self::CUSTOMER => 'Customer',
        };
    }

    public function canAccessAdminPanel(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN, self::SUPPORT], true);
    }
}

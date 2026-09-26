<?php

namespace App\Enums;

enum PermissionGroup: string
{
    case USERS = 'users';
    case PROFILES = 'profiles';
    case CARDS = 'cards';
    case SUBSCRIPTIONS = 'subscriptions';
    case PAYMENTS = 'payments';
    case SETTINGS = 'settings';
    case AUDIT_LOGS = 'audit_logs';

    public function label(): string
    {
        return match ($this) {
            self::USERS => 'Users Management',
            self::PROFILES => 'Profiles Management',
            self::CARDS => 'NFC Cards Management',
            self::SUBSCRIPTIONS => 'Subscriptions Management',
            self::PAYMENTS => 'Payments & Proofs',
            self::SETTINGS => 'Platform Settings',
            self::AUDIT_LOGS => 'Audit & Security Logs',
        };
    }
}

<?php

namespace App\Enums;

enum ProfileStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case SUSPENDED = 'suspended';
    case ARCHIVED = 'archived';

    public const Draft = self::DRAFT;
    public const Published = self::PUBLISHED;
    public const Suspended = self::SUSPENDED;
    public const Archived = self::ARCHIVED;

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::SUSPENDED => 'Suspended',
            self::ARCHIVED => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PUBLISHED => 'success',
            self::SUSPENDED => 'danger',
            self::ARCHIVED => 'warning',
        };
    }

    public function isPubliclyResolvable(): bool
    {
        return $this === self::PUBLISHED;
    }
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentRequestStatus: string
{
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function isFinal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::CANCELLED], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Verification',
            self::UNDER_REVIEW => 'Under Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
        };
    }
}

<?php

namespace App\Services;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class PrivacyIdentifierService
{
    /**
     * Generate a privacy-safe, rotating time-bounded HMAC visitor identifier.
     * Never stores or returns raw IP addresses.
     *
     * @param string|null $ipAddress Client IP address
     * @param CarbonInterface|null $date Reference date for rotation (defaults to now)
     * @return string Truncated 32-char HMAC token
     */
    public function generateIdentifier(?string $ipAddress, ?CarbonInterface $date = null): string
    {
        if (empty($ipAddress)) {
            return 'anon-' . bin2hex(random_bytes(14));
        }

        $referenceDate = $date ?? Carbon::now();
        $dateWindow = $referenceDate->format('Y-m-d'); // Daily rotating window

        $appKey = config('app.key') ?: 'auracard-static-fallback-secret-2026';

        // Salted daily HMAC
        $hmac = hash_hmac('sha256', "{$ipAddress}|{$dateWindow}", $appKey);

        return substr($hmac, 0, 32);
    }
}

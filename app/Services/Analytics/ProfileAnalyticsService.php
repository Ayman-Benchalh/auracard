<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\Profile;
use App\Models\ProfileAnalyticsEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileAnalyticsService
{
    /**
     * Record a privacy-preserving analytics interaction.
     * Raw IP is never stored. A daily salt prevents cross-day visitor profiling.
     */
    public function recordEvent(
        Profile $profile,
        string $eventType,
        Request $request,
        ?string $targetUrl = null,
        array $metadata = []
    ): void {
        $ip = $request->ip() ?? '127.0.0.1';
        $ua = $request->userAgent() ?? 'unknown';
        $dailySalt = (string) config('app.key') . date('Y-m-d');
        $visitorHash = hash_hmac('sha256', "{$ip}|{$ua}", $dailySalt);

        ProfileAnalyticsEvent::create([
            'profile_id' => $profile->id,
            'event_type' => $eventType,
            'visitor_hash' => $visitorHash,
            'target_url' => $targetUrl,
            'metadata' => array_merge($metadata, [
                'referer' => $request->header('referer'),
            ]),
            'created_at' => now(),
        ]);
    }

    /**
     * Aggregate real telemetry counts for customer dashboard.
     *
     * @param Profile $profile
     * @param int $days
     * @return array{
     *     total_views: int,
     *     nfc_taps: int,
     *     qr_scans: int,
     *     vcard_downloads: int,
     *     action_clicks: int,
     *     unique_visitors: int,
     *     daily_stats: array<string, int>
     * }
     */
    public function getProfileMetrics(Profile $profile, int $days = 30): array
    {
        $since = now()->subDays($days);

        $events = ProfileAnalyticsEvent::where('profile_id', $profile->id)
            ->where('created_at', '>=', $since)
            ->get();

        $totalViews = $events->where('event_type', 'view')->count();
        $nfcTaps = $events->where('event_type', 'nfc_tap')->count();
        $qrScans = $events->where('event_type', 'qr_scan')->count();
        $vcardDownloads = $events->where('event_type', 'vcard_download')->count();
        $actionClicks = $events->where('event_type', 'link_click')->count();
        $uniqueVisitors = $events->pluck('visitor_hash')->filter()->unique()->count();

        // Daily breakdown for charts
        $dailyStats = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dateKey = now()->subDays($i)->format('Y-m-d');
            $dailyStats[$dateKey] = 0;
        }

        foreach ($events as $event) {
            $date = $event->created_at->format('Y-m-d');
            if (isset($dailyStats[$date])) {
                $dailyStats[$date]++;
            }
        }

        return [
            'total_views' => $totalViews,
            'nfc_taps' => $nfcTaps,
            'qr_scans' => $qrScans,
            'vcard_downloads' => $vcardDownloads,
            'action_clicks' => $actionClicks,
            'unique_visitors' => $uniqueVisitors,
            'daily_stats' => $dailyStats,
        ];
    }
}

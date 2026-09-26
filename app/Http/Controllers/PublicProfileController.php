<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ProfileStatus;
use App\Models\Profile;
use App\Services\Analytics\ProfileAnalyticsService;
use App\Services\QrCode\ProfileQrCodeService;
use App\Services\VCard\VCard4Generator;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicProfileController extends Controller
{
    public function __construct(
        protected VCard4Generator $vcardGenerator,
        protected ProfileQrCodeService $qrCodeService,
        protected ProfileAnalyticsService $analyticsService
    ) {}

    /**
     * Display the public published profile snapshot.
     * Never serves draft changes. Only serves immutable published snapshot.
     */
    public function show(Request $request, string $slug): Response
    {
        $profile = Profile::where('slug', $slug)
            ->with(['publishedVersion', 'professionType', 'template', 'theme'])
            ->first();

        if (! $profile || $profile->status !== ProfileStatus::Published || ! $profile->publishedVersion) {
            abort(404, __('This digital card profile is not available or is currently in draft mode.'));
        }

        // Record privacy-safe analytics
        $this->analyticsService->recordEvent($profile, 'view', $request);

        $snapshot = $profile->publishedVersion->snapshot;

        // Meta tags for rich previews
        $meta = [
            'title' => ($snapshot['display_name'] ?? $profile->display_name) . ($snapshot['headline'] ? ' - ' . $snapshot['headline'] : ' | AURA CARD'),
            'description' => $snapshot['bio'] ?? ($snapshot['headline'] ?? 'Connect with ' . $snapshot['display_name'] . ' via AURA CARD.'),
            'image' => $snapshot['avatar_url'] ?? asset('favicon.ico'),
            'canonical_url' => url("/p/{$profile->slug}"),
            'author' => $snapshot['display_name'] ?? $profile->display_name,
        ];

        return Inertia::render('Public/ProfileView', [
            'profile' => [
                'id' => $profile->id,
                'slug' => $profile->slug,
                'version' => $profile->publishedVersion->version_number,
                'published_at' => $profile->published_at?->toISOString(),
                'display_name' => $snapshot['display_name'] ?? $profile->display_name,
                'headline' => $snapshot['headline'] ?? null,
                'bio' => $snapshot['bio'] ?? null,
                'company' => $snapshot['company'] ?? null,
                'job_title' => $snapshot['job_title'] ?? null,
                'location' => $snapshot['location'] ?? null,
                'avatar_url' => $snapshot['avatar_url'] ?? null,
                'cover_url' => $snapshot['cover_url'] ?? null,
                'primary_color' => $snapshot['primary_color'] ?? '#1e293b',
                'secondary_color' => $snapshot['secondary_color'] ?? '#3b82f6',
                'font_family' => $snapshot['font_family'] ?? 'Inter',
                'custom_css' => $snapshot['custom_css'] ?? null,
                'profession' => $profile->professionType ? [
                    'id' => $profile->professionType->id,
                    'slug' => $profile->professionType->slug,
                    'name' => $profile->professionType->name,
                    'icon' => $profile->professionType->icon,
                ] : null,
                'template' => $profile->template ? [
                    'id' => $profile->template->id,
                    'slug' => $profile->template->slug,
                    'name' => $profile->template->name,
                ] : null,
                'theme' => $profile->theme ? [
                    'id' => $profile->theme->id,
                    'slug' => $profile->theme->slug,
                    'name' => $profile->theme->name,
                    'tokens' => $profile->theme->tokens,
                ] : null,
                'blocks' => $snapshot['blocks'] ?? [],
            ],
            'meta' => $meta,
            'vcard_url' => url("/p/{$profile->slug}/vcard"),
            'qr_url' => url("/p/{$profile->slug}/qr"),
        ]);
    }

    /**
     * Download RFC 6350 vCard 4.0 representation.
     */
    public function downloadVCard(Request $request, string $slug): HttpResponse
    {
        $profile = Profile::where('slug', $slug)
            ->where('status', ProfileStatus::Published)
            ->with(['publishedVersion'])
            ->firstOrFail();

        $this->analyticsService->recordEvent($profile, 'vcard_download', $request);

        $vcard = $profile->vcard_cache;
        if (! $vcard) {
            $blocks = $profile->blocks()->where('is_enabled', true)->orderBy('sort_order')->get();
            $vcard = $this->vcardGenerator->generate($profile, $blocks);
        }

        $filename = "{$profile->slug}.vcf";

        return response($vcard, 200, [
            'Content-Type' => 'text/vcard; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Serve dynamic high-contrast SVG QR Code pointing to canonical profile.
     */
    public function showQr(Request $request, string $slug): HttpResponse
    {
        $profile = Profile::where('slug', $slug)
            ->where('status', ProfileStatus::Published)
            ->firstOrFail();

        $this->analyticsService->recordEvent($profile, 'qr_scan', $request);

        $profileUrl = url("/p/{$profile->slug}");
        $svg = $this->qrCodeService->generateSvg($profileUrl, 280);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}

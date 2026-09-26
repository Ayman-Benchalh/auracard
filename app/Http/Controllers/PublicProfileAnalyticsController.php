<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\Analytics\ProfileAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProfileAnalyticsController extends Controller
{
    public function __construct(
        protected ProfileAnalyticsService $analyticsService
    ) {}

    /**
     * Track client-side interaction events (button clicks, links, shares).
     */
    public function track(Request $request, string $slug): JsonResponse
    {
        $profile = Profile::where('slug', $slug)
            ->where('status', \App\Enums\ProfileStatus::Published)
            ->firstOrFail();

        $validated = $request->validate([
            'event_type' => ['required', 'string', 'in:link_click,vcard_download,qr_scan,share_click'],
            'target_url' => ['nullable', 'string', 'max:500'],
            'metadata' => ['nullable', 'array'],
        ]);

        $this->analyticsService->recordEvent(
            $profile,
            $validated['event_type'],
            $request,
            $validated['target_url'] ?? null,
            $validated['metadata'] ?? []
        );

        return response()->json(['success' => true]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\ProfileLead;
use App\Notifications\ProfileLeadReceivedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicProfileLeadController extends Controller
{
    /**
     * Submit an inquiry via profile lead capture block.
     * Enforces rate limiting, anti-spam honeypot, and zero owner email exposure.
     */
    public function store(Request $request, string $slug): JsonResponse|RedirectResponse
    {
        $profile = Profile::where('slug', $slug)
            ->where('status', \App\Enums\ProfileStatus::Published)
            ->firstOrFail();

        // Honeypot anti-spam verification
        if (! empty($request->input('_hp_time')) || ! empty($request->input('website_trap'))) {
            return response()->json(['success' => true]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:2000'],
            'form_data' => ['nullable', 'array'],
        ]);

        $lead = ProfileLead::create([
            'profile_id' => $profile->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'message' => $validated['message'] ?? null,
            'form_data' => $validated['form_data'] ?? null,
            'is_read' => false,
        ]);

        // Notify profile owner safely without exposing their email to the public form
        if ($profile->user) {
            $profile->user->notify(new ProfileLeadReceivedNotification($profile, $lead));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Your message has been sent successfully.'),
            ]);
        }

        return back()->with('success', __('Your message has been sent successfully.'));
    }
}

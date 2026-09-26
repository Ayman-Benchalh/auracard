<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the customer dashboard with foundation metrics and status.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $primaryCard = $user->cards()->with('currentEntitlement')->latest('claimed_at')->first();
        $cardSummary = $primaryCard ? [
            'id' => $primaryCard->id,
            'serial_number' => $primaryCard->serial_number,
            'status' => $primaryCard->status->value,
            'status_label' => $primaryCard->status->label(),
            'status_color' => $primaryCard->status->color(),
            'card_type' => $primaryCard->card_type,
            'claimed_at' => $primaryCard->claimed_at?->toISOString(),
            'has_entitlement' => $primaryCard->currentEntitlement !== null,
            'total_cards' => $user->cards()->count(),
        ] : null;

        $profile = $user->primaryProfile ?? $user->profiles()->first();
        $profileSummary = $profile ? [
            'id' => $profile->id,
            'slug' => $profile->slug,
            'display_name' => $profile->display_name,
            'status' => $profile->status->value,
            'current_version' => $profile->current_version,
            'published_at' => $profile->published_at?->toISOString(),
            'public_url' => url("/p/{$profile->slug}"),
        ] : null;

        $editCreditsBalance = $user->edit_credit_balance;

        return Inertia::render('Customer/Dashboard', [
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->account_status->value,
                'is_verified' => $user->hasVerifiedEmail(),
                'locale' => $user->locale,
                'created_at' => $user->created_at->toISOString(),
            ],
            'cardSummary' => $cardSummary,
            'profileSummary' => $profileSummary,
            'subscriptionSummary' => null,
            'editCreditsBalance' => $editCreditsBalance,
        ]);
    }
}

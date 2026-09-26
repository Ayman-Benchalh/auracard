<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Cards\ClaimCardAction;
use App\Actions\Cards\MarkCardLostAction;
use App\Actions\Cards\SuspendCardAction;
use App\Enums\CardStatus;
use App\Http\Controllers\Controller;
use App\Models\NfcCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NfcCardController extends Controller
{
    /**
     * Display the "My NFC Card" customer page
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $cards = $user->cards()
            ->with(['currentEntitlement'])
            ->latest('created_at')
            ->get()
            ->map(function (NfcCard $card) {
                return [
                    'id' => $card->id,
                    'serial_number' => $card->serial_number,
                    'status' => $card->status->value,
                    'status_label' => $card->status->label(),
                    'status_color' => $card->status->color(),
                    'card_type' => $card->card_type,
                    'claimed_at' => $card->claimed_at?->toISOString(),
                    'activated_at' => $card->activated_at?->toISOString(),
                    'entitlement' => $card->currentEntitlement ? [
                        'id' => $card->currentEntitlement->id,
                        'initial_credits_amount' => $card->currentEntitlement->initial_credits_amount,
                        'is_initial_credits_granted' => $card->currentEntitlement->is_initial_credits_granted,
                        'status' => $card->currentEntitlement->status->value,
                        'status_label' => $card->currentEntitlement->status->label(),
                    ] : null,
                ];
            });

        return Inertia::render('Customer/Cards/MyNfcCard', [
            'cards' => $cards,
        ]);
    }

    /**
     * Display the NFC card claim screen
     */
    public function claimView(Request $request): Response
    {
        return Inertia::render('Customer/Cards/ClaimCard', [
            'initialToken' => $request->query('token', ''),
        ]);
    }

    /**
     * Process an atomic card claim
     */
    public function claim(Request $request, ClaimCardAction $claimAction): RedirectResponse
    {
        $validated = $request->validate([
            'raw_token' => ['required', 'string', 'min:32', 'max:128'],
            'claim_code' => ['required', 'string', 'min:6', 'max:32'],
        ]);

        $card = $claimAction->execute(
            user: $request->user(),
            rawPublicToken: $validated['raw_token'],
            claimCode: $validated['claim_code'],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->route('cards.claim-success')
            ->with('claimed_serial', $card->serial_number);
    }

    /**
     * Display the claim success experience
     */
    public function claimSuccess(Request $request): Response
    {
        return Inertia::render('Customer/Cards/ClaimSuccess', [
            'serialNumber' => session('claimed_serial'),
        ]);
    }

    /**
     * Pause or resume a card (Active <-> Suspended)
     */
    public function toggleStatus(
        Request $request,
        NfcCard $card,
        SuspendCardAction $suspendAction
    ): RedirectResponse {
        if ($card->user_id !== $request->user()->id) {
            abort(403, __('Unauthorized'));
        }

        if ($card->status === CardStatus::ACTIVE) {
            $suspendAction->execute($card, true, 'Paused by customer', $request->user());
            $message = __('Card has been paused. Tapping this card will not route to your profile.');
        } elseif ($card->status === CardStatus::SUSPENDED) {
            $suspendAction->execute($card, false, 'Reactivated by customer', $request->user());
            $message = __('Card has been reactivated and is actively routing.');
        } else {
            return back()->with('error', __('Cannot toggle status for this card.'));
        }

        return back()->with('success', $message);
    }

    /**
     * Report card as lost
     */
    public function reportLost(
        Request $request,
        NfcCard $card,
        MarkCardLostAction $lostAction
    ): RedirectResponse {
        if ($card->user_id !== $request->user()->id) {
            abort(403, __('Unauthorized'));
        }

        if (in_array($card->status, [CardStatus::LOST, CardStatus::REPLACED, CardStatus::DISABLED], true)) {
            return back()->with('error', __('Card is already inactive.'));
        }

        $lostAction->execute($card, 'Reported lost by customer', $request->user());

        return back()->with('success', __('Your card has been marked as lost. Public NFC routing has been deactivated.'));
    }
}

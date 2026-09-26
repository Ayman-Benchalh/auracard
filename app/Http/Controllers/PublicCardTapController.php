<?php

namespace App\Http\Controllers;

use App\Actions\Cards\ResolveNfcCardAction;
use App\Enums\CardStatus;
use App\Services\Analytics\ProfileAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicCardTapController extends Controller
{
    public function __construct(
        protected ResolveNfcCardAction $resolveCardAction,
        protected ProfileAnalyticsService $analyticsService
    ) {}

    /**
     * Handle physical NFC card tap or QR scan: GET /c/{token}
     * Receives raw public token, hashes via SHA-256 and resolves card state.
     */
    public function show(Request $request, string $token): Response|SymfonyResponse
    {
        try {
            $card = $this->resolveCardAction->execute($token);
        } catch (NotFoundHttpException $e) {
            abort(404, __('Unrecognized or invalid AURA CARD NFC token.'));
        }

        // 1. Unclaimed / Inventory / Sold: Direct to activation
        if (in_array($card->status, [CardStatus::INVENTORY, CardStatus::SOLD, CardStatus::UNCLAIMED], true)) {
            if ($request->user()) {
                return redirect()->route('cards.claim', ['token' => $token])
                    ->with('info', __('Physical NFC card detected! Enter your activation code to link it.'));
            }

            return Inertia::render('Public/CardTap', [
                'card' => [
                    'serial_number' => $card->serial_number,
                    'status' => 'unclaimed',
                    'card_type' => $card->card_type,
                    'raw_token' => $token,
                ],
                'owner' => null,
            ]);
        }

        // 2. Suspended Card
        if ($card->status === CardStatus::SUSPENDED) {
            return Inertia::render('Public/CardTap', [
                'card' => [
                    'serial_number' => $card->serial_number,
                    'status' => 'suspended',
                    'card_type' => $card->card_type,
                ],
                'owner' => null,
            ]);
        }

        // 3. Lost Card
        if ($card->status === CardStatus::LOST) {
            return Inertia::render('Public/CardTap', [
                'card' => [
                    'serial_number' => $card->serial_number,
                    'status' => 'lost',
                    'card_type' => $card->card_type,
                ],
                'owner' => null,
            ]);
        }

        // 4. Replaced Card
        if ($card->status === CardStatus::REPLACED) {
            return Inertia::render('Public/CardTap', [
                'card' => [
                    'serial_number' => $card->serial_number,
                    'status' => 'replaced',
                    'card_type' => $card->card_type,
                ],
                'owner' => null,
            ]);
        }

        // 5. Disabled Card
        if ($card->status === CardStatus::DISABLED) {
            return Inertia::render('Public/CardTap', [
                'card' => [
                    'serial_number' => $card->serial_number,
                    'status' => 'disabled',
                    'card_type' => $card->card_type,
                ],
                'owner' => null,
            ]);
        }

        // 6. Active / Claimed Card
        $entitlement = $card->currentEntitlement;
        $profile = $entitlement?->profile ?? ($entitlement ? \App\Models\Profile::where('card_entitlement_id', $entitlement->id)->first() : null);

        if ($profile) {
            $this->analyticsService->recordEvent($profile, 'nfc_tap', $request, null, [
                'serial_number' => $card->serial_number,
                'card_type' => $card->card_type,
            ]);
        }

        if ($profile && $profile->status === \App\Enums\ProfileStatus::Published) {
            return redirect()->route('profile.public.show', ['slug' => $profile->slug], 302);
        }

        return Inertia::render('Public/CardTap', [
            'card' => [
                'serial_number' => $card->serial_number,
                'status' => 'active',
                'card_type' => $card->card_type,
                'activated_at' => $card->activated_at?->toISOString(),
                'has_profile' => $profile !== null,
                'profile_status' => $profile?->status->value,
            ],
            'owner' => [
                'name' => $card->user?->name,
                'email' => $card->user?->email,
            ],
        ]);
    }
}

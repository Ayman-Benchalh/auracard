<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Actions\Commercial\CreateCommercialOrderAction;
use App\Actions\Commercial\SubmitOrderPaymentProofAction;
use App\Http\Controllers\Controller;
use App\Models\CardDesign;
use App\Models\CommercialPackage;
use App\Models\Order;
use App\Models\PaymentRequest;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommercialOrderController extends Controller
{
    public function __construct(
        protected CreateCommercialOrderAction $createOrderAction,
        protected SubmitOrderPaymentProofAction $submitProofAction
    ) {}

    /**
     * Display the dedicated Commercial Package checkout page with interactive card design picker.
     */
    public function checkout(Request $request, CommercialPackage $package): Response
    {
        if (! $package->is_active) {
            abort(404, __('This commercial package is currently unavailable.'));
        }

        $user = $request->user();
        $cardDesigns = CardDesign::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $paymentSettings = PaymentSetting::where('is_enabled', true)->get();

        return Inertia::render('Customer/Commercial/PackageCheckout', [
            'package' => [
                'id' => $package->id,
                'key' => $package->key,
                'name' => $package->name,
                'description' => $package->description,
                'price' => (float) $package->price,
                'currency' => $package->currency,
                'duration_months' => $package->duration_months,
                'physical_cards_included' => $package->physical_cards_included,
                'profiles_included' => $package->profiles_included,
                'edit_credits_per_profile' => $package->edit_credits_per_profile,
                'allows_customer_owned_card' => $package->allows_customer_owned_card,
                'requires_customer_owned_card' => $package->requires_customer_owned_card,
                'features_list' => $package->features_list,
                'is_featured' => $package->is_featured,
            ],
            'cardDesigns' => $cardDesigns->map(fn ($d) => [
                'id' => $d->id,
                'key' => $d->key,
                'name' => $d->name,
                'description' => $d->description,
                'material' => $d->material,
                'color' => $d->color,
                'preview_image' => $d->preview_image,
            ]),
            'paymentSettings' => $paymentSettings->map(fn ($s) => [
                'id' => $s->id,
                'key' => $s->key,
                'title' => $s->title,
                'instructions' => $s->instructions,
                'bank_name' => $s->bank_name,
                'rib' => $s->rib,
                'swift_bic' => $s->swift_bic,
                'account_holder' => $s->account_holder,
            ]),
            'userReferenceHint' => 'AURA-' . strtoupper(substr((string) $user->id, -6)),
        ]);
    }

    /**
     * Handle submission of commercial package order with chosen card designs and payment proof.
     */
    public function submit(Request $request, CommercialPackage $package): RedirectResponse
    {
        if (! $package->is_active) {
            abort(404, __('This commercial package is currently unavailable.'));
        }

        $user = $request->user();

        // 1. Validate Form Inputs
        $allowedMethods = PaymentSetting::where('is_enabled', true)->pluck('key')->all();
        $allowedMethods = array_unique(array_merge($allowedMethods, ['bank_transfer', 'manual_bank_transfer', 'cash_deposit']));

        $rules = [
            'payment_method' => ['required', 'string', \Illuminate\Validation\Rule::in($allowedMethods)],
            'reference_number' => ['required', 'string', 'max:100'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
            'proof_file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
        ];

        if ($package->physical_cards_included > 0) {
            $rules['card_selections'] = ['required', 'array', 'size:' . $package->physical_cards_included];
            $rules['card_selections.*'] = ['required', 'string', 'exists:card_designs,key'];
        } else {
            $rules['external_card_data'] = ['nullable', 'array'];
            $rules['external_card_data.tag_type'] = ['nullable', 'string', 'max:64'];
            $rules['external_card_data.notes'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        // 2. Create the immutable commercial order snapshot
        $order = $this->createOrderAction->execute($user, [
            'package_id' => $package->id,
            'card_selections' => $validated['card_selections'] ?? [],
            'external_card_data' => $validated['external_card_data'] ?? null,
            'customer_notes' => $validated['customer_notes'] ?? null,
        ]);

        // 3. Attach payment proof and transition order to under_review
        $this->submitProofAction->execute($user, $order, [
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'],
            'proof_file' => $request->file('proof_file'),
            'customer_notes' => $validated['customer_notes'] ?? null,
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', __('Votre commande a été enregistrée avec succès. Notre équipe examine votre reçu sous 24h ouvrées.'));
    }

    /**
     * Customer orders listing and active commercial packages overview.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $orders = $user->orders()
            ->with(['package', 'paymentRequests'])
            ->latest()
            ->paginate(10);

        $activePurchases = $user->packagePurchases()
            ->with([
                'package',
                'cardAllocations.card',
                'cardAllocations.design',
                'cardAllocations.entitlement',
                'profileAllocations.profile',
            ])
            ->where('status', 'active')
            ->latest('starts_at')
            ->get();

        return Inertia::render('Customer/Commercial/OrdersIndex', [
            'orders' => $orders->through(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_amount' => (float) $order->total_amount,
                'currency' => $order->currency,
                'card_selections' => $order->card_selections,
                'package' => $order->package ? [
                    'key' => $order->package->key,
                    'name' => $order->package->name,
                    'physical_cards_included' => $order->package->physical_cards_included,
                    'profiles_included' => $order->package->profiles_included,
                ] : null,
                'latest_payment' => $order->paymentRequests->sortByDesc('created_at')->first() ? [
                    'status' => $order->paymentRequests->sortByDesc('created_at')->first()->status->value,
                    'status_label' => $order->paymentRequests->sortByDesc('created_at')->first()->status->label(),
                    'reference_number' => $order->paymentRequests->sortByDesc('created_at')->first()->reference_number,
                    'created_at' => $order->paymentRequests->sortByDesc('created_at')->first()->created_at->toISOString(),
                ] : null,
                'created_at' => $order->created_at->toISOString(),
            ]),
            'activePurchases' => $activePurchases->map(fn ($p) => [
                'id' => $p->id,
                'package_name' => $p->package?->name,
                'package_key' => $p->package?->key,
                'starts_at' => $p->starts_at?->toISOString(),
                'ends_at' => $p->ends_at?->toISOString(),
                'card_allocations_count' => $p->cardAllocations->count(),
                'profile_allocations_count' => $p->profileAllocations->count(),
            ]),
        ]);
    }

    /**
     * Display a specific order and its real-time processing timeline.
     */
    public function show(Request $request, Order $order): Response
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            abort(403, __('Non autorisé à consulter cette commande.'));
        }

        $order->load([
            'package',
            'paymentRequests',
            'purchase.cardAllocations.card',
            'purchase.cardAllocations.design',
            'purchase.cardAllocations.entitlement',
            'purchase.profileAllocations.profile',
        ]);

        $cardDesigns = CardDesign::where('is_active', true)->get()->keyBy('key');
        $paymentSettings = PaymentSetting::where('is_enabled', true)->get();

        return Inertia::render('Customer/Commercial/OrderDetail', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_amount' => (float) $order->total_amount,
                'currency' => $order->currency,
                'customer_notes' => $order->customer_notes,
                'card_selections' => $order->card_selections,
                'created_at' => $order->created_at->toISOString(),
                'updated_at' => $order->updated_at->toISOString(),
                'package' => $order->package ? [
                    'id' => $order->package->id,
                    'key' => $order->package->key,
                    'name' => $order->package->name,
                    'description' => $order->package->description,
                    'duration_months' => $order->package->duration_months,
                    'physical_cards_included' => $order->package->physical_cards_included,
                    'profiles_included' => $order->package->profiles_included,
                    'edit_credits_per_profile' => $order->package->edit_credits_per_profile,
                ] : null,
                'payment_requests' => $order->paymentRequests->map(fn ($pr) => [
                    'id' => $pr->id,
                    'status' => $pr->status->value,
                    'status_label' => $pr->status->label(),
                    'payment_method' => $pr->payment_method,
                    'reference_number' => $pr->reference_number,
                    'rejection_reason' => $pr->rejection_reason,
                    'created_at' => $pr->created_at->toISOString(),
                    'processed_at' => $pr->processed_at?->toISOString(),
                ]),
                'purchase' => $order->purchase ? [
                    'id' => $order->purchase->id,
                    'status' => $order->purchase->status,
                    'starts_at' => $order->purchase->starts_at?->toISOString(),
                    'ends_at' => $order->purchase->ends_at?->toISOString(),
                    'card_allocations' => $order->purchase->cardAllocations->map(fn ($ca) => [
                        'id' => $ca->id,
                        'card_serial' => $ca->card?->serial_number,
                        'card_status' => $ca->card?->status->value,
                        'design_name' => $ca->design?->name,
                        'design_color' => $ca->design?->color,
                        'credits_allocated' => $ca->entitlement?->initial_credits_amount,
                    ]),
                ] : null,
            ],
            'availableDesigns' => $cardDesigns->map(fn ($d) => [
                'key' => $d->key,
                'name' => $d->name,
                'color' => $d->color,
                'material' => $d->material,
                'preview_image' => $d->preview_image,
            ]),
            'paymentSettings' => $paymentSettings->map(fn ($s) => [
                'id' => $s->id,
                'key' => $s->key,
                'title' => $s->title,
                'instructions' => $s->instructions,
                'bank_name' => $s->bank_name,
                'rib' => $s->rib,
                'swift_bic' => $s->swift_bic,
                'account_holder' => $s->account_holder,
            ]),
            'userReferenceHint' => 'AURA-' . strtoupper(substr((string) $user->id, -6)),
        ]);
    }

    /**
     * Submit payment proof for a pending order.
     */
    public function uploadProof(Request $request, Order $order): RedirectResponse
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            abort(403, __('Non autorisé à modifier cette commande.'));
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:bank_transfer,cash_deposit'],
            'reference_number' => ['required', 'string', 'max:100'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
            'proof_file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf'],
        ]);

        $this->submitProofAction->execute($user, $order, [
            'payment_method' => $validated['payment_method'],
            'reference_number' => $validated['reference_number'],
            'proof_file' => $request->file('proof_file'),
            'customer_notes' => $validated['customer_notes'] ?? null,
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success', __('Votre preuve de paiement a été soumise avec succès.'));
    }

    /**
     * Stream private payment proof receipt to authorized order owner.
     */
    public function downloadProof(Request $request, Order $order, PaymentRequest $paymentRequest): StreamedResponse
    {
        $user = $request->user();

        if ($order->user_id !== $user->id || $paymentRequest->order_id !== $order->id) {
            abort(403, __('Non autorisé à télécharger cette pièce justificative.'));
        }

        if (! $paymentRequest->proof_path || ! Storage::disk('local')->exists('private/' . $paymentRequest->proof_path)) {
            // Also check root if stored directly
            if (! Storage::disk('local')->exists($paymentRequest->proof_path)) {
                abort(404, __('Reçu introuvable sur le serveur sécurisé.'));
            }
            return Storage::disk('local')->response(
                $paymentRequest->proof_path,
                $paymentRequest->proof_original_name ?? 'recu-paiement'
            );
        }

        return Storage::disk('local')->response(
            'private/' . $paymentRequest->proof_path,
            $paymentRequest->proof_original_name ?? 'recu-paiement'
        );
    }
}

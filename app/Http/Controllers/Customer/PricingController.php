<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Actions\Commercial\SubmitPaymentRequestAction;
use App\Http\Controllers\Controller;
use App\Models\PaymentRequest;
use App\Models\PaymentSetting;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PricingController extends Controller
{
    public function __construct(
        protected SubmitPaymentRequestAction $submitPaymentAction
    ) {}

    /**
     * Display public/customer commercial pricing page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $packages = \App\Models\CommercialPackage::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $cardDesigns = \App\Models\CardDesign::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $plans = Plan::where('is_active', true)
            ->with(['features'])
            ->orderBy('sort_order', 'asc')
            ->get();

        $activeSubscription = $user?->activeSubscription();
        if ($activeSubscription) {
            $activeSubscription->load('plan');
        }

        $activePurchases = $user ? $user->packagePurchases()
            ->with(['package', 'cardAllocations.card', 'cardAllocations.design'])
            ->where('status', 'active')
            ->get() : collect();

        return Inertia::render('Customer/Commercial/Pricing', [
            'packages' => $packages->map(fn ($pkg) => [
                'id' => $pkg->id,
                'key' => $pkg->key,
                'name' => $pkg->name,
                'description' => $pkg->description,
                'price' => (float) $pkg->price,
                'currency' => $pkg->currency,
                'duration_months' => $pkg->duration_months,
                'physical_cards_included' => $pkg->physical_cards_included,
                'profiles_included' => $pkg->profiles_included,
                'edit_credits_per_profile' => $pkg->edit_credits_per_profile,
                'allows_customer_owned_card' => $pkg->allows_customer_owned_card,
                'requires_customer_owned_card' => $pkg->requires_customer_owned_card,
                'is_featured' => $pkg->is_featured,
                'features_list' => $pkg->features_list,
            ]),
            'cardDesigns' => $cardDesigns->map(fn ($d) => [
                'id' => $d->id,
                'key' => $d->key,
                'name' => $d->name,
                'description' => $d->description,
                'material' => $d->material,
                'color' => $d->color,
                'preview_image' => $d->preview_image,
            ]),
            'activePurchases' => $activePurchases->map(fn ($p) => [
                'id' => $p->id,
                'package_key' => $p->package?->key,
                'package_name' => $p->package?->name,
                'ends_at' => $p->ends_at?->toISOString(),
            ]),
            'plans' => $plans->map(fn ($p) => [
                'id' => $p->id,
                'key' => $p->key,
                'name' => $p->name,
                'description' => $p->description,
                'price' => (float) $p->price,
                'currency' => $p->currency,
                'billing_period' => $p->billing_period,
                'is_recommended' => $p->is_recommended,
                'features' => $p->features->map(fn ($f) => [
                    'key' => $f->key,
                    'name' => $f->name,
                    'description' => $f->description,
                    'value' => $f->pivot->value,
                ]),
            ]),
            'activeSubscription' => $activeSubscription ? [
                'id' => $activeSubscription->id,
                'plan_key' => $activeSubscription->plan?->key,
                'plan_name' => $activeSubscription->plan?->name,
                'price' => (float) $activeSubscription->price_amount,
                'currency' => $activeSubscription->price_currency,
                'starts_at' => $activeSubscription->starts_at?->toISOString(),
                'ends_at' => $activeSubscription->ends_at?->toISOString(),
                'status' => $activeSubscription->status->value,
            ] : null,
            'editCreditBalance' => $user?->edit_credit_balance ?? 0,
        ]);
    }

    /**
     * Customer subscription management view.
     */
    public function subscription(Request $request): Response
    {
        $user = $request->user();
        $activeSubscription = $user->activeSubscription();
        if ($activeSubscription) {
            $activeSubscription->load('plan.features');
        }

        $paymentRequests = $user->paymentRequests()
            ->with('plan')
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Customer/Commercial/Subscription', [
            'subscription' => $activeSubscription ? [
                'id' => $activeSubscription->id,
                'status' => $activeSubscription->status->value,
                'plan' => $activeSubscription->plan ? [
                    'key' => $activeSubscription->plan->key,
                    'name' => $activeSubscription->plan->name,
                ] : null,
                'price' => (float) $activeSubscription->price_amount,
                'currency' => $activeSubscription->price_currency,
                'billing_period' => $activeSubscription->billing_period,
                'plan_snapshot' => $activeSubscription->plan_snapshot,
                'starts_at' => $activeSubscription->starts_at?->toISOString(),
                'ends_at' => $activeSubscription->ends_at?->toISOString(),
                'has_unlimited_edits' => $activeSubscription->hasFeature('profile.unlimited_edits'),
            ] : null,
            'editCreditBalance' => $user->edit_credit_balance,
            'paymentRequests' => $paymentRequests->map(fn ($pr) => [
                'id' => $pr->id,
                'plan_name' => $pr->plan?->name,
                'amount' => (float) $pr->amount,
                'currency' => $pr->currency,
                'status' => $pr->status->value,
                'status_label' => $pr->status->label(),
                'reference_number' => $pr->reference_number,
                'rejection_reason' => $pr->rejection_reason,
                'created_at' => $pr->created_at->toISOString(),
                'processed_at' => $pr->processed_at?->toISOString(),
            ]),
        ]);
    }

    /**
     * Checkout payment page with manual bank/cash instructions.
     */
    public function checkout(Request $request, Plan $plan): Response
    {
        $user = $request->user();
        $paymentSettings = PaymentSetting::where('is_enabled', true)->get();

        return Inertia::render('Customer/Commercial/Checkout', [
            'plan' => [
                'id' => $plan->id,
                'key' => $plan->key,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => (float) $plan->price,
                'currency' => $plan->currency,
                'billing_period' => $plan->billing_period,
            ],
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
            'userReferenceHint' => 'AURA-' . strtoupper(substr($user->id, -6)),
        ]);
    }

    /**
     * Submit payment proof.
     */
    public function submitPayment(Request $request, Plan $plan): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:bank_transfer,cash_deposit'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
            'proof' => ['required', 'file', 'max:10240'], // 10MB max, strict MIME checked in action
        ]);

        $this->submitPaymentAction->execute(
            $user,
            $plan,
            [
                'payment_method' => $validated['payment_method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'customer_notes' => $validated['customer_notes'] ?? null,
            ],
            $request->file('proof')
        );

        return redirect()->route('subscription.index')
            ->with('success', __('Your payment verification request has been submitted. Our team will review your receipt within 24 hours.'));
    }

    /**
     * Securely stream private payment proof to authorized owner or admin.
     */
    public function downloadProof(Request $request, PaymentRequest $paymentRequest): StreamedResponse
    {
        $user = $request->user();

        // Authorization: owner or staff with payments.review
        if ($paymentRequest->user_id !== $user->id && ! $user->hasPermission('payments.review') && ! $user->hasRole(\App\Enums\RoleName::SUPER_ADMIN)) {
            abort(403, __('You are not authorized to view this payment receipt.'));
        }

        if (! $paymentRequest->proof_path || ! Storage::disk('local')->exists($paymentRequest->proof_path)) {
            abort(404, __('Payment receipt file not found.'));
        }

        return Storage::disk('local')->response(
            $paymentRequest->proof_path,
            $paymentRequest->proof_original_name ?? 'payment-receipt'
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Actions\Commercial;

use App\Enums\PaymentRequestStatus;
use App\Enums\SubscriptionStatus;
use App\Models\PaymentRequest;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovePaymentRequestAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Atomically approve a payment request, activate subscription, and snapshot commercial terms.
     * Prevents double approval via row lock and state verification.
     *
     * @param User $admin
     * @param PaymentRequest $paymentRequest
     * @return Subscription
     * @throws ValidationException
     */
    public function execute(User $admin, PaymentRequest $paymentRequest): Subscription
    {
        return DB::transaction(function () use ($admin, $paymentRequest) {
            // Lock payment request row
            $lockedRequest = PaymentRequest::where('id', $paymentRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedRequest->isPending()) {
                throw ValidationException::withMessages([
                    'payment_request' => [__('This payment request has already been processed or finalized.')],
                ]);
            }

            // Customer cannot approve their own payment request
            if ($lockedRequest->user_id === $admin->id && ! $admin->hasRole(\App\Enums\RoleName::SUPER_ADMIN)) {
                throw ValidationException::withMessages([
                    'approval' => [__('You cannot approve your own payment request.')],
                ]);
            }

            $plan = $lockedRequest->plan;
            if (! $plan) {
                throw ValidationException::withMessages([
                    'plan' => [__('The associated subscription plan could not be found.')],
                ]);
            }

            // Load plan features to build immutable snapshot
            $plan->load('features');
            $featuresSnapshot = $plan->features->map(fn ($f) => [
                'id' => $f->id,
                'key' => $f->key,
                'name' => $f->name,
                'value' => $f->pivot->value,
            ])->toArray();

            $planSnapshot = [
                'plan_id' => $plan->id,
                'key' => $plan->key,
                'name' => $plan->name,
                'description' => $plan->description,
                'price' => (float) $lockedRequest->amount,
                'currency' => $lockedRequest->currency,
                'billing_period' => $plan->billing_period,
                'features' => $featuresSnapshot,
                'approved_at' => now()->toISOString(),
            ];

            // Determine subscription duration
            $startsAt = now();
            $endsAt = match ($plan->billing_period) {
                'monthly' => $startsAt->copy()->addMonth(),
                'lifetime' => null,
                default => $startsAt->copy()->addYear(),
            };

            // Update payment request
            $lockedRequest->status = PaymentRequestStatus::APPROVED;
            $lockedRequest->processed_by = $admin->id;
            $lockedRequest->processed_at = now();
            $lockedRequest->save();

            // Deactivate any existing active subscription for this user
            Subscription::where('user_id', $lockedRequest->user_id)
                ->where('status', SubscriptionStatus::ACTIVE->value)
                ->update([
                    'status' => SubscriptionStatus::CANCELLED->value,
                    'cancelled_at' => now(),
                ]);

            // Create new active subscription
            $subscription = Subscription::create([
                'user_id' => $lockedRequest->user_id,
                'plan_id' => $plan->id,
                'payment_request_id' => $lockedRequest->id,
                'status' => SubscriptionStatus::ACTIVE,
                'price_amount' => $lockedRequest->amount,
                'price_currency' => $lockedRequest->currency,
                'billing_period' => $plan->billing_period,
                'plan_snapshot' => $planSnapshot,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'metadata' => [
                    'payment_request_id' => $lockedRequest->id,
                    'reference_number' => $lockedRequest->reference_number,
                ],
            ]);

            // Log financial audit
            $this->auditLogService->log(
                'payment.approved',
                $lockedRequest,
                ['status' => 'pending'],
                [
                    'status' => 'approved',
                    'subscription_id' => $subscription->id,
                    'amount' => $lockedRequest->amount,
                    'currency' => $lockedRequest->currency,
                    'plan_key' => $plan->key,
                ],
                "Approved payment request {$lockedRequest->id} and activated subscription {$subscription->id}",
                $admin
            );

            return $subscription;
        });
    }
}

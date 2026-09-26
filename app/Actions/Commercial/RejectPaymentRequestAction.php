<?php

declare(strict_types=1);

namespace App\Actions\Commercial;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RejectPaymentRequestAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Atomically reject a payment request with a mandatory reason.
     *
     * @param User $admin
     * @param PaymentRequest $paymentRequest
     * @param string $reason
     * @return PaymentRequest
     * @throws ValidationException
     */
    public function execute(User $admin, PaymentRequest $paymentRequest, string $reason): PaymentRequest
    {
        $trimmedReason = trim($reason);
        if ($trimmedReason === '') {
            throw ValidationException::withMessages([
                'reason' => [__('A valid rejection reason is required.')],
            ]);
        }

        return DB::transaction(function () use ($admin, $paymentRequest, $trimmedReason) {
            $lockedRequest = PaymentRequest::where('id', $paymentRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedRequest->isPending()) {
                throw ValidationException::withMessages([
                    'payment_request' => [__('This payment request has already been finalized.')],
                ]);
            }

            $lockedRequest->status = PaymentRequestStatus::REJECTED;
            $lockedRequest->rejection_reason = $trimmedReason;
            $lockedRequest->processed_by = $admin->id;
            $lockedRequest->processed_at = now();
            $lockedRequest->save();

            $this->auditLogService->log(
                'payment.rejected',
                $lockedRequest,
                ['status' => 'pending'],
                [
                    'status' => 'rejected',
                    'reason' => $trimmedReason,
                ],
                "Rejected payment request {$lockedRequest->id}: {$trimmedReason}",
                $admin
            );

            return $lockedRequest;
        });
    }
}

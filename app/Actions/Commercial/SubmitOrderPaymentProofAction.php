<?php

declare(strict_types=1);

namespace App\Actions\Commercial;

use App\Enums\PaymentRequestStatus;
use App\Models\Order;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubmitOrderPaymentProofAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Submit payment proof for an existing commercial order.
     * Enforces private storage, MIME whitelist, size limit, and safe randomized naming.
     *
     * @param User $user
     * @param Order $order
     * @param array{
     *     payment_method: string,
     *     reference_number: string,
     *     proof_file: UploadedFile,
     *     customer_notes?: string|null
     * } $data
     * @return PaymentRequest
     * @throws ValidationException
     */
    public function execute(User $user, Order $order, array $data): PaymentRequest
    {
        return DB::transaction(function () use ($user, $order, $data) {
            $lockedOrder = Order::where('id', $order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'order' => [__('Unauthorized order ownership.')],
                ]);
            }

            if ($lockedOrder->status === 'approved') {
                throw ValidationException::withMessages([
                    'order' => [__('This order has already been approved.')],
                ]);
            }

            /** @var UploadedFile $file */
            $file = $data['proof_file'];

            // 1. Strict MIME whitelist
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            $fileMime = $file->getMimeType();

            if (! in_array($fileMime, $allowedMimes, true)) {
                throw ValidationException::withMessages([
                    'proof_file' => [__('Invalid file format. Allowed formats: JPG, PNG, WebP, PDF.')],
                ]);
            }

            // 2. Strict file size: max 10MB (10485760 bytes)
            $fileSize = $file->getSize();
            if ($fileSize > 10 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'proof_file' => [__('Payment receipt size must not exceed 10 megabytes.')],
                ]);
            }

            // 3. Prevent dangerous executable extensions
            $ext = strtolower($file->getClientOriginalExtension());
            $dangerousExts = ['php', 'phtml', 'exe', 'bat', 'sh', 'js', 'html', 'htm', 'svg', 'phar'];
            if (in_array($ext, $dangerousExts, true)) {
                throw ValidationException::withMessages([
                    'proof_file' => [__('Dangerous file extension rejected.')],
                ]);
            }

            // 4. Safe randomized storage name
            $safeExtension = match ($fileMime) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'application/pdf' => 'pdf',
                default => 'bin',
            };

            $safeFileName = 'proof_' . date('Ymd_His') . '_' . Str::random(24) . '.' . $safeExtension;
            $storagePath = 'payment-proofs/' . $safeFileName;

            // Store in private disk
            Storage::disk('local')->putFileAs('private/payment-proofs', $file, $safeFileName);

            // 5. Create PaymentRequest linked to this order
            $paymentRequest = PaymentRequest::create([
                'user_id' => $user->id,
                'order_id' => $lockedOrder->id,
                'plan_id' => null,
                'amount' => $lockedOrder->total_amount,
                'currency' => $lockedOrder->currency,
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'],
                'customer_notes' => $data['customer_notes'] ?? null,
                'status' => PaymentRequestStatus::UNDER_REVIEW,
                'proof_path' => $storagePath,
                'proof_original_name' => $file->getClientOriginalName(),
                'proof_mime' => $fileMime,
                'proof_size' => $fileSize,
            ]);

            // Update order status
            $lockedOrder->status = 'under_review';
            $lockedOrder->save();

            // Financial Audit Log
            $this->auditLogService->log(
                'order.payment_proof_submitted',
                $lockedOrder,
                ['status' => 'pending'],
                [
                    'status' => 'under_review',
                    'payment_request_id' => $paymentRequest->id,
                    'reference_number' => $paymentRequest->reference_number,
                    'amount' => $paymentRequest->amount,
                ],
                "Submitted payment proof for Order #{$lockedOrder->order_number}",
                $user
            );

            return $paymentRequest;
        });
    }
}

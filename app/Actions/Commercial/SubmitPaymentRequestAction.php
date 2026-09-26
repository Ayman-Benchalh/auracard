<?php

declare(strict_types=1);

namespace App\Actions\Commercial;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\Plan;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SubmitPaymentRequestAction
{
    protected const ALLOWED_IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Submit manual payment verification request with proof document.
     *
     * @param User $user
     * @param Plan $plan
     * @param array{
     *     payment_method: string,
     *     reference_number?: string|null,
     *     customer_notes?: string|null,
     * } $data
     * @param UploadedFile $proofFile
     * @return PaymentRequest
     * @throws ValidationException
     */
    public function execute(
        User $user,
        Plan $plan,
        array $data,
        UploadedFile $proofFile
    ): PaymentRequest {
        if (! $plan->is_active) {
            throw ValidationException::withMessages([
                'plan' => [__('The selected subscription plan is currently unavailable.')],
            ]);
        }

        // Strict file verification
        $storedProof = $this->verifyAndStoreProof($proofFile, $user);

        $request = PaymentRequest::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'payment_method' => $data['payment_method'] ?? 'bank_transfer',
            'reference_number' => $data['reference_number'] ?? null,
            'customer_notes' => $data['customer_notes'] ?? null,
            'status' => PaymentRequestStatus::PENDING,
            'proof_path' => $storedProof['path'],
            'proof_original_name' => $storedProof['original_name'],
            'proof_mime' => $storedProof['mime'],
            'proof_size' => $storedProof['size'],
        ]);

        $this->auditLogService->log(
            'payment.submitted',
            $request,
            null,
            [
                'payment_request_id' => $request->id,
                'plan_id' => $plan->id,
                'amount' => $plan->price,
                'currency' => $plan->currency,
            ],
            "Submitted payment proof for plan {$plan->key}",
            $user
        );

        return $request;
    }

    /**
     * Strictly validate and store proof file in private storage.
     *
     * @param UploadedFile $file
     * @param User $user
     * @return array{path: string, original_name: string, mime: string, size: int}
     * @throws ValidationException
     */
    protected function verifyAndStoreProof(UploadedFile $file, User $user): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        // Strictly reject dangerous executable extensions and SVG
        if (in_array($extension, ['php', 'phtml', 'phar', 'exe', 'sh', 'bat', 'cmd', 'svg', 'xml', 'html', 'js'], true)) {
            throw ValidationException::withMessages([
                'proof' => [__('The uploaded file type is strictly prohibited for security reasons.')],
            ]);
        }

        $realPath = $file->getRealPath();
        $mime = $file->getMimeType();
        $size = $file->getSize();

        $isImage = array_key_exists($mime, self::ALLOWED_IMAGE_MIMES);
        $isPdf = ($mime === 'application/pdf');

        if (! $isImage && ! $isPdf) {
            throw ValidationException::withMessages([
                'proof' => [__('Payment proof must be a valid image (JPEG, PNG, WebP) or PDF document.')],
            ]);
        }

        // Image validation
        if ($isImage) {
            if ($size > 5 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'proof' => [__('Image proof must not exceed 5MB.')],
                ]);
            }

            $imgSize = @getimagesize($realPath);
            if (! $imgSize || empty($imgSize[0]) || empty($imgSize[1])) {
                throw ValidationException::withMessages([
                    'proof' => [__('The uploaded image is corrupted or invalid.')],
                ]);
            }

            $safeExt = self::ALLOWED_IMAGE_MIMES[$mime];
        } else {
            // PDF validation
            if ($size > 10 * 1024 * 1024) {
                throw ValidationException::withMessages([
                    'proof' => [__('PDF proof must not exceed 10MB.')],
                ]);
            }

            $handle = @fopen($realPath, 'rb');
            $header = $handle ? fread($handle, 5) : '';
            if ($handle) {
                fclose($handle);
            }

            if ($header !== '%PDF-') {
                throw ValidationException::withMessages([
                    'proof' => [__('The uploaded file is not a valid PDF document.')],
                ]);
            }

            $safeExt = 'pdf';
        }

        // Randomized safe filename in private storage
        $safeFilename = bin2hex(random_bytes(16)) . '.' . $safeExt;
        $path = "payment_proofs/{$safeFilename}";

        // Store to private disk
        Storage::disk('local')->putFileAs('payment_proofs', $file, $safeFilename);

        return [
            'path' => $path,
            'original_name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'mime' => $mime,
            'size' => $size,
        ];
    }
}

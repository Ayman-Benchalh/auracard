<?php

declare(strict_types=1);

namespace App\Services\Commercial;

use App\Contracts\PaymentGatewayInterface;
use App\Models\PaymentSetting;
use App\Models\Plan;
use App\Models\User;

class ManualPaymentGateway implements PaymentGatewayInterface
{
    public function getIdentifier(): string
    {
        return 'manual';
    }

    public function getName(): string
    {
        return 'Manual Bank Transfer & Cash Deposit';
    }

    public function isAvailable(): bool
    {
        return PaymentSetting::where('is_enabled', true)->exists();
    }

    public function initiatePayment(User $user, Plan $plan, array $options = []): array
    {
        $setting = PaymentSetting::where('is_enabled', true)->first();

        return [
            'success' => true,
            'instructions' => [
                'bank_name' => $setting?->bank_name ?? 'Attijariwafa Bank',
                'rib' => $setting?->rib ?? '007 780 0001234567890123 45',
                'account_holder' => $setting?->account_holder ?? 'AURA CARD SARL',
                'swift_bic' => $setting?->swift_bic ?? 'BCMAMAMC',
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'reference_hint' => 'AURA-' . strtoupper(substr($user->id, -6)),
            ],
            'message' => __('Please transfer :amount :currency and upload your receipt below.', [
                'amount' => $plan->price,
                'currency' => $plan->currency,
            ]),
        ];
    }
}

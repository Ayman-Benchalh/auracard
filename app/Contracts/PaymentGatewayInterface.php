<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\PaymentRequest;
use App\Models\Plan;
use App\Models\User;

interface PaymentGatewayInterface
{
    /**
     * Unique identifier for this payment gateway driver.
     */
    public function getIdentifier(): string;

    /**
     * Human-readable name of the payment gateway.
     */
    public function getName(): string;

    /**
     * Check if the gateway is ready and configured.
     */
    public function isAvailable(): bool;

    /**
     * Initiate checkout or payment request.
     *
     * @param User $user
     * @param Plan $plan
     * @param array<string, mixed> $options
     * @return array{
     *     success: bool,
     *     payment_request_id?: string,
     *     redirect_url?: string,
     *     instructions?: array<string, mixed>,
     *     message?: string
     * }
     */
    public function initiatePayment(User $user, Plan $plan, array $options = []): array;
}

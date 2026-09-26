<?php

declare(strict_types=1);

namespace App\Services\Commercial;

use App\Models\Profile;
use App\Models\User;

class ProfileEntitlementResolver
{
    /**
     * Resolve publishing cost and authorization for a given profile and user.
     *
     * @param User $user
     * @param Profile $profile
     * @param bool $isContentChanged
     * @return array{
     *     allowed: bool,
     *     cost: int,
     *     reason: string,
     *     has_unlimited: bool,
     *     balance: int,
     *     message?: string
     * }
     */
    public function resolvePublishDecision(User $user, Profile $profile, bool $isContentChanged): array
    {
        $entitlement = $profile->card_entitlement_id
            ? \App\Models\CardEntitlement::find($profile->card_entitlement_id)
            : null;

        $balance = $entitlement
            ? $entitlement->edit_credit_balance
            : $user->edit_credit_balance;

        // 1. Unchanged content publish: ALWAYS 0 credits and ALWAYS allowed
        if (! $isContentChanged) {
            return [
                'allowed' => true,
                'cost' => 0,
                'reason' => 'unchanged_content',
                'has_unlimited' => false,
                'balance' => $balance,
            ];
        }

        // 2. First publish of Version 1: ALWAYS 0 cost (grants +2 bonus)
        if ((int) $profile->current_version === 0) {
            return [
                'allowed' => true,
                'cost' => 0,
                'reason' => 'first_publication_grant',
                'has_unlimited' => false,
                'balance' => $balance,
            ];
        }

        // 3. Inspect Active Subscription
        $subscription = $user->activeSubscription();
        if ($subscription && $subscription->hasFeature('profile.unlimited_edits')) {
            return [
                'allowed' => true,
                'cost' => 0,
                'reason' => 'subscription_unlimited',
                'has_unlimited' => true,
                'balance' => $balance,
            ];
        }

        // 4. Fallback to free modification credits
        if ($balance > 0) {
            return [
                'allowed' => true,
                'cost' => 1,
                'reason' => 'edit_credit_deduction',
                'has_unlimited' => false,
                'balance' => $balance,
            ];
        }

        // 5. Balance is 0: publication blocked
        return [
            'allowed' => false,
            'cost' => 1,
            'reason' => 'insufficient_credits',
            'has_unlimited' => false,
            'balance' => $balance,
            'message' => __('You have 0 modification credits remaining. Please upgrade to Pro for unlimited edits or purchase additional credits.'),
        ];
    }
}

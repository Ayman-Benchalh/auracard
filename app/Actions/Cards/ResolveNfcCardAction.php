<?php

namespace App\Actions\Cards;

use App\Models\NfcCard;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveNfcCardAction
{
    /**
     * Resolve a physical NFC card by hashing its raw incoming public token.
     * Prevents token enumeration and guarantees constant-format SHA-256 lookups.
     *
     * @param string $rawToken
     * @return NfcCard
     * @throws NotFoundHttpException
     */
    public function execute(string $rawToken): NfcCard
    {
        $sanitized = trim($rawToken);

        // Basic format validation: must be alphanumeric/hex/URL-safe between 32 and 128 characters
        if (strlen($sanitized) < 32 || strlen($sanitized) > 128 || ! preg_match('/^[a-zA-Z0-9_-]+$/', $sanitized)) {
            throw new NotFoundHttpException(__('Invalid or unrecognized AURA CARD token.'));
        }

        $tokenHash = NfcCard::hashPublicToken($sanitized);

        $card = NfcCard::with(['user', 'currentEntitlement', 'batch'])
            ->where('public_token_hash', $tokenHash)
            ->first();

        if (! $card) {
            throw new NotFoundHttpException(__('Invalid or unrecognized AURA CARD token.'));
        }

        return $card;
    }
}

<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    /**
     * Record a security or administrative audit log.
     * Sanitizes sensitive fields automatically.
     */
    public function log(
        string $action,
        Model|string $target,
        ?array $before = null,
        ?array $after = null,
        ?string $reason = null,
        ?User $actor = null
    ): AuditLog {
        $actorId = $actor?->id ?? auth()->id();
        $targetType = is_object($target) ? get_class($target) : 'system';
        $targetId = is_object($target) && isset($target->id) ? (string) $target->id : (string) $target;

        return AuditLog::create([
            'actor_id' => $actorId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'before_payload' => $this->sanitizePayload($before),
            'after_payload' => $this->sanitizePayload($after),
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Strip secrets and raw tokens from payloads before persisting
     */
    protected function sanitizePayload(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        $redactedKeys = [
            'raw_public_token',
            'raw_token',
            'claim_code',
            'claim_code_hash',
            'public_token_hash',
            'password',
            'password_confirmation',
            'secret',
            'token',
            'two_factor_secret',
        ];

        $sanitized = [];
        foreach ($payload as $key => $value) {
            if (in_array(strtolower($key), $redactedKeys, true)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizePayload($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}

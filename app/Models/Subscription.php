<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'plan_id',
        'payment_request_id',
        'status',
        'price_amount',
        'price_currency',
        'billing_period',
        'plan_snapshot',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'approved_by',
        'approved_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'price_amount' => 'decimal:2',
            'plan_snapshot' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'approved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function paymentRequest(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isActive(): bool
    {
        if ($this->status !== SubscriptionStatus::ACTIVE) {
            return false;
        }

        if ($this->ends_at !== null && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', SubscriptionStatus::ACTIVE->value)
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    public function hasFeature(string $featureKey): bool
    {
        // First inspect immutable plan snapshot
        $features = $this->plan_snapshot['features'] ?? null;
        if (is_array($features)) {
            foreach ($features as $f) {
                if (($f['key'] ?? null) === $featureKey) {
                    return true;
                }
            }
        }

        // Fallback to active plan relation
        return $this->plan?->hasFeature($featureKey) ?? false;
    }
}

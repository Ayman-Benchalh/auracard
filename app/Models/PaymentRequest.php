<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentRequestStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentRequest extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'order_id',
        'plan_id',
        'amount',
        'currency',
        'payment_method',
        'reference_number',
        'customer_notes',
        'status',
        'proof_path',
        'proof_original_name',
        'proof_mime',
        'proof_size',
        'processed_by',
        'processed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PaymentRequestStatus::class,
            'proof_size' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function isPending(): bool
    {
        return $this->status === PaymentRequestStatus::PENDING || $this->status === PaymentRequestStatus::UNDER_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === PaymentRequestStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === PaymentRequestStatus::REJECTED;
    }
}

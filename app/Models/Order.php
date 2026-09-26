<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'order_number',
        'user_id',
        'commercial_package_id',
        'total_amount',
        'currency',
        'status',
        'package_snapshot',
        'card_selections',
        'external_card_data',
        'customer_notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'package_snapshot' => 'array',
            'card_selections' => 'array',
            'external_card_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(CommercialPackage::class, 'commercial_package_id');
    }

    public function purchase(): HasOne
    {
        return $this->hasOne(PackagePurchase::class);
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}

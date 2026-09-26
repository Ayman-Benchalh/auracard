<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageProfileAllocation extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'package_purchase_id',
        'card_entitlement_id',
        'profile_id',
        'edit_credits_allocated',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'edit_credits_allocated' => 'integer',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(PackagePurchase::class, 'package_purchase_id');
    }

    public function entitlement(): BelongsTo
    {
        return $this->belongsTo(CardEntitlement::class, 'card_entitlement_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}

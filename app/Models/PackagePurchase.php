<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackagePurchase extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'order_id',
        'user_id',
        'commercial_package_id',
        'status',
        'starts_at',
        'ends_at',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(CommercialPackage::class, 'commercial_package_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function cardAllocations(): HasMany
    {
        return $this->hasMany(PackageCardAllocation::class);
    }

    public function profileAllocations(): HasMany
    {
        return $this->hasMany(PackageProfileAllocation::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' &&
            $this->starts_at <= now() &&
            $this->ends_at > now();
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'key',
        'name',
        'description',
        'price',
        'currency',
        'billing_period',
        'is_active',
        'is_recommended',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_recommended' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class);
    }

    public function getLocalizedName(string $locale = 'en'): string
    {
        return $this->name[$locale] ?? $this->name['en'] ?? $this->key;
    }

    public function getLocalizedDescription(string $locale = 'en'): string
    {
        return $this->description[$locale] ?? $this->description['en'] ?? '';
    }

    public function hasFeature(string $featureKey): bool
    {
        return $this->features()->where('key', $featureKey)->exists();
    }
}

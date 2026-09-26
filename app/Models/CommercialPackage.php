<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommercialPackage extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'key',
        'name',
        'description',
        'price',
        'currency',
        'duration_months',
        'physical_cards_included',
        'profiles_included',
        'edit_credits_per_profile',
        'allows_customer_owned_card',
        'requires_customer_owned_card',
        'is_active',
        'is_featured',
        'sort_order',
        'features_list',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'price' => 'decimal:2',
            'duration_months' => 'integer',
            'physical_cards_included' => 'integer',
            'profiles_included' => 'integer',
            'edit_credits_per_profile' => 'integer',
            'allows_customer_owned_card' => 'boolean',
            'requires_customer_owned_card' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'features_list' => 'array',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(PackagePurchase::class);
    }

    public function getLocalizedName(string $locale = 'fr'): string
    {
        return $this->name[$locale] ?? $this->name['fr'] ?? $this->name['en'] ?? $this->key;
    }

    public function getLocalizedDescription(string $locale = 'fr'): string
    {
        return $this->description[$locale] ?? $this->description['fr'] ?? $this->description['en'] ?? '';
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardDesign extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'key',
        'name',
        'description',
        'material',
        'color',
        'preview_image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function cardAllocations(): HasMany
    {
        return $this->hasMany(PackageCardAllocation::class);
    }

    public function getLocalizedName(string $locale = 'fr'): string
    {
        return $this->name[$locale] ?? $this->name['fr'] ?? $this->name['en'] ?? $this->key;
    }
}

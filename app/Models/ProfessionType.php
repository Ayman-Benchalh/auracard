<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfessionType extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'default_blocks',
        'is_active',
        'sort_order',
    ];

    protected $appends = ['slug'];

    public function getSlugAttribute(): string
    {
        return $this->key;
    }

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'default_blocks' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }

    /**
     * Get translated name with fallback.
     */
    public function getTranslatedName(?string $locale = null): string
    {
        $loc = $locale ?? app()->getLocale();
        return $this->name[$loc] ?? $this->name['en'] ?? $this->key;
    }

    /**
     * Get translated description with fallback.
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $loc = $locale ?? app()->getLocale();
        return $this->description[$loc] ?? $this->description['en'] ?? null;
    }
}

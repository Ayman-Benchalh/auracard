<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'key',
        'name',
        'description',
        'preview_image',
        'layout_type',
        'is_active',
        'is_premium',
        'sort_order',
        'configuration',
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
            'configuration' => 'array',
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }

    public function getTranslatedName(?string $locale = null): string
    {
        $loc = $locale ?? app()->getLocale();
        return $this->name[$loc] ?? $this->name['en'] ?? $this->key;
    }

    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $loc = $locale ?? app()->getLocale();
        return $this->description[$loc] ?? $this->description['en'] ?? null;
    }
}

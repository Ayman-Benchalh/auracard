<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'key',
        'name',
        'description',
        'default_tokens',
        'is_active',
        'is_premium',
        'sort_order',
    ];

    protected $appends = ['slug', 'tokens'];

    public function getSlugAttribute(): string
    {
        return $this->key;
    }

    public function getTokensAttribute(): array
    {
        return $this->default_tokens ?? [];
    }

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
            'default_tokens' => 'array',
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
}

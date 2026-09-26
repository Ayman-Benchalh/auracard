<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSetting extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'key',
        'title',
        'instructions',
        'bank_name',
        'rib',
        'swift_bic',
        'account_holder',
        'is_enabled',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'instructions' => 'array',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getLocalizedTitle(string $locale = 'fr'): string
    {
        if (is_string($this->title)) {
            return $this->title;
        }

        return $this->title[$locale] ?? $this->title['fr'] ?? $this->title['en'] ?? $this->key;
    }

    public function getLocalizedInstructions(string $locale = 'fr'): string
    {
        if (! $this->instructions) {
            return '';
        }

        if (is_string($this->instructions)) {
            return $this->instructions;
        }

        return $this->instructions[$locale] ?? $this->instructions['fr'] ?? $this->instructions['en'] ?? '';
    }
}

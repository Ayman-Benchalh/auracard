<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProfileBlockType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileBlock extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'profile_id',
        'type',
        'sort_order',
        'is_enabled',
        'content',
        'settings',
    ];

    protected $appends = [
        'title',
        'order_index',
        'is_visible',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProfileBlockType::class,
            'sort_order' => 'integer',
            'is_enabled' => 'boolean',
            'content' => 'array',
            'settings' => 'array',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function getOrderIndexAttribute(): int
    {
        return $this->sort_order ?? 0;
    }

    public function setOrderIndexAttribute(int $value): void
    {
        $this->sort_order = $value;
    }

    public function getIsVisibleAttribute(): bool
    {
        return $this->is_enabled ?? true;
    }

    public function setIsVisibleAttribute(bool $value): void
    {
        $this->is_enabled = $value;
    }

    public function getConfigAttribute(): array
    {
        return $this->settings ?? [];
    }

    public function setConfigAttribute(array $value): void
    {
        $this->settings = $value;
    }

    public function getTitleAttribute(): ?string
    {
        return $this->settings['title'] ?? $this->content['title'] ?? $this->type?->label();
    }

    public function setTitleAttribute(?string $value): void
    {
        $settings = $this->settings ?? [];
        $settings['title'] = $value;
        $this->settings = $settings;
    }
}

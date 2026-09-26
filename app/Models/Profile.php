<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProfileStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'card_entitlement_id',
        'profession_type_id',
        'template_id',
        'theme_id',
        'slug',
        'display_name',
        'professional_title',
        'company_name',
        'bio',
        'status',
        'draft_design_tokens',
        'published_design_tokens',
        'first_published_at',
        'published_at',
        'current_version',
        'seo_title',
        'seo_description',
        'is_indexable',
    ];

    protected $appends = [
        'headline',
        'company',
        'job_title',
        'avatar_url',
        'cover_url',
        'primary_color',
        'secondary_color',
        'font_family',
        'custom_css',
        'location',
        'version_counter',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProfileStatus::class,
            'draft_design_tokens' => 'array',
            'published_design_tokens' => 'array',
            'first_published_at' => 'datetime',
            'published_at' => 'datetime',
            'current_version' => 'integer',
            'is_indexable' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cardEntitlement(): BelongsTo
    {
        return $this->belongsTo(CardEntitlement::class);
    }

    public function professionType(): BelongsTo
    {
        return $this->belongsTo(ProfessionType::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(ProfileBlock::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProfileMedia::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProfileVersion::class);
    }

    public function publishedVersion(): HasOne
    {
        return $this->hasOne(ProfileVersion::class)->latestOfMany('version_number');
    }

    public function getPublishedVersionIdAttribute(): ?string
    {
        return $this->publishedVersion?->id;
    }

    public function isPublished(): bool
    {
        return $this->status === ProfileStatus::PUBLISHED;
    }

    public function isSuspended(): bool
    {
        return $this->status === ProfileStatus::SUSPENDED;
    }

    public function isDraft(): bool
    {
        return $this->status === ProfileStatus::DRAFT;
    }

    // Aliases and Virtual Attributes
    public function getHeadlineAttribute(): ?string
    {
        return $this->professional_title;
    }

    public function setHeadlineAttribute(?string $value): void
    {
        $this->professional_title = $value;
    }

    public function getCompanyAttribute(): ?string
    {
        return $this->company_name;
    }

    public function setCompanyAttribute(?string $value): void
    {
        $this->company_name = $value;
    }

    public function getJobTitleAttribute(): ?string
    {
        return $this->professional_title;
    }

    public function setJobTitleAttribute(?string $value): void
    {
        $this->professional_title = $value;
    }

    public function getVersionCounterAttribute(): int
    {
        return $this->current_version ?? 0;
    }

    public function setVersionCounterAttribute(int $value): void
    {
        $this->current_version = $value;
    }

    public function getIsPrimaryAttribute(): bool
    {
        return true;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->draft_design_tokens['avatar_url'] ?? null;
    }

    public function setAvatarUrlAttribute(?string $value): void
    {
        $tokens = $this->draft_design_tokens ?? [];
        $tokens['avatar_url'] = $value;
        $this->draft_design_tokens = $tokens;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->draft_design_tokens['cover_url'] ?? null;
    }

    public function setCoverUrlAttribute(?string $value): void
    {
        $tokens = $this->draft_design_tokens ?? [];
        $tokens['cover_url'] = $value;
        $this->draft_design_tokens = $tokens;
    }

    public function getPrimaryColorAttribute(): string
    {
        return $this->draft_design_tokens['primary_color'] ?? '#1e293b';
    }

    public function setPrimaryColorAttribute(string $value): void
    {
        $tokens = $this->draft_design_tokens ?? [];
        $tokens['primary_color'] = $value;
        $this->draft_design_tokens = $tokens;
    }

    public function getSecondaryColorAttribute(): string
    {
        return $this->draft_design_tokens['secondary_color'] ?? '#3b82f6';
    }

    public function setSecondaryColorAttribute(string $value): void
    {
        $tokens = $this->draft_design_tokens ?? [];
        $tokens['secondary_color'] = $value;
        $this->draft_design_tokens = $tokens;
    }

    public function getFontFamilyAttribute(): string
    {
        return $this->draft_design_tokens['font_family'] ?? 'Inter';
    }

    public function setFontFamilyAttribute(string $value): void
    {
        $tokens = $this->draft_design_tokens ?? [];
        $tokens['font_family'] = $value;
        $this->draft_design_tokens = $tokens;
    }

    public function getCustomCssAttribute(): ?string
    {
        return $this->draft_design_tokens['custom_css'] ?? null;
    }

    public function setCustomCssAttribute(?string $value): void
    {
        $tokens = $this->draft_design_tokens ?? [];
        $tokens['custom_css'] = $value;
        $this->draft_design_tokens = $tokens;
    }

    public function getLocationAttribute(): ?string
    {
        return $this->draft_design_tokens['location'] ?? null;
    }

    public function setLocationAttribute(?string $value): void
    {
        $tokens = $this->draft_design_tokens ?? [];
        $tokens['location'] = $value;
        $this->draft_design_tokens = $tokens;
    }

    public function getVcardCacheAttribute(): ?string
    {
        return $this->draft_design_tokens['vcard_cache'] ?? null;
    }

    public function setVcardCacheAttribute(?string $value): void
    {
        $tokens = $this->draft_design_tokens ?? [];
        $tokens['vcard_cache'] = $value;
        $this->draft_design_tokens = $tokens;
    }
}

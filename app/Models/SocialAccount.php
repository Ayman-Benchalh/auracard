<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    use HasFactory, HasUlids;

    public const PROVIDER_GOOGLE = 'google';
    public const PROVIDER_FACEBOOK = 'facebook';
    public const PROVIDER_INSTAGRAM = 'instagram';

    public const SUPPORTED_PROVIDERS = [
        self::PROVIDER_GOOGLE,
        self::PROVIDER_FACEBOOK,
        self::PROVIDER_INSTAGRAM,
    ];

    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'email',
        'avatar_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

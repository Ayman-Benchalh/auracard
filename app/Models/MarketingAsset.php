<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingAsset extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'type',
        'title',
        'prompt',
        'prompt_hash',
        'provider',
        'model',
        'status',
        'file_path',
        'thumbnail_path',
        'mime_type',
        'width',
        'height',
        'file_size',
        'created_by',
        'approved_by',
        'approved_at',
        'error_code',
        'error_message',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'file_size' => 'integer',
            'approved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}

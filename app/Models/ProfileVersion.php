<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileVersion extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'profile_id',
        'version_number',
        'content_snapshot',
        'design_snapshot',
        'content_hash',
        'published_by_user_id',
        'published_at',
    ];

    protected $appends = ['snapshot', 'snapshot_hash'];

    protected function casts(): array
    {
        return [
            'version_number' => 'integer',
            'content_snapshot' => 'array',
            'design_snapshot' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_user_id');
    }

    public function getSnapshotAttribute(): array
    {
        $content = $this->content_snapshot ?? [];
        if (! empty($this->design_snapshot)) {
            $content['design'] = $this->design_snapshot;
        }
        return $content;
    }

    public function setSnapshotAttribute(array $value): void
    {
        $this->content_snapshot = $value;
        $this->design_snapshot = $value['design'] ?? [];
    }

    public function getSnapshotHashAttribute(): string
    {
        return $this->content_hash ?? '';
    }

    public function setSnapshotHashAttribute(string $value): void
    {
        $this->content_hash = $value;
    }
}

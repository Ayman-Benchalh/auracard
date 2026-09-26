<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileLead extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'profile_id',
        'name',
        'email',
        'phone',
        'message',
        'form_data',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'form_data' => 'array',
            'is_read' => 'boolean',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}

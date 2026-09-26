<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NfcCardBatch extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'nfc_card_batches';

    protected $fillable = [
        'batch_code',
        'card_model',
        'card_color',
        'material',
        'quantity',
        'notes',
        'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function cards(): HasMany
    {
        return $this->hasMany(NfcCard::class, 'batch_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}

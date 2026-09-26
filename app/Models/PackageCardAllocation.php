<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageCardAllocation extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'package_purchase_id',
        'card_design_id',
        'nfc_card_id',
        'card_entitlement_id',
        'status',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(PackagePurchase::class, 'package_purchase_id');
    }

    public function design(): BelongsTo
    {
        return $this->belongsTo(CardDesign::class, 'card_design_id');
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(NfcCard::class, 'nfc_card_id');
    }

    public function entitlement(): BelongsTo
    {
        return $this->belongsTo(CardEntitlement::class, 'card_entitlement_id');
    }
}

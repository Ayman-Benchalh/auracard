<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\CardDesign;
use App\Models\CommercialPackage;
use Inertia\Inertia;
use Inertia\Response;

class MarketingController extends Controller
{
    /**
     * Display the official AURACARD premium commercial landing page.
     */
    public function home(): Response
    {
        $packages = CommercialPackage::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $cardDesigns = CardDesign::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return Inertia::render('Marketing/Home', [
            'packages' => $packages->map(fn ($p) => [
                'id' => $p->id,
                'key' => $p->key,
                'name' => $p->name,
                'description' => $p->description,
                'price' => (float) $p->price,
                'currency' => $p->currency,
                'duration_months' => $p->duration_months,
                'physical_cards_included' => $p->physical_cards_included,
                'profiles_included' => $p->profiles_included,
                'edit_credits_per_profile' => $p->edit_credits_per_profile,
                'allows_customer_owned_card' => $p->allows_customer_owned_card,
                'requires_customer_owned_card' => $p->requires_customer_owned_card,
                'is_featured' => (bool) $p->is_featured,
                'features_list' => $p->features_list,
            ]),
            'cardDesigns' => $cardDesigns->map(fn ($d) => [
                'id' => $d->id,
                'key' => $d->key,
                'name' => $d->name,
                'description' => $d->description,
                'material' => $d->material,
                'color' => $d->color,
                'preview_image' => $d->preview_image,
            ]),
        ]);
    }
}

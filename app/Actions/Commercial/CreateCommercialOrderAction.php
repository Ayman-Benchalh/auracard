<?php

declare(strict_types=1);

namespace App\Actions\Commercial;

use App\Models\CardDesign;
use App\Models\CommercialPackage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateCommercialOrderAction
{
    /**
     * Create an immutable order snapshot for a commercial package purchase.
     * Validates card selections against catalog and server-side package requirements.
     *
     * @param User $user
     * @param array{
     *     package_id: string,
     *     card_selections?: array<int, string>,
     *     external_card_data?: array<string, mixed>,
     *     customer_notes?: string|null
     * } $data
     * @return Order
     * @throws ValidationException
     */
    public function execute(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $package = CommercialPackage::where('id', $data['package_id'])
                ->where('is_active', true)
                ->first();

            if (! $package) {
                throw ValidationException::withMessages([
                    'package_id' => [__('The selected commercial package is invalid or currently inactive.')],
                ]);
            }

            $validatedSelections = [];

            // If package includes physical cards, validate exact quantity and designs server-side
            if ($package->physical_cards_included > 0) {
                $rawSelections = $data['card_selections'] ?? [];

                if (count($rawSelections) !== (int) $package->physical_cards_included) {
                    throw ValidationException::withMessages([
                        'card_selections' => [
                            __('You must select exactly :count card designs for this package.', [
                                'count' => $package->physical_cards_included,
                            ]),
                        ],
                    ]);
                }

                $activeDesigns = CardDesign::where('is_active', true)->pluck('key', 'id')->all();
                $activeKeys = array_values($activeDesigns);
                $activeIds = array_keys($activeDesigns);

                foreach ($rawSelections as $selection) {
                    // Match by ID or Key
                    $matchedDesign = null;
                    if (in_array($selection, $activeIds, true)) {
                        $matchedDesign = CardDesign::find($selection);
                    } elseif (in_array($selection, $activeKeys, true)) {
                        $matchedDesign = CardDesign::where('key', $selection)->first();
                    }

                    if (! $matchedDesign || ! $matchedDesign->is_active) {
                        throw ValidationException::withMessages([
                            'card_selections' => [__('One or more selected card designs are not available.')],
                        ]);
                    }

                    $validatedSelections[] = [
                        'design_id' => $matchedDesign->id,
                        'key' => $matchedDesign->key,
                        'name' => $matchedDesign->name,
                        'material' => $matchedDesign->material,
                        'color' => $matchedDesign->color,
                    ];
                }
            }

            // For profile_only package: validate customer owned card prerequisites
            $externalCardData = null;
            if ($package->requires_customer_owned_card) {
                $externalCardData = [
                    'hardware_origin' => 'customer_owned',
                    'tag_type' => $data['external_card_data']['tag_type'] ?? 'Standard NTAG213/215/216',
                    'notes' => $data['external_card_data']['notes'] ?? null,
                    'registered_at' => now()->toISOString(),
                ];
            }

            // Immutable Package Snapshot
            $packageSnapshot = [
                'package_id' => $package->id,
                'key' => $package->key,
                'name' => $package->name,
                'description' => $package->description,
                'price' => (float) $package->price,
                'currency' => $package->currency,
                'duration_months' => $package->duration_months,
                'physical_cards_included' => $package->physical_cards_included,
                'profiles_included' => $package->profiles_included,
                'edit_credits_per_profile' => $package->edit_credits_per_profile,
                'features_list' => $package->features_list,
                'created_at' => now()->toISOString(),
            ];

            return Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'commercial_package_id' => $package->id,
                'total_amount' => $package->price,
                'currency' => $package->currency,
                'status' => 'pending',
                'package_snapshot' => $packageSnapshot,
                'card_selections' => $validatedSelections,
                'external_card_data' => $externalCardData,
                'customer_notes' => $data['customer_notes'] ?? null,
            ]);
        });
    }
}

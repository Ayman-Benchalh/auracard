<?php

declare(strict_types=1);

namespace App\Filament\Resources\MarketingAssetResource\Pages;

use App\Filament\Resources\MarketingAssetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketingAsset extends CreateRecord
{
    protected static string $resource = MarketingAssetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = 'draft';

        return $data;
    }
}

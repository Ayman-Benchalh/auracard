<?php

declare(strict_types=1);

namespace App\Filament\Resources\MarketingAssetResource\Pages;

use App\Filament\Resources\MarketingAssetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketingAssets extends ListRecords
{
    protected static string $resource = MarketingAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

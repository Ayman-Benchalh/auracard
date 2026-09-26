<?php

declare(strict_types=1);

namespace App\Filament\Resources\CommercialPackageResource\Pages;

use App\Filament\Resources\CommercialPackageResource;
use Filament\Resources\Pages\ListRecords;

class ListCommercialPackages extends ListRecords
{
    protected static string $resource = CommercialPackageResource::class;
}

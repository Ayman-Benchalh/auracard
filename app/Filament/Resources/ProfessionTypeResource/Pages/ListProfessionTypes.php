<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProfessionTypeResource\Pages;

use App\Filament\Resources\ProfessionTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProfessionTypes extends ListRecords
{
    protected static string $resource = ProfessionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

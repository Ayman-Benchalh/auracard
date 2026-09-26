<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProfessionTypeResource\Pages;

use App\Filament\Resources\ProfessionTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProfessionType extends EditRecord
{
    protected static string $resource = ProfessionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
